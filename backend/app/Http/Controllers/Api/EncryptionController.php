<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationPdf;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * EncryptionController
 *
 * Manages RSA/AES end-to-end encryption for insurance proforma submissions.
 *
 * Flow overview:
 *  1. Insurance sets up an RSA key pair in the app (private key never leaves the device).
 *     The browser/app encrypts the private key with a PIN-derived AES key, then POSTs only
 *     the public key + encrypted-private-key blob to this controller (saveKeys).
 *
 *  2. When a shop or garage is about to submit a price for an insurance proforma, it calls
 *     getPublicKey to retrieve the insurance's RSA public key.  It then encrypts the price
 *     (and optionally a PDF) client-side before POSTing to the apply endpoint.
 *
 *  3. The insurance user calls privateKey to download the encrypted-private-key blob, then
 *     unlocks it locally with their PIN and decrypts the submitted prices in-app.
 *
 * All routes that modify state require the authenticated user to be the owner of the keys.
 * The server never learns the plaintext private key or the PIN.
 */
class EncryptionController extends Controller
{
    // =========================================================================
    // GET /api/v1/insurance/encryption/status
    // Returns the encryption setup state for the authenticated insurance user.
    // =========================================================================
    public function status()
    {
        $user = auth()->user();

        return response()->json([
            'success'        => true,
            'has_encryption' => (bool) $user->has_encryption,
            'public_key'     => $user->has_encryption ? $user->public_key : null,
            'has_recovery'   => $user->has_encryption && !empty($user->recovery_encrypted_private_key),
        ]);
    }

    // =========================================================================
    // POST /api/v1/insurance/encryption/setup
    // Save the RSA public key and the PIN-wrapped private key.
    // The plaintext private key is NEVER sent to the server.
    //
    // Body:
    //   required: public_key, encrypted_private_key, key_iv, key_salt
    //   optional: recovery_encrypted_private_key, recovery_key_iv, recovery_key_salt
    // =========================================================================
    public function saveKeys(Request $request)
    {
        $request->validate([
            'public_key'                     => ['required', 'string'],
            'encrypted_private_key'          => ['required', 'string'],
            'key_iv'                         => ['required', 'string'],
            'key_salt'                       => ['required', 'string'],
            'recovery_encrypted_private_key' => ['nullable', 'string'],
            'recovery_key_iv'                => ['nullable', 'string'],
            'recovery_key_salt'              => ['nullable', 'string'],
        ]);

        $user = auth()->user();

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

        return response()->json([
            'success' => true,
            'message' => 'Encryption keys saved successfully.',
        ]);
    }

    // =========================================================================
    // GET /api/v1/insurance/encryption/private-key
    // Return the encrypted private key blob so the app can unlock it with the PIN.
    // The server never knows the PIN or the plaintext key.
    // =========================================================================
    public function privateKey()
    {
        $user = auth()->user();

        if (!$user->has_encryption) {
            return response()->json([
                'success' => false,
                'message' => 'Encryption not set up yet.',
            ], 404);
        }

        return response()->json([
            'success'               => true,
            'encrypted_private_key' => $user->encrypted_private_key,
            'key_iv'                => $user->key_iv,
            'key_salt'              => $user->key_salt,
        ]);
    }

    // =========================================================================
    // POST /api/v1/insurance/encryption/change-pin
    // Re-wrap the existing private key with a new PIN.
    // The client decrypts with old PIN then re-encrypts with new PIN —
    // only the new encrypted blob is sent here.  The public key is never changed,
    // so all previously encrypted proformas remain readable with the new PIN.
    //
    // Body: encrypted_private_key, key_iv, key_salt
    // =========================================================================
    public function changePin(Request $request)
    {
        $request->validate([
            'encrypted_private_key' => ['required', 'string'],
            'key_iv'                => ['required', 'string'],
            'key_salt'              => ['required', 'string'],
        ]);

        $user = auth()->user();

        if (!$user->has_encryption) {
            return response()->json([
                'success' => false,
                'message' => 'Encryption is not set up. Please run setup first.',
            ], 422);
        }

        $user->update([
            'encrypted_private_key' => $request->encrypted_private_key,
            'key_iv'                => $request->key_iv,
            'key_salt'              => $request->key_salt,
            // public_key intentionally NOT updated — old proformas stay readable
        ]);

        Log::info('E2E encryption PIN changed', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'PIN changed successfully.',
        ]);
    }

    // =========================================================================
    // GET /api/v1/insurance/encryption/recovery-key
    // Return the recovery-wrapped private key blob.
    // Used when the user has forgotten their PIN.
    // =========================================================================
    public function recoveryKey()
    {
        $user = auth()->user();

        if (!$user->has_encryption) {
            return response()->json([
                'success' => false,
                'message' => 'Encryption not set up.',
            ], 404);
        }

        if (empty($user->recovery_encrypted_private_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No recovery key on file. Regenerate your keys to enable recovery.',
            ], 404);
        }

        return response()->json([
            'success'                        => true,
            'recovery_encrypted_private_key' => $user->recovery_encrypted_private_key,
            'recovery_key_iv'                => $user->recovery_key_iv,
            'recovery_key_salt'              => $user->recovery_key_salt,
        ]);
    }

    // =========================================================================
    // GET /api/v1/proforma/{proforma}/public-key
    // Return the insurance poster's RSA public key for a given proforma.
    // Called by shops/garages before submitting a price so they can encrypt it.
    // Accessible to any authenticated user.
    // =========================================================================
    public function getPublicKey(Proforma $proforma)
    {
        $poster = $proforma->poster;

        if (!$poster || !$poster->has_encryption || empty($poster->public_key)) {
            return response()->json([
                'success'        => true,
                'has_encryption' => false,
                'public_key'     => null,
                'message'        => 'This proforma does not require encrypted submissions.',
            ]);
        }

        return response()->json([
            'success'        => true,
            'has_encryption' => true,
            'public_key'     => $poster->public_key,
        ]);
    }

    // =========================================================================
    // GET /api/v1/insurance/application/{application}/encrypted-file
    // Serve the encrypted PDF blob and the RSA-wrapped AES key to the insurance user
    // so they can decrypt the PDF client-side with their private key.
    // Only the proforma poster (insurance) may access this endpoint.
    // =========================================================================
    public function encryptedFile(ProformaApplication $application)
    {
        $user = auth()->user();

        // Only the proforma poster may download the encrypted file
        $proforma = $application->proforma;
        if (!$proforma || $proforma->poster_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the proforma poster can access this file.',
            ], 403);
        }

        $pdf = $application->pdf;

        if (!$pdf || !$pdf->isEncrypted()) {
            return response()->json([
                'success' => false,
                'message' => 'No encrypted file found for this application.',
            ], 404);
        }

        // Resolve base64 — may be stored on disk or inline in DB column
        if ($pdf->path && Storage::exists($pdf->path)) {
            $encBase64 = base64_encode(Storage::get($pdf->path));
        } elseif (!empty($pdf->encrypted_pdf)) {
            $encBase64 = $pdf->encrypted_pdf;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Encrypted file data not available.',
            ], 404);
        }

        return response()->json([
            'success'           => true,
            'application_id'    => $application->id,
            'encrypted_pdf'     => $encBase64,
            'encrypted_aes_key' => $pdf->encrypted_aes_key,
        ]);
    }
}
