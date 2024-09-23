
# Secure and Robust PHP JWT Class

This class provides a secure way to create and verify JWT (JSON Web Tokens) in PHP using both HMAC and RSA algorithms. It includes support for various JWT claims like expiration (`exp`), issued-at (`iat`), and not-before (`nbf`), making it suitable for production-level applications.

## SecureJWT Class Code

```php
<?php

class SecureJWT
{
    private static $secretKey = 'your_hmac_secret_key';  // For HMAC (HS256, HS384, HS512)
    private static $privateKey = 'file://path/to/private_key.pem';  // For RS256
    private static $publicKey = 'file://path/to/public_key.pem';    // For RS256
    private static $alg = 'RS256';  // Default algorithm (change to 'HS256' for HMAC)
    private static $algorithms = [
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
        'RS256' => 'RS256',
        'RS512' => 'RS512'
    ];

    // Encode the JWT with header, payload, and signature
    public static function encode(array $payload, $exp = 3600)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$alg]);

        // Add standard claims
        $payload['exp'] = time() + $exp;  // Expiration time
        $payload['iat'] = time();         // Issued at time
        $payload['nbf'] = time();         // Not before time

        // Convert to base64 URL encoding
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Generate the signature based on the algorithm
        $signature = self::sign("$base64UrlHeader.$base64UrlPayload");

        $base64UrlSignature = self::base64UrlEncode($signature);

        // Final JWT token (header.payload.signature)
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    // Decode and verify the JWT
    public static function decode($jwt)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token structure');
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($base64UrlHeader), true);
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        $signatureProvided = self::base64UrlDecode($base64UrlSignature);

        // Check for proper decoding
        if (!$header || !$payload) {
            throw new Exception('Invalid token');
        }

        // Verify the algorithm
        if (!in_array($header['alg'], array_keys(self::$algorithms))) {
            throw new Exception('Algorithm not supported');
        }

        // Verify the signature
        $signatureValid = self::verify("$base64UrlHeader.$base64UrlPayload", $signatureProvided);

        if (!$signatureValid) {
            throw new Exception('Invalid signature');
        }

        // Check expiration
        if (isset($payload['exp']) && time() >= $payload['exp']) {
            throw new Exception('Token has expired');
        }

        // Check "not before" time
        if (isset($payload['nbf']) && time() < $payload['nbf']) {
            throw new Exception('Token not valid yet');
        }

        return $payload;
    }

    // Sign the JWT using the appropriate algorithm
    private static function sign($data)
    {
        switch (self::$alg) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                return hash_hmac(self::$algorithms[self::$alg], $data, self::$secretKey, true);
            case 'RS256':
            case 'RS512':
                $privateKey = openssl_pkey_get_private(self::$privateKey);
                if (!$privateKey) {
                    throw new Exception('Private key is invalid');
                }
                openssl_sign($data, $signature, $privateKey, self::$algorithms[self::$alg]);
                openssl_free_key($privateKey);
                return $signature;
            default:
                throw new Exception('Unsupported algorithm');
        }
    }

    // Verify the signature
    private static function verify($data, $signature)
    {
        switch (self::$alg) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                $expectedSignature = hash_hmac(self::$algorithms[self::$alg], $data, self::$secretKey, true);
                return hash_equals($signature, $expectedSignature);  // Timing-safe comparison
            case 'RS256':
            case 'RS512':
                $publicKey = openssl_pkey_get_public(self::$publicKey);
                if (!$publicKey) {
                    throw new Exception('Public key is invalid');
                }
                $result = openssl_verify($data, $signature, $publicKey, self::$algorithms[self::$alg]);
                openssl_free_key($publicKey);
                return $result === 1;
            default:
                throw new Exception('Unsupported algorithm');
        }
    }

    // Base64 URL-safe encoding
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Base64 URL-safe decoding
    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
```

## Key Features

- **Algorithm Support**: Supports HMAC (`HS256`, `HS384`, `HS512`) and RSA (`RS256`, `RS512`) algorithms for signing JWTs.
- **Standard Claims**: Automatically adds and validates common JWT claims such as:
  - `exp` (Expiration Time)
  - `iat` (Issued At Time)
  - `nbf` (Not Before Time)
- **Signature Verification**: Verifies the JWT signature using the selected algorithm and key.
- **Error Handling**: Throws meaningful exceptions for token structure, signature, expiration, and key errors.

## RSA Key Generation

To use RSA-based algorithms, you need to generate a public and private key pair:

```bash
# Generate a private key
openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048

# Generate the corresponding public key
openssl rsa -pubout -in private_key.pem -out public_key.pem
```

## Usage Examples

### Encoding with RSA (RS256)

```php
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin'
];

$jwt = SecureJWT::encode($payload, 3600);  // Token expires in 1 hour
echo $jwt;
```

### Decoding and Verifying the Token

```php
try {
    $decoded = SecureJWT::decode($jwt);
    print_r($decoded);  // Decoded payload
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## Improvements Over Simple JWT Class

1. **Support for RSA Algorithms**: In addition to HMAC, the class supports RSA-based algorithms (`RS256`, `RS512`), which use public/private key pairs for stronger security.
2. **Automatic Claims**: The class automatically handles claims like `exp`, `iat`, and `nbf`, reducing manual handling of these common JWT attributes.
3. **Error Handling**: Improved error handling, providing detailed exceptions for invalid tokens, expired tokens, invalid signatures, and algorithm mismatches.
4. **Security**: Use of `openssl_sign` and `openssl_verify` for RSA ensures secure signing and verification.
