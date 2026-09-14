import 'dart:async';
import 'dart:convert';
import 'dart:isolate';
import 'dart:math';
import 'dart:typed_data';

import 'package:pointycastle/export.dart';

/// End-to-end encryption utilities for etera.
///
/// Algorithm choices (compatible with the web app's Web Crypto API):
///   Key pair  : RSA-OAEP, 2048-bit, SHA-256
///   PIN wrap  : PBKDF2(HMAC-SHA256, 100 000 iters) → AES-256-CBC
///   Price enc : RSA-OAEP(SHA-256) applied to the plaintext price string
///
/// The private key NEVER leaves the device in plaintext.

// ────────────────────────────────────────────────────────────────────────────
// Public types
// ────────────────────────────────────────────────────────────────────────────

class KeyPairResult {
  /// PEM-encoded RSA public key (SPKI — "BEGIN PUBLIC KEY").
  final String publicKeyPem;
  /// PEM-encoded RSA private key (PKCS8 — "BEGIN PRIVATE KEY").
  final String privateKeyPem;
  const KeyPairResult({required this.publicKeyPem, required this.privateKeyPem});
}

class WrappedKey {
  final String encryptedPrivateKey; // base64 AES-CBC ciphertext
  final String keyIv;               // base64 IV (16 bytes)
  final String keySalt;             // base64 PBKDF2 salt (16 bytes)
  const WrappedKey({required this.encryptedPrivateKey, required this.keyIv, required this.keySalt});
}

class EncryptedPrice {
  final String encryptedAmount; // base64 RSA-OAEP ciphertext
  const EncryptedPrice({required this.encryptedAmount});
}

// ────────────────────────────────────────────────────────────────────────────
// 1.  RSA key-pair generation  (offloaded to an isolate)
// ────────────────────────────────────────────────────────────────────────────

Future<KeyPairResult> generateKeyPair() => Isolate.run(_generateIsolate);

KeyPairResult _generateIsolate() {
  final rand = _secureRandom();
  final keyGen = RSAKeyGenerator()
    ..init(ParametersWithRandom(
        RSAKeyGeneratorParameters(BigInt.parse('65537'), 2048, 64), rand));

  final pair = keyGen.generateKeyPair();
  // pair.publicKey / privateKey are already RSAPublicKey / RSAPrivateKey
  // in pointycastle 4.x — the explicit cast is kept for documentation clarity.
  // ignore: unnecessary_cast
  final rsaPub  = pair.publicKey  as RSAPublicKey;
  // ignore: unnecessary_cast
  final rsaPriv = pair.privateKey as RSAPrivateKey;
  return KeyPairResult(
    publicKeyPem:  _encodeSpkiPem(rsaPub),
    privateKeyPem: _encodePkcs8Pem(rsaPriv),
  );
}

// ────────────────────────────────────────────────────────────────────────────
// 2.  PIN wrapping / unwrapping  (AES-256-CBC + PBKDF2)
// ────────────────────────────────────────────────────────────────────────────

WrappedKey wrapPrivateKey(String privateKeyPem, String pin) {
  final salt = _randomBytes(16);
  final iv   = _randomBytes(16);
  final key  = _pbkdf2(pin, salt, 32);
  final ct   = _aesCbcEncrypt(key, iv, Uint8List.fromList(utf8.encode(privateKeyPem)));
  return WrappedKey(
    encryptedPrivateKey: base64.encode(ct),
    keyIv:               base64.encode(iv),
    keySalt:             base64.encode(salt),
  );
}

/// Unwrap a PIN-wrapped private key. Throws on wrong PIN (bad padding).
String unwrapPrivateKey(
  String encryptedPrivateKeyB64,
  String ivB64,
  String saltB64,
  String pin,
) {
  final salt = Uint8List.fromList(base64.decode(saltB64));
  final iv   = Uint8List.fromList(base64.decode(ivB64));
  final key  = _pbkdf2(pin, salt, 32);
  final ct   = Uint8List.fromList(base64.decode(encryptedPrivateKeyB64));
  final pt   = _aesCbcDecrypt(key, iv, ct);
  return utf8.decode(pt); // throws if padding is corrupt (wrong PIN)
}

// ────────────────────────────────────────────────────────────────────────────
// 3.  RSA-OAEP price encryption / decryption
// ────────────────────────────────────────────────────────────────────────────

EncryptedPrice encryptAmount(double amount, String publicKeyPem) {
  final pub    = _parseSpkiPem(publicKeyPem);
  final plain  = Uint8List.fromList(utf8.encode(amount.toStringAsFixed(2)));
  final cipher = OAEPEncoding.withSHA256(RSAEngine())
    ..init(true, PublicKeyParameter<RSAPublicKey>(pub));
  return EncryptedPrice(encryptedAmount: base64.encode(cipher.process(plain)));
}

