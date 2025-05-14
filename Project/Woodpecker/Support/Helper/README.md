Here's a comprehensive `README.md` covering all the provided support classes in the Woodpecker framework:

```markdown
# Woodpecker Support Components 🛠️

A collection of essential support classes for the Woodpecker PHP framework, providing core functionality for web applications.

![Woodpecker Components](https://example.com/woodpecker-components.png) *(optional diagram)*

## Table of Contents
- [RateLimiter](#ratelimiter-)
- [Redirect](#redirect-)
- [Response](#response-)
- [Timer](#timer-)
- [Request](#request-)

---

## RateLimiter ⏱️
Protects your application from excessive requests by enforcing rate limits.

### Features
- Multiple time windows (minute/hour/day)
- Session-based tracking
- Automatic reset after time window
- Remaining attempts tracking

### Usage
```php
use Woodpecker\Support\RateLimiter;

// Limit to 10 requests per minute per IP
$limiter = RateLimiter::by($request->ip())->perMinute(10);

if (!$limiter->attempt()) {
    // Return 429 Too Many Requests
    http_response_code(429);
    exit;
}

echo "Remaining attempts: " . $limiter->remaining();
```

### Methods
| Method | Description |
|--------|-------------|
| `by()` | Set rate limit key (typically IP) |
| `perMinute()` | Set limit per minute |
| `attempt()` | Check/increment counter |
| `remaining()` | Get remaining attempts |
| `retryAfter()` | Get seconds until next attempt |

---

## Redirect 🔀
Handles HTTP redirects with support for status codes and session flashing.

### Features
- Automatic URL construction
- Proper HTTP status codes
- Session flashing
- Action redirection

### Usage
```php
use Woodpecker\Support\Redirect;

// Simple redirect
Redirect::to('/dashboard');

// With status code
Redirect::to('/login', 301);

// With flashed session data
Redirect::with('error', 'Invalid credentials')->to('/login');

// To controller action
Redirect::action('AuthController@login');
```

### Methods
| Method | Description |
|--------|-------------|
| `to()` | Redirect to URL/path |
| `action()` | Redirect to controller action |
| `with()` | Flash session data |

---

## Response 📨
Builds and sends HTTP responses with headers and status codes.

### Features
- Status code management
- Header manipulation
- JSON responses
- Security headers

### Usage
```php
use Woodpecker\Support\Response;

// Simple response
Response::setBody('Hello World')->send();

// JSON response
Response::json(['status' => 'success']);

// With custom headers
Response::headers([
    'X-Custom' => 'Value',
    'Cache-Control' => 'no-cache'
])->setBody('Content')->send();
```

### Methods
| Method | Description |
|--------|-------------|
| `setStatusCode()` | Set HTTP status code |
| `setHeader()` | Set single header |
| `headers()` | Set multiple headers |
| `json()` | Send JSON response |
| `send()` | Output response |

---

## Timer ⏳
Provides timer functionality for delayed and periodic execution.

### Features
- One-time timers
- Repeating timers
- Precision timing
- Async execution

### Usage
```php
use Woodpecker\Support\Timer;

// One-time timer
Timer::Timer(fn() => log('Done!'), 5000); // After 5 seconds

// Daily repeating timer
$id = Timer::dailyTimer(fn() => backup(), 86400000); // Daily

// Start the tick loop (typically in background process)
Timer::tick();

// Stop specific timer
Timer::stopDailyTimer($id);
```

### Methods
| Method | Description |
|--------|-------------|
| `Timer()` | Create one-time timer |
| `dailyTimer()` | Create repeating timer |
| `tick()` | Process active timers |
| `stopTick()` | Stop all timers |
| `stopDailyTimer()` | Stop specific timer |

---

## Request 🤲
Encapsulates HTTP request data with validation and sanitization.

### Features
- Input sanitization
- Built-in validation
- Convenient data access
- Request introspection

### Usage
```php
use Woodpecker\Support\Request;

$request = new Request($_POST);

// Get input with validation
try {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:8'
    ]);
} catch (WPException $e) {
    // Handle validation errors
}

// Get specific input
$email = $request->input('email');

// Check request type
if ($request->isMethod('POST')) {
    // Handle POST
}
```

### Methods
| Method | Description |
|--------|-------------|
| `validate()` | Validate input data |
| `input()` | Get specific input |
| `all()` | Get all inputs |
| `has()` | Check if input exists |
| `isMethod()` | Check HTTP method |

---

## Best Practices ✅
1. **Rate Limiting** - Protect all public endpoints
2. **Validation** - Always validate before processing
3. **Sanitization** - Use Request class for clean input
4. **Proper Redirects** - Use 301 for permanent, 302 for temporary
5. **Timers** - Run tick() in background process

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted with care by Woodpecker**
```