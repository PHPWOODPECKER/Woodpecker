<?php
namespace Woodpecker\Support;

use Woodpecker\WPException;
use Woodpecker\Cryption;
use Woodpecker\Facade;

/**
 * Secure Session Management Class
 *
 * Provides a secure session handling system with multiple security features:
 * - Encrypted session data storage
 * - CSRF protection tokens
 * - Session fixation prevention
 * - Strict input validation
 * - Automatic session expiration
 * - Secure cookie configuration
 *
 * @package Woodpecker\Support
 */

  class Session extends Facade {
    
    /**
     * The name of the session cookie
     * - Used in session_name() and cookie settings
     * - Default: 'SECURE_SESSION'
     * - Should be changed in production for security through obscurity
     */
    protected static string $sessionName = "SECURE_SESSION";
  
    /**
     * The key used to store encrypted session data in $_SESSION superglobal
     * - All session data will be stored under this key in encrypted form
     * - Default: 'ENCRYPTED_DATA'
     * - Prevents direct access to session values
     */
    protected static string $sessionDataKey = "ENCRYPTED_DATA";
  
    /**
     * Session timeout in seconds
     * - Default: 3600 (1 hour)
     * - After this period of inactivity, session will expire
     * - Used for both server-side and cookie expiration
     */
    protected static int $timeout = 3600;
  
    /**
     * Encryption secret key for session data
     * - Used by Cryption class to encrypt/decrypt session values
     * - Must be set before session initialization
     * - Should be a strong cryptographic key (32+ bytes recommended)
     */
    protected static string $secretKey;
  
    /**
     * Initialization status flag
     * - Prevents multiple initializations
     * - Ensures session is properly configured before use
     * - Set to true after successful initialization
     */
    protected static bool $initialized = false;
  
    /**
     * Key name for CSRF token in session
     * - Default: 'CSRF_TOKEN'
     * - Used to store/retrieve the anti-CSRF token
     * - Should not be changed unless necessary
     */
    protected static string $csrfTokenKey = "CSRF_TOKEN";
  
    /**
     * Initialize the secure session system
     *
     * @param array $config Configuration options [
     *     'sessionName'    => string,  Custom session name
     *     'sessionKey'     => string,  Key for encrypted session data
     *     'secretKey'      => string,  Encryption secret key
     *     'timeout'        => int      Session timeout in seconds
     * ]
     * @throws WPException If configuration is invalid or session fails to start
     */
    public static function init(array $config = null): void
    {
      if (self::$initialized) {
        return;
      }
      
      $configList = require_once(__DIR__ . '/../../../config.php');
      $config = $config ?? $configList["Session"] ;
  
      self::applyConfiguration($config);
      self::validateConfiguration();
      self::configureSession();
      self::startSession();
      self::validateSession();
      self::generateCsrfToken();
      self::updateLastActivity();
  
      self::$initialized = true;
    }
  
    /**
     * Store a value in the encrypted session
     *
     * @param string $key   The session key
     * @param mixed $value  The value to store (arrays allowed, objects prohibited)
     * @throws WPException If session isn't initialized or value is invalid
     */
    public static function set(string $key, $value): void
    {
      self::ensureInitialized();
  
      $sanitizedKey = self::sanitizeKey($key);
      $validatedValue = self::validateValue($value);
      
      $validatedValue = is_array($validatedValue) ? json_encode($validatedValue, true) : $validatedValue;
  
      $_SESSION[self::$sessionDataKey][$sanitizedKey] = Cryption::encrypt(
        $validatedValue,
        self::$secretKey
      );
    }
  
    /**
     * Retrieve a value from the encrypted session
     *
     * @param string $key      The session key
     * @param mixed $default   Default value if key doesn't exist
     * @return mixed           The decrypted value or default
     * @throws WPException If session isn't initialized
     */
    public static function get(string $key, $default = null)
    {
      self::ensureInitialized();
  
      $sanitizedKey = self::sanitizeKey($key);
  
      if (!isset($_SESSION[self::$sessionDataKey][$sanitizedKey])) {
        return $default;
      }
  
      $date = Cryption::decrypt(
        $_SESSION[self::$sessionDataKey][$sanitizedKey],
        self::$secretKey
      );
      
      $decode = json_decode($date, true);
      
      return json_last_error() === JSON_ERROR_NONE ? $decode : $data;
    }
  
    /**
     * Check if a session key exists
     *
     * @param string $key The key to check
     * @return bool True if key exists
     * @throws WPException If session isn't initialized
     */
    public static function has(string $key): bool
    {
      self::ensureInitialized();
      return isset($_SESSION[self::$sessionDataKey][self::sanitizeKey($key)]);
    }
  
    /**
     * Completely destroy the current session
     * - Clears session data
     * - Destroys session file
     * - Expires session cookie
     */
    public static function destroy(): void
    {
      if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
  
        $params = session_get_cookie_params();
        setcookie(
          session_name(),
          "",
          time() - 42000,
          $params["path"],
          $params["domain"],
          $params["secure"],
          $params["httponly"]
        );
      }
  
      self::$initialized = false;
    }
  
    /**
     * Regenerate session ID to prevent session fixation
     *
     * @param bool $deleteOldSession Whether to delete the old session file
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
      session_regenerate_id($deleteOldSession);
      self::generateCsrfToken();
    }
  
    /**
     * Get the current CSRF protection token
     *
     * @return string The CSRF token
     * @throws WPException If session isn't initialized
     */
    public static function getCsrfToken(): string
    {
      self::ensureInitialized();
      return $_SESSION[self::$csrfTokenKey] ?? "";
    }
  
    /**
     * Validate a provided CSRF token
     *
     * @param string $token The token to validate
     * @return bool True if token is valid
     * @throws WPException If session isn't initialized
     */
    public static function validateCsrfToken(string $token): bool
    {
      return hash_equals(self::getCsrfToken(), $token);
    }
  
    /**
     * Apply configuration settings
     *
     * @param array $config Configuration options
     */
    protected static function applyConfiguration(array $config): void
    {
      self::$sessionName = $config["sessionName"] ?? self::$sessionName;
      self::$sessionDataKey = $config["sessionKey"] ?? self::$sessionDataKey;
      self::$secretKey = $config["secretKey"] ?? self::generateSecureKey();
      self::$timeout = $config["timeout"] ?? self::$timeout;
    }
  
    /**
     * Validate that required configuration is present
     *
     * @throws WPException If any required configuration is missing
     */
    protected static function validateConfiguration(): void
    {
      if (empty(self::$sessionName)) {
        throw new WPException("Session name cannot be empty");
      }
  
      if (empty(self::$sessionDataKey)) {
        throw new WPException("Session data key cannot be empty");
      }
  
      if (empty(self::$secretKey)) {
        throw new WPException("Secret key cannot be empty");
      }
    }
  
    protected static function configureSession(): void
    {
      session_name(self::$sessionName);
  
      $cookieParams = [
        "lifetime" => self::$timeout,
        "path" => "/",
        "domain" => $_SERVER["HTTP_HOST"] ?? "",
        "secure" => true,
        "httponly" => true,
        "samesite" => "Strict",
      ];
  
      session_set_cookie_params($cookieParams);
  
      if (!headers_sent()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
      }
    }
  
    /**
     * Start PHP session if not already active
     */
    protected static function startSession(): void
    {
      if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
      }
    }
  
    /**
     * Validate session state and timeout
     *
     * @throws WPException If session has expired
     */
    protected static function validateSession(): void
    {
      if (
        isset($_SESSION["LAST_ACTIVE"]) &&
        time() - $_SESSION["LAST_ACTIVE"] > self::$timeout
      ) {
        self::destroy();
        throw new WPException("Session expired");
      }
  
      if (!isset($_SESSION[self::$sessionDataKey])) {
        $_SESSION[self::$sessionDataKey] = [];
      }
    }
  
    /**
     * Update last activity timestamp
     */
    protected static function updateLastActivity(): void
    {
      $_SESSION["LAST_ACTIVE"] = time();
    }
  
    /**
     * Generate a new CSRF protection token
     */
    protected static function generateCsrfToken(): void
    {
      $_SESSION[self::$csrfTokenKey] = bin2hex(random_bytes(32));
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
     * Sanitize a session key name
     *
     * @param string $key The input key
     * @return string Sanitized key containing only alphanumerics and underscores
     */
    protected static function sanitizeKey(string $key): string
    {
      return preg_replace("/[^a-zA-Z0-9_]/", "", $key);
    }
  
    /**
     * Validate a session value
     *
     * @param mixed $value The value to validate
     * @return mixed Validated value
     * @throws WPException If value is an object (not allowed)
     */
    protected static function validateValue($value)
    {
      if (is_array($value)) {
        return array_map([self::class, "validateValue"], $value);
      }
  
      if (is_object($value)) {
        throw new WPException("Objects cannot be stored in session");
      }
  
      return $value;
    }
  
    /**
     * Ensure session is initialized before operations
     *
     * @throws WPException If session isn't initialized
     */
    protected static function ensureInitialized(): void
    {
      if (!self::$initialized) {
        throw new WPException(
          "Session not initialized. Call Session::init() first"
        );
      }
    }
    
    public static function isInitialized(): bool {
      return self::$initialized;
    }
  }
