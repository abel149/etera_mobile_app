<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InsuranceEncryptionController extends Controller
{
    /**
     * Show the encryption setup page for the logged-in insurance user.
     */
    public function setupPage()
    {
        $user = Auth::user();
        return view('insurance.encryption-setup', compact('user'));
    }

    /**
     * Save the insurance user's public key and AES-wrapped private key.
     * Called once during setup — all three fields come from the browser
     * (the plaintext private key never touches the server).
     *
     * POST /insurance/encryption/setup
     * body: { public_key, encrypted_private_key, key_iv, key_salt }
     */
    public function saveKeys(Request $request)
    {
        $request->validate([
            'public_key'                      => 'required|string',
            'encrypted_private_key'           => 'required|string',
            'key_iv'                          => 'required|string',
            'key_salt'                        => 'required|string',
            'recovery_encrypted_private_key'  => 'nullable|string',
            'recovery_key_iv'                 => 'nullable|string',
            'recovery_key_salt'               => 'nullable|string',
        ]);

        $user = Auth::user();
        $user->update([
            'public_key'                     => $request->public_key,
            'encrypted_private_key'          => $request->encrypted_private_key,
            'key_iv'                         => $request->key_iv,
            'key_salt'                       => $request->key_salt,
            'has_encryption'                 => true,
            'recovery_encrypted_private_key' => $request->recovery_encrypted_private_key,
            'recovery_key_iv'                => $request->recovery_key_iv,
            'recovery_key_salt'              => $request->recovery_key_salt,
        ]);

        Log::info('E2E encryption keys saved', ['user_id' => $user->id]);

        return response()->json(['success' => true]);
    }

    /**
     * Return the insurance's public key for a given proforma.
     * Used by shops/garages before submitting a price quote so they can
     * encrypt their prices in the browser.
     *
     * GET /insurance/public-key/{proforma}
     * Requires the requesting user to be authenticated shop/garage.
     */
    public function getPublicKey(Proforma $proforma)
    {
        $poster = $proforma->poster;

        if (!$poster || !$poster->has_encryption || !$poster->public_key) {
            return response()->json([
                'has_encryption' => false,
                'public_key'     => null,
            ]);
        }

        return response()->json([
            'has_encryption' => true,
            'public_key'     => $poster->public_key,
        ]);
    }

    /**
     * Re-wrap the existing RSA private key with a new PIN.
     * The browser decrypts with old PIN, re-encrypts with new PIN, then POSTs
     * only the new encrypted blob. The public key is NEVER changed here, so
     * all previously encrypted proformas remain fully readable with the new PIN.
     *
     * POST /insurance/encryption/change-pin
     */
    public function changePin(Request $request)
    {
        $request->validate([
            'encrypted_private_key' => 'required|string',
            'key_iv'                => 'required|string',
            'key_salt'              => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user->has_encryption) {
            return response()->json(['error' => 'Encryption not set up yet.'], 422);
        }

        $user->update([
            'encrypted_private_key' => $request->encrypted_private_key,
            'key_iv'                => $request->key_iv,
            'key_salt'              => $request->key_salt,
            // public_key intentionally NOT updated — old proformas stay readable
        ]);

        Log::info('E2E encryption PIN changed', ['user_id' => $user->id]);

        return response()->json(['success' => true]);
    }

    /**
     * Return the encrypted private key blob for the logged-in insurance user.
     * The browser will use the PIN to unwrap it client-side — the server
     * never learns the PIN or the plaintext private key.
     *
     * GET /insurance/encryption/private-key
     */
    public function getEncryptedPrivateKey()
    {
        $user = Auth::user();

        if (!$user->has_encryption) {
            return response()->json(['error' => 'Encryption not set up.'], 404);
        }

        return response()->json([
            'encrypted_private_key' => $user->encrypted_private_key,
            'key_iv'                => $user->key_iv,
            'key_salt'              => $user->key_salt,
        ]);
    }

    /**
     * Return the recovery-wrapped private key blob for the logged-in insurance user.
     * Used when the user has forgotten their PIN and wants to recover via recovery code.
     *
     * GET /insurance/encryption/recovery-key
     */
    public function getRecoveryKey()
    {
        $user = Auth::user();

        if (!$user->has_encryption) {
            return response()->json(['error' => 'Encryption not set up.'], 404);
        }

        if (!$user->recovery_encrypted_private_key) {
            return response()->json(['error' => 'No recovery key on file. Please regenerate keys to enable recovery.'], 404);
        }

        return response()->json([
            'recovery_encrypted_private_key' => $user->recovery_encrypted_private_key,
            'recovery_key_iv'                => $user->recovery_key_iv,
            'recovery_key_salt'              => $user->recovery_key_salt,
        ]);
    }
}
