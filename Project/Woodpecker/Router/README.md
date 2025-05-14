# Woodpecker Router System 🚀

A lightweight yet powerful PHP router system for modern web applications, featuring route matching, middleware support, and RESTful routing.

![Router System Diagram](https://example.com/router-diagram.png) *(optional diagram)*

## Features ✨
- **RESTful Routing** - Supports GET, POST, PUT, DELETE, HEAD, OPTIONS
- **Middleware Pipeline** - Register and execute middleware functions
- **Dynamic URL Parameters** - With optional pattern matching
- **Request Sanitization** - Built-in XSS protection
- **Rate Limiting** - Built-in support via `RateLimiter`
- **Dependency Injection** - Automatic method parameter resolution
- **Error Handling** - Custom 404 and 429 error pages

## Basic Usage 🛠️

### Defining Routes
```php
use Woodpecker\Router;

// Simple GET route
Router::get(['id', 'name'], function($id, $name) {
    return "User $id: $name";
});

// Controller route
Router::post(['email', 'password'], [
    'class' => 'UserController',
    'method' => 'login'
]);

// Route with middleware
Router::put(['user_id', 'data'], 'UserController@update', 'auth');
```

### Middleware
```php
Router::middleware('auth', function() {
    if (!isset($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }
});
```

### Dynamic URL Routing
```php
Router::url('/users/{id:\d+}/profile', function($id) {
    return "Showing profile for user $id";
});

// With rate limiting
Router::url([
    'url' => '/api/data',
    'rate' => 60  // 60 requests/minute
], 'ApiController@fetchData');
```

## Advanced Features 🔥

### Method Injection
Controllers automatically receive resolved parameters:

```php
class UserController {
    public function show(Request $request, $userId) {
        // $request is auto-injected
    }
}
```

### Rate Limiting
```php
Router::url('/api/limited', [
    'class' => 'ApiController',
    'method' => 'sensitiveOperation'
], 'auth', ['rate' => 30]); // 30 requests/minute
```

### Multi-method Routes
```php
Router::multiMethod('GET|POST', ['param'], 'Controller@handle');
```

## Error Handling
The router includes built-in error pages:
- `404Error.php` - Route not found
- `429Error.php` - Rate limit exceeded

## API Reference 📚

### Core Methods
| Method | Description |
|--------|-------------|
| `Router::run()` | Executes the router |
| `Router::get()` | GET route |
| `Router::post()` | POST route |
| `Router::url()` | Advanced URL routing |

### Request Object
The `Request` object provides:
- `->ip()` - Client IP address
- `->all()` - All parameters
- `->input()` - GET parameters With Key

## Best Practices ✅
1. **Organize routes** by domain (web.php, api.php)
2. **Use middleware** for cross-cutting concerns
3. **Validate inputs** before processing
4. **Rate limit** sensitive endpoints
5. **Keep controllers lean** - delegate to services

## Examples 🎯

### RESTful API
```php
Router::get(['id'], 'UserController@show');
Router::post([], 'UserController@store');
Router::put(['id'], 'UserController@update');
Router::delete(['id'], 'UserController@destroy');
```

## Troubleshooting ⚠️

### Common Issues
**404 Errors**:
- Verify route parameters match exactly
- Check HTTP method matches

**Middleware Not Firing**:
- Ensure middleware is registered before routes
- Verify middleware name spelling

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---

**Crafted with care by Woodpecker** 🔨  
*Part of the Woodpecker Framework Ecosystem*
```