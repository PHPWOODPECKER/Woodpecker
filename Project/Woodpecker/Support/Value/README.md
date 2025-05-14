```markdown
# Woodpecker Utility Classes 🛠️

Powerful utility classes for working with collections, numbers, and strings in PHP.

![Utility Classes Diagram](https://example.com/utility-classes.png) *(optional diagram)*

## Table of Contents
- [Collection](#collection-)
- [Number](#number-)
- [String](#string-)
- [Installation](#installation-)
- [Best Practices](#best-practices-)

---

## Collection 🧺
A fluent, object-oriented wrapper for working with arrays of data.

### Features
- **50+ Methods** - Comprehensive array manipulation
- **Fluent Interface** - Chainable method calls
- **Immutable Operations** - Returns new instances
- **Type Safety** - Strict typing throughout

### Basic Usage
```php
use Woodpecker\Support\Collection;

// Create collection
$collection = new Collection([1, 2, 3, 4, 5]);

// Or use static maker
$collection = Collection::make(['a' => 1, 'b' => 2]);

// Chain operations
$result = $collection
    ->filter(fn($n) => $n > 2)
    ->map(fn($n) => $n * 2)
    ->toArray(); // [6, 8, 10]
```

### Key Methods
| Method | Description | Example |
|--------|-------------|---------|
| `map()` | Transform items | `->map(fn($i) => $i*2)` |
| `filter()` | Filter items | `->filter(fn($i) => $i > 5)` |
| `reduce()` | Reduce to single value | `->reduce(fn($c, $i) => $c + $i, 0)` |
| `groupBy()` | Group by key | `->groupBy('category')` |
| `sort()` | Sort collection | `->sort(SORT_NUMERIC)` |

### Advanced Examples
```php
// Group users by department
$usersByDept = Collection::make($users)
    ->groupBy('department');

// Calculate average score
$avgScore = Collection::make($scores)
    ->average();

// Chunk large dataset
$chunks = Collection::make($bigData)
    ->chunk(100);
```

---

## Number 🔢
Number manipulation and validation utilities.

### Features
- **Precision Handling** - Rounding, ceiling, flooring
- **Range Operations** - Clamping, in-range checks
- **Formatting** - Locale-aware number formats
- **Random Generation** - Cryptographically secure

### Basic Usage
```php
use Woodpecker\Support\Number;

// Check if number is in range
$inRange = Number::inRange(5, 1, 10); // true

// Format with thousands separator
$formatted = Number::format(1234567.89, 2); // "1,234,567.89"

// Generate secure random number
$random = Number::randomInt(1, 100);
```

### Key Methods
| Method | Description | Example |
|--------|-------------|---------|
| `inRange()` | Range check | `inRange(5, 1, 10)` |
| `format()` | Number formatting | `format(1234.56, 2)` |
| `clamp()` | Constrain value | `clamp(15, 0, 10)` |
| `average()` | Calculate average | `average([1, 2, 3])` |
| `randomInt()` | Secure random number | `randomInt(1, 100)` |

### Advanced Examples
```php
// Financial formatting
$price = Number::format(1999.99, 2, ',', '.'); // "1.999,99"

// Scientific clamping
$value = Number::clamp($input, -100, 100);

// Precise rounding
$result = Number::round(3.14159, 2, PHP_ROUND_HALF_UP);
```

---

## String 📝
String manipulation and analysis utilities.

### Features
- **Multi-byte Safe** - Full UTF-8 support
- **Position Handling** - Before/after/between operations
- **Security Focused** - Injection-resistant methods
- **Encoding Aware** - Proper encoding handling

### Basic Usage
```php
use Woodpecker\Support\Str;

// Create string object
$string = new Str('Hello World');

// Get substring
$sub = $string->substr(0, 5); // "Hello"

// Check contains
$hasWorld = $string->contains('World'); // true

// String manipulation
$result = $string->after('Hello')->limit(5); // " World"
```

### Key Methods
| Method | Description | Example |
|--------|-------------|---------|
| `contains()` | Check for substring | `contains('needle')` |
| `substr()` | Get substring | `substr(0, 5)` |
| `limit()` | Truncate with ellipsis | `limit(10)` |
| `between()` | Extract between delimiters | `between('[', ']')` |
| `reverse()` | Reverse string | `reverse()` |

### Advanced Examples
```php
// Extract domain from email
$domain = (new Str('user@example.com'))
    ->after('@')
    ->before('.'); // "example"

// Multi-byte safe operations
$length = (new Str('こんにちは'))
    ->length(); // 5

// Secure string building
$result = (new Str('{user_input}'))
    ->betweenFirst('{', '}')
    ->limit(100);
```

---

## Best Practices ✅

### For Collections
1. **Chain Methods** - Leverage fluent interface
2. **Use Mappers** - Prefer `map()` over foreach
3. **Immutable** - Treat as immutable
4. **Type Hint** - Use for array parameters

### For Numbers
1. **Precision** - Specify decimal places
2. **Range Checks** - Validate early
3. **Secure Randoms** - Always use `randomInt()`
4. **Locale** - Consider regional formats

### For Strings
1. **UTF-8** - Always assume multi-byte
2. **Validation** - Sanitize before manipulation
3. **Limits** - Set reasonable length limits
4. **Encoding** - Specify when needed

---

## Performance Tips ⚡

**Collections:**
```php
// Faster for large datasets
$filtered = $collection->filter(...);

// Slower - creates multiple collections
$collection->map()->filter()->sort();
```

**Strings:**
```php
// Single operation is faster
$string->limit(100);

// Multiple operations have overhead
$string->after('a')->before('b')->limit(100);
```

---

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted with care by Woodpecker**
```