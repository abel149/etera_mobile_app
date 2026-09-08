/**
 * E2E Encryption Module — Web Crypto API (RSA-OAEP + AES-GCM)
 *
 * Flow:
 *  1. Insurance sets up: generateKeyPair() → store public key on server,
 *     wrap private key with PIN via encryptPrivateKey() → store on server.
 *  2. Shop/Garage submitting prices: fetch insurance public key →
 *     encryptValue(price, publicKey) → send ciphertext to server.
 *  3. Insurance viewing: load encrypted private key + ciphertexts →
 *     decryptPrivateKey(pin) → decryptValue(ciphertext, privateKey).
 */
const E2EEncryption = (() => {

    // ── Helpers ──────────────────────────────────────────────────────────────

    const b64 = buf => btoa(String.fromCharCode(...new Uint8Array(buf)));
    const unb64 = str => Uint8Array.from(atob(str), c => c.charCodeAt(0));
    const enc = new TextEncoder();
    const dec = new TextDecoder();

    // ── RSA Key Pair ─────────────────────────────────────────────────────────

    async function generateKeyPair() {
        return crypto.subtle.generateKey(
            {
                name: 'RSA-OAEP',
                modulusLength: 2048,
                publicExponent: new Uint8Array([1, 0, 1]),
                hash: 'SHA-256',
            },
            true,
            ['encrypt', 'decrypt']
        );
    }

    async function exportPublicKey(key) {
        return b64(await crypto.subtle.exportKey('spki', key));
    }

    async function exportPrivateKey(key) {
        return b64(await crypto.subtle.exportKey('pkcs8', key));
    }

    async function importPublicKey(base64) {
        return crypto.subtle.importKey(
            'spki', unb64(base64),
            { name: 'RSA-OAEP', hash: 'SHA-256' },
            false, ['encrypt']
        );
    }

    async function importPrivateKey(base64) {
        return crypto.subtle.importKey(
            'pkcs8', unb64(base64),
            { name: 'RSA-OAEP', hash: 'SHA-256' },
            false, ['decrypt']
        );
    }

    // ── AES-GCM key derived from PIN (PBKDF2) ────────────────────────────────

    async function deriveAESKey(pin, salt) {
        const keyMaterial = await crypto.subtle.importKey(
            'raw', enc.encode(pin), 'PBKDF2', false, ['deriveKey']
        );
        return crypto.subtle.deriveKey(
            { name: 'PBKDF2', salt, iterations: 200000, hash: 'SHA-256' },
            keyMaterial,
            { name: 'AES-GCM', length: 256 },
            false, ['encrypt', 'decrypt']
        );
    }

    // ── Wrap / Unwrap Private Key with PIN ───────────────────────────────────

    /**
     * Encrypts the RSA private key using a PIN.
     * Returns { encrypted, iv, salt } — all base64 strings.
     * Store all three on the server; never store the PIN.
     */
    async function encryptPrivateKey(privateKey, pin) {
        const privateKeyB64 = await exportPrivateKey(privateKey);
        const salt = crypto.getRandomValues(new Uint8Array(16));
        const iv   = crypto.getRandomValues(new Uint8Array(12));
        const aesKey = await deriveAESKey(pin, salt);
        const ciphertext = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv },
            aesKey,
            enc.encode(privateKeyB64)
        );
        return { encrypted: b64(ciphertext), iv: b64(iv), salt: b64(salt) };
    }

    /**
     * Decrypts the stored private key using the PIN.
     * Returns a CryptoKey ready for RSA decryption.
     */
    async function decryptPrivateKey(encryptedB64, ivB64, saltB64, pin) {
        const aesKey = await deriveAESKey(pin, unb64(saltB64));
        const plaintext = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: unb64(ivB64) },
            aesKey,
            unb64(encryptedB64)
        );
        return importPrivateKey(dec.decode(plaintext));
    }

    /**
     * Like decryptPrivateKey but returns the raw base64 private key string
     * instead of a CryptoKey. Use this to cache the key in sessionStorage.
     */
    async function decryptPrivateKeyRaw(encryptedB64, ivB64, saltB64, pin) {
        const aesKey = await deriveAESKey(pin, unb64(saltB64));
        const plaintext = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: unb64(ivB64) },
            aesKey,
            unb64(encryptedB64)
        );
        return dec.decode(plaintext); // raw base64 pkcs8 string
    }

    /**
     * Re-wraps the RSA private key with a new PIN — works entirely at the
     * raw-bytes level so extractable:false is never an issue.
     * Returns { encrypted, iv, salt } — same shape as encryptPrivateKey().
     */
    async function rewrapPrivateKey(encryptedB64, ivB64, saltB64, oldPin, newPin) {
        // 1. AES-decrypt with old PIN → raw ArrayBuffer of private key bytes
        const aesOld    = await deriveAESKey(oldPin, unb64(saltB64));
        const rawBytes  = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: unb64(ivB64) },
            aesOld,
            unb64(encryptedB64)
        );
        // 2. AES-encrypt the same raw bytes with new PIN
        const newSalt = crypto.getRandomValues(new Uint8Array(16));
        const newIv   = crypto.getRandomValues(new Uint8Array(12));
        const aesNew  = await deriveAESKey(newPin, newSalt);
        const cipher  = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: newIv },
            aesNew,
            rawBytes
        );
        return { encrypted: b64(cipher), iv: b64(newIv), salt: b64(newSalt) };
    }

    // ── Encrypt / Decrypt Price Values ───────────────────────────────────────

    /**
     * Encrypts a numeric price value with the insurance's RSA public key.
     * Returns base64 ciphertext.
     */
    async function encryptValue(value, publicKeyBase64) {
        const publicKey = await importPublicKey(publicKeyBase64);
        const ciphertext = await crypto.subtle.encrypt(
            { name: 'RSA-OAEP' },
            publicKey,
            enc.encode(String(value))
        );
        return b64(ciphertext);
    }

    /**
     * Decrypts a base64 ciphertext using the insurance's CryptoKey private key.
     * Returns a numeric string (e.g. "12500.50").
     */
    async function decryptValue(ciphertextB64, privateKey) {
        const plaintext = await crypto.subtle.decrypt(
            { name: 'RSA-OAEP' },
            privateKey,
            unb64(ciphertextB64)
        );
        return dec.decode(plaintext);
    }

    // ── Public API ───────────────────────────────────────────────────────────
    return {
        generateKeyPair,
        exportPublicKey,
        importPrivateKey,
        encryptPrivateKey,
        decryptPrivateKey,
        decryptPrivateKeyRaw,
        rewrapPrivateKey,
        encryptValue,
        decryptValue,
    };
})();
