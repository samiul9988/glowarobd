<?php

namespace App\Helpers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JWToken
{
    /**
     * Secret key for signing the token.
     *
     * @var string
     */
    private static $secretKey;

    /**
     * Algorithm used for signing the token.
     *
     * @var string
     */
    private static $algorithm;

    /**
     * Initialize the secret key and algorithm.
     */
    private static function initialize()
    {
        self::$secretKey = env('JWT_SECRET');
        self::$algorithm = env('JWT_ALGORITHM', 'HS256');
    }

    /**
     * Generate a JWS token.
     *
     * @param array $payload The payload data to include in the token.
     * @return string The generated JWS token.
     */
    public static function generate(User $user): string
    {
        self::initialize();

        $payload = [
            'id' => $user->id, // User ID
            'email' => $user->email, // User email
            'role' => $user->user_type,
            'iss' => env('APP_NAME'), // Issuer
            'iat' => time(), // Issued at
            'jti' => bin2hex(random_bytes(16)), // Unique token ID
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null, // User IP address
        ];

        // Generate and return the token
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    /**
     * Regenerate a JWS token with a new expiry time.
     *
     * @param string $token The original token.
     * @return string The regenerated JWS token.
     */
    public static function regenerate($token): string | null
    {
        self::initialize();

        if(!$token) {
            return null;
        }

        $decoded = self::decode($token);
        if (!$decoded) {
            return null;
        }
        $user = User::find($decoded->id);
        if (!$user || $user->banned) {
            return 'invalid';
        }

        // Update the expiry time
        $decoded->iat = time(); // Update issued at time

        // Generate and return the new token
        return JWT::encode((array) $decoded, self::$secretKey, self::$algorithm);
    }

    /**
     * Verify a JWS token.
     *
     * @param string $token The token to verify.
     * @return object|null The decoded token payload if valid, otherwise null.
     */
    public static function verify(string $app_id, string $app_key): object | string | null
    {
        self::initialize();

        try {
            if(!$app_id || !$app_key) {
                return null;
            }
            // Decode and verify the token
            $decoded = JWT::decode($app_key, new Key(self::$secretKey, self::$algorithm));

            $user = User::find($decoded->id);
            if(!$user || $user->banned) {
                // User not found
                return 'invalid';
            }
            if($user->app_id !== $app_id || $user->app_key !== $app_key) {
                return null;
            }
            return $decoded;
        } catch (ExpiredException $e) {
            // Token has expired
            return null;
        } catch (SignatureInvalidException $e) {
            // Invalid token signature
            return null;
        } catch (Exception $e) {
            // Other errors
            return null;
        }
    }

    /**
     * Get the payload from a JWS token without verification.
     *
     * @param string $token The token to decode.
     * @return object|null The decoded token payload if valid, otherwise null.
     */
    public static function decode(string $token): ?object
    {
        self::initialize();

        try {
            if(!$token) {
                return null;
            }
            // Decode the token without verification
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return $decoded;
        } catch (Exception $e) {
            // Handle errors
            return null;
        }
    }
}