double decryptAmount(String encryptedAmountB64, String privateKeyPem) {
  final priv   = _parsePkcs8Pem(privateKeyPem);
  final ct     = Uint8List.fromList(base64.decode(encryptedAmountB64));
  final cipher = OAEPEncoding.withSHA256(RSAEngine())
    ..init(false, PrivateKeyParameter<RSAPrivateKey>(priv));
  return double.parse(utf8.decode(cipher.process(ct)));
}

// ────────────────────────────────────────────────────────────────────────────
// Crypto helpers
// ────────────────────────────────────────────────────────────────────────────

SecureRandom _secureRandom() {
  final rng  = Random.secure();
  final seed = Uint8List.fromList(List.generate(32, (_) => rng.nextInt(256)));
  return FortunaRandom()..seed(KeyParameter(seed));
}

Uint8List _randomBytes(int n) {
  final rng = Random.secure();
  return Uint8List.fromList(List.generate(n, (_) => rng.nextInt(256)));
}

Uint8List _pbkdf2(String password, Uint8List salt, int keyLen) {
  final kdf = PBKDF2KeyDerivator(HMac(SHA256Digest(), 64))
    ..init(Pbkdf2Parameters(salt, 100000, keyLen));
  return kdf.process(Uint8List.fromList(utf8.encode(password)));
}

Uint8List _aesCbcEncrypt(Uint8List key, Uint8List iv, Uint8List plain) {
  final params = ParametersWithIV<KeyParameter>(KeyParameter(key), iv);
  return (PaddedBlockCipher('AES/CBC/PKCS7')
        ..init(true, PaddedBlockCipherParameters(params, null)))
      .process(plain);
}

Uint8List _aesCbcDecrypt(Uint8List key, Uint8List iv, Uint8List ct) {
  final params = ParametersWithIV<KeyParameter>(KeyParameter(key), iv);
  return (PaddedBlockCipher('AES/CBC/PKCS7')
        ..init(false, PaddedBlockCipherParameters(params, null)))
      .process(ct);
}

// ────────────────────────────────────────────────────────────────────────────
// DER / PEM encoding  (manual, no ASN1 library needed)
// ────────────────────────────────────────────────────────────────────────────

// RSA OID bytes: 1.2.840.113549.1.1.1
const _rsaOidBytes = [0x2a, 0x86, 0x48, 0x86, 0xf7, 0x0d, 0x01, 0x01, 0x01];

Uint8List _tag(int t, Uint8List content) {
  final lenBytes = _encLen(content.length);
  final out = Uint8List(1 + lenBytes.length + content.length);
  out[0] = t;
  out.setRange(1, 1 + lenBytes.length, lenBytes);
  out.setRange(1 + lenBytes.length, out.length, content);
  return out;
}

Uint8List _encLen(int len) {
  if (len < 128) return Uint8List.fromList([len]);
  if (len < 256) return Uint8List.fromList([0x81, len]);
  return Uint8List.fromList([0x82, (len >> 8) & 0xff, len & 0xff]);
}

Uint8List _seq(List<Uint8List> items) {
  final body = _concat(items);
  return _tag(0x30, body);
}

Uint8List _concat(List<Uint8List> parts) {
  int total = parts.fold(0, (s, p) => s + p.length);
  final out = Uint8List(total);
  int offset = 0;
  for (final p in parts) { out.setRange(offset, offset + p.length, p); offset += p.length; }
  return out;
}

Uint8List _derInt(BigInt n) {
  Uint8List bytes = _bigIntToBytes(n);
  if (bytes.isNotEmpty && (bytes[0] & 0x80) != 0) {
    bytes = _concat([Uint8List.fromList([0x00]), bytes]);
  }
  return _tag(0x02, bytes);
}

Uint8List _bigIntToBytes(BigInt n) {
  if (n == BigInt.zero) return Uint8List.fromList([0x00]);
  var hex = n.toRadixString(16);
  if (hex.length.isOdd) hex = '0$hex';
  return Uint8List.fromList(
    List.generate(hex.length ~/ 2, (i) => int.parse(hex.substring(i * 2, i * 2 + 2), radix: 16)));
}

Uint8List _oid(List<int> oidBytes) => _tag(0x06, Uint8List.fromList(oidBytes));

Uint8List _null() => Uint8List.fromList([0x05, 0x00]);

Uint8List _octetString(Uint8List data) => _tag(0x04, data);

Uint8List _bitString(Uint8List data) {
  // prefix with 0x00 (no unused bits)
  return _tag(0x03, _concat([Uint8List.fromList([0x00]), data]));
}

String _toPem(String label, Uint8List der) {
  final b64 = base64.encode(der);
  final lines = RegExp(r'.{1,64}').allMatches(b64).map((m) => m.group(0)!).join('\n');
  return '-----BEGIN $label-----\n$lines\n-----END $label-----';
}

/// Encode RSA public key → SPKI PEM ("BEGIN PUBLIC KEY")
String _encodeSpkiPem(RSAPublicKey key) {
  final innerSeq = _seq([_derInt(key.modulus!), _derInt(key.exponent!)]);
  final algId    = _seq([_oid(_rsaOidBytes), _null()]);
  final spki     = _seq([algId, _bitString(innerSeq)]);
  return _toPem('PUBLIC KEY', spki);
}

