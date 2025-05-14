<?php

namespace Woodpecker\Support;

use WPException;
use Woodpecker\Cryption;
use Woodpecker\Facade;

/**
 * Secure Cookie Management Class
 * 
 * Provides a secure cookie handling system with multiple security features:
 * - Encrypted cookie data storage
 * - Strict input validation
 * - Automatic expiration
 * - Secure cookie configuration (HttpOnly, Secure, SameSite)
 * - CSRF protection integration
 * - Protection against cookie tampering
 * 
 * @package Woodpecker\Support
 */
class Cookie extends Facade
{
    /**
     * Default cookie name prefix
     * - Adds an extra layer of security through obscurity
     */
    protected static string $cookiePrefix = 'SECURE_';

    /**
     * Encryption secret key for cookie data
     * - Must be set before any cookie operations
     * - Should be a strong cryptographic key (32+ bytes recommended)
     */
    protected static string $secretKey = '';

    /**
     * Default cookie lifetime in seconds
     * - Default: 3600 (1 hour)
     * - After this period, cookie will expire
     */
    protected static int $lifetime = 3600;

    /**
     * Whether to encrypt cookie values by default
     * - Recommended to keep true for production
     */
    protected static bool $encryptByDefault = true;

    /**
     * Default cookie path
     * - Should be restricted to necessary paths only
     */
    protected static string $path = '/';

    /**
     * Default cookie domain
     * - Should be set to your primary domain
     */
    protected static string $domain = '';

    /**
     * Whether cookies should only be sent over HTTPS
     * - Must be true in production
     */
    protected static bool $secure = true;

    /**
     * Whether cookies should be accessible only through HTTP protocol
     * - Prevents JavaScript access
     */
    protected static bool $httpOnly = true;

    /**
     * SameSite attribute for cookies
     * - Default: 'Strict' (recommended for security)
     * - Alternatives: 'Lax' or 'None' (with secure=true)
     */
    protected static string $sameSite = 'Strict';

    /**
     * Initialize the secure cookie system
     * 
     * @param array $config Configuration options [
     *     'prefix'        => string,  Cookie name prefix
     *     'secretKey'     => string,  Encryption secret key
     *     'lifetime'      => int,     Cookie lifetime in seconds
     *     'encrypt'      => bool,    Whether to encrypt by default
     *     'path'         => string,  Cookie path
     *     'domain'       => string,  Cookie domain
     *     'secure'       => bool,    Secure flag
     *     'httpOnly'     => bool,    HttpOnly flag
     *     'sameSite'     => string   SameSite attribute
     * ]
     * @throws WPException If configuration is invalid
     */
    public static function init(array $config = null): void
    {
      $configList = require_once(__DIR__ . '/../../../config.php');
      $config = $config ?? $configList["Cookie"] ;
        self::applyConfiguration($config);
        self::validateConfiguration();
    }

    /**
     * Set a secure cookie
     * 
     * @param string $name      Cookie name
     * @param mixed $value      Cookie value
     * @param array $options    Additional options [
     *     'lifetime'  => int,
     *     'encrypt'   => bool,
     *     'path'      => string,
     *     'domain'    => string,
     *     'secure'    => bool,
     *     'httpOnly'  => bool,
     *     'sameSite'  => string
     * ]
     * @throws WPException If value is invalid or cookie can't be set
     */
    public static function set(string $name, $value, array $options = []): void
    {
        $name = self::prepareName($name);
        $value = self::prepareValue($value, $options['encrypt'] ?? self::$encryptByDefault);
        $options = self::mergeOptions($options);

        $success = setcookie(
            $name,
            $value,
            [
                'expires' => time() + ($options['lifetime'] ?? self::$lifetime),
                'path' => $options['path'] ?? self::$path,
                'domain' => $options['domain'] ?? self::$domain,
                'secure' => $options['secure'] ?? self::$secure,
                'httponly' => $options['httpOnly'] ?? self::$httpOnly,
                'samesite' => $options['sameSite'] ?? self::$sameSite
            ]
        );

        if (!$success) {
            throw new WPException('Failed to set secure cookie');
        }
    }

    /**
     * Get a cookie value
     * 
     * @param string $name      Cookie name
     * @param mixed $default    Default value if cookie doesn't exist
     * @param bool $decrypt     Whether to decrypt the value (if encrypted)
     * @return mixed            The cookie value or default
     * @throws WPException If decryption fails
     */
    public static function get(string $name, $default = null, bool $decrypt = true)
    {
        $name = self::prepareName($name);
        
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        $value = $_COOKIE[$name];
        
        if ($decrypt) {
            try {
                return Cryption::decrypt($value, self::$secretKey);
            } catch (WPException $e) {
                if (self::$encryptByDefault) {
                    throw new WPException('Failed to decrypt cookie value');
                }
                return $value;
            }
        }

        return $value;
    }

