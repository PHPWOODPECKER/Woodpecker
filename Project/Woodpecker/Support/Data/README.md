Here's a comprehensive `README.md` for the Cookie and Session components of the Woodpecker framework:

```markdown
# Woodpecker Cookie & Session Management 🍪🔒

Secure client-side (Cookie) and server-side (Session) state management for PHP applications with built-in security features.

![State Management Flow](https://example.com/state-management.png) *(optional diagram)*

## Table of Contents
- [Cookie Management](#cookie-management-)
- [Session Management](#session-management-)
- [Security Features](#security-features-)
- [Installation](#installation-)
- [Best Practices](#best-practices-)

---

## Cookie Management 🍪
Secure encrypted cookie handling with automatic validation.

### Features
- **Encrypted Storage** - AES-256 encrypted cookie values
- **Secure Configuration** - HttpOnly, Secure, SameSite flags
- **Automatic Validation** - Strict input sanitization
- **CSRF Protection** - Built-in token generation
- **Tamper Detection** - HMAC verification

### Basic Usage
```php
use Woodpecker\Support\Cookie;

// Initialize with config (typically in bootstrap)
Cookie::init([
    'secretKey' => 'your-32-byte-secret-key',
    'lifetime' => 86400, // 1 day
    'secure' => true
]);

// Set secure cookie
Cookie::set('user_token', 'abc123', [
    'lifetime' => 3600 // Override default
]);

// Get cookie value
$token = Cookie::get('user_token');

// Delete cookie
Cookie::delete('user_token');
```

### Configuration Options
| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `prefix` | string | `SECURE_` | Cookie name prefix |
| `secretKey` | string | *required* | Encryption key (32+ bytes) |
| `lifetime` | int | 3600 | Cookie lifetime in seconds |
| `encrypt` | bool | true | Enable value encryption |
| `path` | string | `/` | Cookie path |
| `domain` | string | Current domain | Cookie domain scope |
| `secure` | bool | true | HTTPS-only cookies |
| `httpOnly` | bool | true | Prevent JS access |
| `sameSite` | string | `Strict` | CSRF protection |

---

## Session Management 🔐
Encrypted server-side session storage with advanced security.

### Features
- **Military-Grade Encryption** - AES-256 + HMAC
- **Automatic Expiry** - Configurable timeout
- **CSRF Tokens** - Built-in protection
- **Fixation Prevention** - Session regeneration
- **Strict Validation** - Type and size checks

### Basic Usage
```php
use Woodpecker\Support\Session;

// Initialize session (typically in bootstrap)
Session::init([
    'secretKey' => 'your-32-byte-secret-key',
    'timeout' => 1800 // 30 minutes
]);

// Store session data
Session::set('user_id', 12345);

// Retrieve data
$userId = Session::get('user_id');

// CSRF protection
$token = Session::getCsrfToken();
if (Session::validateCsrfToken($inputToken)) {
    // Valid request
}

// Destroy session
Session::destroy();
```

### Configuration Options
| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `sessionName` | string | `SECURE_SESSION` | Session cookie name |
| `sessionKey` | string | `ENCRYPTED_DATA` | Internal data key |
| `secretKey` | string | *required* | Encryption key (32+ bytes) |
| `timeout` | int | 3600 | Session timeout in seconds |

---

## Security Features 🛡️

### Common Security Measures
- **Encryption-at-Rest** - All data encrypted before storage
- **Strict Validation** - Rejects invalid input formats
- **Secure Headers** - Automatic security headers
- **Activity Timeout** - Automatic session expiration

### Cookie-Specific Protections
- **HttpOnly** - Prevents XSS cookie theft
- **SameSite** - Prevents CSRF attacks
- **Secure Prefix** - Obscures cookie names
- **Value Signing** - Detects tampering

### Session-Specific Protections
- **Session Regeneration** - Prevents fixation
- **IP Binding** - Optional IP validation
- **Token Rotation** - Per-request CSRF tokens
- **Browser Fingerprinting** - Additional validation

---

Configure in your config file:
```php
// config/cookie.php
return [
    'secretKey' => env('COOKIE_SECRET'),
    'sameSite' => 'Lax'
];

// config/session.php 
return [
    'secretKey' => env('SESSION_SECRET'),
    'timeout' => 86400
];
```

---

## Best Practices ✅

### For Cookies
1. **Always Encrypt** - Enable `encrypt` flag
2. **Minimal Lifetime** - Set shortest practical duration
3. **Restrict Paths** - Use specific paths, not `/`
4. **Secure Domain** - Explicitly set domain
5. **Regular Rotation** - Change secret keys periodically

### For Sessions
1. **Early Initialization** - Start session first
2. **Regenerate ID** - After login/logout
3. **Destroy Properly** - Use `destroy()` not `unset()`
4. **Validate Activity** - Check `LAST_ACTIVE`
5. **Secure Storage** - Use encrypted session files

### Common
```php
// Secure configuration example
[
    'secretKey' => bin2hex(random_bytes(32)), // 64 char hex
    'secure' => true, // HTTPS only
    'httpOnly' => true, // No JS access
    'sameSite' => 'Strict' // CSRF protection
]
```

---

## Troubleshooting ⚠️

### Common Issues
**Cookie Not Persisting**:
- Verify domain/path settings
- Check secure flag matches HTTPS
- Ensure headers not sent before setcookie()

**Session Data Lost**:
- Confirm session_start() before output
- Check disk space for session files
- Verify timeout not too short

**Encryption Errors**:
- Ensure secret key hasn't changed
- Verify value size limits
- Check for special characters

---

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted with care by Woodpecker**
```