/// Encode RSA private key → PKCS8 PEM ("BEGIN PRIVATE KEY")
String _encodePkcs8Pem(RSAPrivateKey key) {
  final exp1 = key.privateExponent! % (key.p! - BigInt.one);
  final exp2 = key.privateExponent! % (key.q! - BigInt.one);
  final coef = key.q!.modInverse(key.p!);

  final rsaSeq = _seq([
    _derInt(BigInt.zero),          // version
    _derInt(key.modulus!),
    _derInt(key.exponent!),
    _derInt(key.privateExponent!),
    _derInt(key.p!),
    _derInt(key.q!),
    _derInt(exp1),
    _derInt(exp2),
    _derInt(coef),
  ]);
  final algId   = _seq([_oid(_rsaOidBytes), _null()]);
  final pkcs8   = _seq([_derInt(BigInt.zero), algId, _octetString(rsaSeq)]);
  return _toPem('PRIVATE KEY', pkcs8);
}

// ────────────────────────────────────────────────────────────────────────────
// DER / PEM parsing  (minimal — only what we need)
// ────────────────────────────────────────────────────────────────────────────

Uint8List _fromPem(String pem) {
  final stripped = pem
      .replaceAll(RegExp(r'-----[^-]+-----'), '')
      .replaceAll(RegExp(r'\s+'), '');
  return Uint8List.fromList(base64.decode(stripped));
}

/// (offset, length) of the content of a TLV at [pos]
({int start, int length}) _tlvContent(Uint8List der, int pos) {
  var p = pos + 1; // skip tag
  int len;
  if (der[p] < 0x80) {
    len = der[p++];
  } else {
    final n = der[p++] & 0x7f;
    len = 0;
    for (int i = 0; i < n; i++) { len = (len << 8) | der[p++]; }
  }
  return (start: p, length: len);
}

RSAPublicKey _parseSpkiPem(String pem) {
  final der  = _fromPem(pem);
  // SEQUENCE { SEQUENCE { OID, NULL }, BIT STRING { SEQUENCE { INT, INT } } }
  final top  = _tlvContent(der, 0);      // outer SEQUENCE
  var   p    = top.start;
  final alg  = _tlvContent(der, p);      // AlgorithmIdentifier SEQUENCE
  p = alg.start + alg.length;            // advance past alg seq

  final bs   = _tlvContent(der, p);      // BIT STRING
  final bsBody = bs.start + 1;          // skip the 0x00 "unused bits" byte

  final inner = _tlvContent(der, bsBody); // inner SEQUENCE { INT, INT }
  var   q   = inner.start;

  final modTlv = _tlvContent(der, q);
  final mod    = _parseBigInt(der, modTlv.start, modTlv.length);
  q = modTlv.start + modTlv.length;

  final expTlv = _tlvContent(der, q);
  final exp    = _parseBigInt(der, expTlv.start, expTlv.length);

  return RSAPublicKey(mod, exp);
}

RSAPrivateKey _parsePkcs8Pem(String pem) {
  final der  = _fromPem(pem);
  // SEQUENCE { INT(0), SEQUENCE { OID, NULL }, OCTET STRING { RSAPrivateKey } }
  final top   = _tlvContent(der, 0);
  var   p     = top.start;

  final ver   = _tlvContent(der, p);    // INTEGER 0 (version)
  p = ver.start + ver.length;

  final alg   = _tlvContent(der, p);   // AlgorithmIdentifier
  p = alg.start + alg.length;

  final os    = _tlvContent(der, p);   // OCTET STRING (inner RSAPrivateKey)
  final rsa   = _tlvContent(der, os.start); // inner SEQUENCE

  var q = rsa.start;
  // Skip version INTEGER 0
  final v0  = _tlvContent(der, q); q = v0.start  + v0.length;

  final nT  = _tlvContent(der, q); q = nT.start  + nT.length;
  final eT  = _tlvContent(der, q); q = eT.start  + eT.length;
  final dT  = _tlvContent(der, q); q = dT.start  + dT.length;
  final p1T = _tlvContent(der, q); q = p1T.start + p1T.length;
  final p2T = _tlvContent(der, q);

  final mod  = _parseBigInt(der, nT.start,  nT.length);
  final d    = _parseBigInt(der, dT.start,  dT.length);
  final p1   = _parseBigInt(der, p1T.start, p1T.length);
  final p2   = _parseBigInt(der, p2T.start, p2T.length);

  return RSAPrivateKey(mod, d, p1, p2);
}

BigInt _parseBigInt(Uint8List der, int start, int length) {
  // Strip leading zero byte (sign byte)
  int offset = start;
  int len    = length;
  while (len > 1 && der[offset] == 0x00) { offset++; len--; }
  var result = BigInt.zero;
  for (int i = 0; i < len; i++) { result = (result << 8) | BigInt.from(der[offset + i]); }
  return result;
}
