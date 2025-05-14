```markdown
# Woodpecker Cryption System 🔒

A secure encryption/decryption module using AES-256-GCM, providing both confidentiality and integrity for your data.

![Encryption Flow](https://example.com/encryption-flow.png) *(optional diagram)*

## Features ✨
- **AES-256-GCM** - Military-grade encryption algorithm
- **Authenticated Encryption** - Built-in integrity checking
- **Secure IV Generation** - Cryptographically strong random IVs
- **Simple API** - Easy encrypt/decrypt methods
- **Exception Handling** - Secure failure modes

## Basic Usage 🚀

### Encryption
```php
use Woodpecker\Cryption;

$secretKey = 'your-32-byte-secure-key'; // Store securely!
$data = 'Sensitive information';

$encrypted = Cryption::encrypt($data, $secretKey);
// Returns base64-encoded string containing IV + tag + ciphertext
```

### Decryption
```php
$decrypted = Cryption::decrypt($encrypted, $secretKey);
// Returns original plaintext or throws WPException on failure
```

## Security Best Practices 🔐

### Key Management
```php
// Generate a secure key (do this once)
$key = bin2hex(random_bytes(32)); // 64-character hex string

// Store securely using environment variables or key management service
putenv("APP_ENCRYPTION_KEY=$key");
```

### Data Handling
```php
// Always encrypt before storing sensitive data
$encryptedPassword = Cryption::encrypt($userPassword, env('APP_ENCRYPTION_KEY'));

// Decrypt only when needed
$decryptedPassword = Cryption::decrypt($encryptedPassword, env('APP_ENCRYPTION_KEY'));
```

## Advanced Configuration ⚙️

### Algorithm Details
| Parameter | Value | Description |
|-----------|-------|-------------|
| Algorithm | AES-256-GCM | Authenticated encryption |
| Key Length | 256-bit | 32 byte key required |
| IV Length | 12 bytes | Generated automatically |
| Tag Length | 16 bytes | Authentication tag |

### Custom Integration
```php
class SecureStorage {
    private string $encryptionKey;
    
    public function __construct(string $key) {
        if (strlen($key) !== 32) {
            throw new \InvalidArgumentException('Key must be 32 bytes');
        }
        $this->encryptionKey = $key;
    }
    
    public function store(string $key, mixed $value): void {
        $encrypted = Cryption::encrypt(serialize($value), $this->encryptionKey);
        // Save to database or file system
    }
    
    public function retrieve(string $key): mixed {
        $encrypted = // Retrieve from storage
        return unserialize(Cryption::decrypt($encrypted, $this->encryptionKey));
    }
}
```

## Error Handling ⚠️

### Common Exceptions
```php
try {
    $decrypted = Cryption::decrypt($data, $key);
} catch (WPException $e) {
    // Handle decryption failures:
    // - Corrupted data
    // - Invalid key
    // - Tampered content
    logger()->error('Decryption failed: ' . $e->getMessage());
    return null;
}
```

## Why AES-256-GCM? 🤔

1. **Confidentiality** - AES-256 is NSA-approved for top secret data
2. **Integrity** - GCM provides authentication preventing tampering
3. **Performance** - Hardware accelerated on modern processors
4. **Standardized** - NIST recommended encryption mode

## Migration Guide 🔄

### From Legacy Encryption
```php
// Old: AES-256-CBC
$legacyEncrypted = legacy_encrypt($data, $key);

// Migration:
$decrypted = legacy_decrypt($legacyEncrypted, $oldKey);
$reencrypted = Cryption::encrypt($decrypted, $newKey);
```

## Frequently Asked Questions ❓

**Q: How should I store the encryption key?**  
A: Use environment variables (never in code) or a key management service like AWS KMS.

**Q: Can I encrypt large files?**  
A: For files > 1MB, consider chunking or streaming encryption.

**Q: How do I rotate keys?**  
A: Decrypt with old key and re-encrypt with new key during maintenance windows.

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted with care by [Your Name]**
```