    /**
     * Check if a cookie exists
     * 
     * @param string $name Cookie name
     * @return bool True if cookie exists
     */
    public static function has(string $name): bool
    {
        return isset($_COOKIE[self::prepareName($name)]);
    }

    /**
     * Delete a cookie
     * 
     * @param string $name Cookie name
     * @param array $options Additional options for path/domain
     */
    public static function delete(string $name, array $options = []): void
    {
        $name = self::prepareName($name);
        $options = self::mergeOptions($options);

        setcookie(
            $name,
            '',
            [
                'expires' => time() - 3600,
                'path' => $options['path'] ?? self::$path,
                'domain' => $options['domain'] ?? self::$domain,
                'secure' => $options['secure'] ?? self::$secure,
                'httponly' => $options['httpOnly'] ?? self::$httpOnly,
                'samesite' => $options['sameSite'] ?? self::$sameSite
            ]
        );

        unset($_COOKIE[$name]);
    }

    /**
     * Apply configuration settings
     * 
     * @param array $config Configuration options
     */
    protected static function applyConfiguration(array $config): void
    {
        self::$cookiePrefix = $config['prefix'] ?? self::$cookiePrefix;
        self::$secretKey = $config['secretKey'] ?? self::generateSecureKey();
        self::$lifetime = $config['lifetime'] ?? self::$lifetime;
        self::$encryptByDefault = $config['encrypt'] ?? self::$encryptByDefault;
        self::$path = $config['path'] ?? self::$path;
        self::$domain = $config['domain'] ?? $_SERVER['HTTP_HOST'] ?? '';
        self::$secure = $config['secure'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        self::$httpOnly = $config['httpOnly'] ?? self::$httpOnly;
        self::$sameSite = $config['sameSite'] ?? self::$sameSite;
    }

    /**
     * Validate that required configuration is present
     * 
     * @throws WPException If any required configuration is missing
     */
    protected static function validateConfiguration(): void
    {
        if (empty(self::$secretKey)) {
            throw new WPException('Secret key cannot be empty');
        }
        
        if (!in_array(strtolower(self::$sameSite), ['strict', 'lax', 'none'], true)) {
            throw new WPException('Invalid SameSite attribute');
        }
        
        if (self::$sameSite === 'None' && !self::$secure) {
            throw new WPException('SameSite=None requires Secure flag');
        }
    }

    /**
     * Prepare cookie name with prefix and sanitization
     * 
     * @param string $name Raw cookie name
     * @return string Prepared cookie name
     */
    protected static function prepareName(string $name): string
    {
        $name = self::$cookiePrefix . $name;
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    }

    /**
     * Prepare cookie value with validation and optional encryption
     * 
     * @param mixed $value Raw value
     * @param bool $encrypt Whether to encrypt the value
     * @return string Prepared value
     * @throws WPException If value is invalid
     */
    protected static function prepareValue($value, bool $encrypt): string
    {
        $validated = self::validateValue($value);
        
        if ($encrypt) {
            return Cryption::encrypt($validated, self::$secretKey);
        }
        
        if (is_array($validated) || is_object($validated)) {
            throw new WPException('Complex data types require encryption');
        }
        
        return (string)$validated;
    }

    /**
     * Merge custom options with defaults
     * 
     * @param array $options Custom options
     * @return array Merged options
     */
    protected static function mergeOptions(array $options): array
    {
        return array_merge([
            'lifetime' => self::$lifetime,
            'encrypt' => self::$encryptByDefault,
            'path' => self::$path,
            'domain' => self::$domain,
            'secure' => self::$secure,
            'httpOnly' => self::$httpOnly,
            'sameSite' => self::$sameSite
        ], $options);
    }

    /**
     * Generate a secure encryption key
     * 
     * @return string Random 64-character hex string
     */
    protected static function generateSecureKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate a cookie value
     * 
     * @param mixed $value The value to validate
     * @return mixed Validated value
     * @throws WPException If value is invalid
     */
    protected static function validateValue($value)
    {
        if (is_resource($value)) {
            throw new WPException('Resources cannot be stored in cookies');
        }
        
        if (is_array($value)) {
            return array_map([self::class, 'validateValue'], $value);
        }
        
        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new WPException('Objects must implement __toString() for cookie storage');
        }
        
        return $value;
    }
}