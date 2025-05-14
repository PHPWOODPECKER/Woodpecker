``markdown
# Woodpecker Facade System 🎭

A elegant facade implementation for PHP applications that provides static interface to objects while maintaining singleton pattern.

![Facade Pattern Diagram](https://example.com/facade-pattern.png) *(optional diagram)*

## Features ✨
- **Singleton Management** - Automatic instance handling
- **Static Proxy** - Call object methods statically
- **Dynamic Method Handling** - Magic `__call` and `__callStatic` support
- **Type Safety** - Strict type declarations
- **Error Handling** - Clear exception messages

## Basic Usage 🛠️

### Creating a Facade
```php
namespace App\Facades;

use Woodpecker\Facade;

class Logger extends Facade
{
    protected static $instance;
    
    public function log(string $message): void
    {
        // Implementation
    }
}
```

### Using the Facade
```php
use App\Facades\Logger;

// Static call (proxied to instance)
Logger::log('Hello World');

// Instance call
$logger = Logger::getInstance();
$logger->log('Direct call');
```

## Advanced Features 🔥

### Method Forwarding
All undefined static calls are automatically forwarded to the instance:

```php
Logger::debug('Test'); // Proxied to $instance->debug()
```

### Singleton Control
Override `getInstance()` for custom initialization:

```php
protected static function getInstance(): static
{
    if (static::$instance === null) {
        static::$instance = new LoggerService();
    }
    return static::$instance;
}
```

## API Reference 📚

| Method | Description |
|--------|-------------|
| `getInstance()` | Gets singleton instance |
| `__callStatic()` | Handles static method calls |
| `__call()` | Handles dynamic method calls |

## Best Practices ✅
1. **Use for Service Access** - Ideal for frequently used services
2. **Keep Lightweight** - Avoid complex logic in facades
3. **Document Methods** - Clearly list available methods
4. **Test Both Contexts** - Verify static and instance calls

## Error Handling ⚠️
Throws `WPException` when:
- Calling undefined methods
- Instance creation fails

Example error:
```plaintext
Method debug does not exist in App\Facades\Logger
```

## Example Implementation 🎯

### Database Facade
```php
namespace App\Facades;

use Woodpecker\Facade;

class DB extends Facade
{
    protected static $instance;
    
    public function query(string $sql): array
    {
        // Database implementation
    }
    
    public function transaction(callable $callback): void
    {
        // Transaction handling
    }
}

// Usage:
DB::query('SELECT * FROM users');
```

## Why Use This Facade? 🤔
- Provides clean static interface
- Maintains testability
- Reduces global state

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted by [Your Name]**
```