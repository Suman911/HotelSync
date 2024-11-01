<?php

class JWT
{
    private static $privateKey = "file://" . __DIR__ . DIRECTORY_SEPARATOR . 'private_key.pem';  // Path to RSA private key
    private static $publicKey = "file://" . __DIR__ . DIRECTORY_SEPARATOR . 'public_key.pem';    // Path to RSA public key
    private static $alg = 'RS256';  // Default algorithm (use 'RS256' or 'RS512')
    private static $algorithms = [
        'RS256' => OPENSSL_ALGO_SHA256,
        'RS512' => OPENSSL_ALGO_SHA512
    ];

    // Encode the JWT with header, payload, and signature
    public static function encode(array $payload, $exp = 86400, $alg = "")
    {
        $alg = $alg ?: self::$alg;

        $header = json_encode(['typ' => 'JWT', 'alg' => $alg]);

        // Add standard claims
        $payload['exp'] = time() + $exp;  // Expiration time
        $payload['iat'] = time();         // Issued at time
        $payload['nbf'] = time();         // Not before time

        // Convert to base64 URL encoding
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Generate the signature based on the algorithm
        $signature = self::sign("$base64UrlHeader.$base64UrlPayload", $alg);

        $base64UrlSignature = self::base64UrlEncode($signature);

        // Final JWT token (header.payload.signature)
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    // Decode and verify the JWT, with optional auto-refresh if about to expire
    public static function decode($jwt)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token structure');
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($base64UrlHeader), true);
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        $signatureProvided = self::base64UrlDecode($base64UrlSignature);
        $alg = $header['alg'];

        // Verify the algorithm
        if (!in_array($alg, array_keys(self::$algorithms))) {
            throw new Exception('Algorithm not supported');
        }

        // Verify the signature
        $signatureValid = self::verify("$base64UrlHeader.$base64UrlPayload", $signatureProvided, $alg);

        if (!$signatureValid) {
            throw new Exception('Token is corrupted');
        }

        return $payload;
    }

    // Refresh the JWT by extending the expiration time
    private static function refresh(array $payload, $exp = 86400)
    {
        if (!isset($payload['exp'])) {
            throw new Exception('Token is corrupted');
        }
        // Check expiration
        if (time() >= $payload['exp']) {
            throw new Exception('Token is already expired please login again');
        }

        unset($payload['exp']);  // Remove the old expiration time
        return self::encode($payload, $exp);  // Re-encode with a new expiration
    }

    // Sign the JWT using RSA algorithms
    private static function sign($data, $alg = "")
    {
        $alg = $alg ?: self::$alg;

        $privateKey = openssl_pkey_get_private(self::$privateKey);
        if (!$privateKey) {
            throw new Exception('Private key is invalid');
        }
        openssl_sign($data, $signature, $privateKey, self::$algorithms[$alg]);
        return $signature;
    }

    // Verify the signature
    private static function verify($data, $signature, $alg = "")
    {
        $alg = $alg ?: self::$alg;
        $publicKey = openssl_pkey_get_public(self::$publicKey);
        if (!$publicKey) {
            throw new Exception('Public key is invalid');
        }
        $result = openssl_verify($data, $signature, $publicKey, self::$algorithms[$alg]);
        return $result === 1;
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
