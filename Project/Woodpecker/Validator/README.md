```markdown
# Woodpecker Validator System 🛡️

A robust validation system for PHP applications that provides clean, intuitive data validation with extensive rule support.

![Validation Flow Diagram](https://example.com/validation-flow.png) *(optional diagram)*

## Features ✨
- **20+ Built-in Rules** - From basic types to complex constraints
- **Custom Rule Support** - Via regex patterns
- **Parameterized Rules** - Min, max, between, in/not_in lists
- **Error Collection** - Detailed error messages
- **Simple API** - Easy to learn and use
- **Type Safety** - Strict type declarations

## Basic Usage 🛠️

### Simple Validation
```php
use Woodpecker\Validator;

$data = ['email' => 'user@example.com', 'age' => 25];
$rules = [
    'email' => 'required|email',
    'age' => 'required|integer|between:18,99'
];

Validator::make($data, $rules);

if (Validator::fails()) {
    echo Validator::errors();
}
```

### Available Validation Rules
| Rule | Description | Example |
|------|-------------|---------|
| `required` | Field must exist | `'username' => 'required'` |
| `integer` | Valid integer | `'age' => 'integer'` |
| `email` | Valid email format | `'email' => 'email'` |
| `min:x` | Minimum length/value | `'password' => 'min:8'` |
| `between:x,y` | Range check | `'age' => 'between:18,65'` |
| `in:a,b,c` | Value in list | `'color' => 'in:red,green,blue'` |
| `regex:pattern` | Custom regex | `'phone' => 'regex:/^\+?[0-9]{10,15}$/'` |

## Advanced Features 🔥

### Parameterized Rules
```php
$rules = [
    'password' => 'required|min:8|max:32',
    'discount' => 'between:5,50' // Percentage between 5-50
];
```

### Custom Regex Validation
```php
$rules = [
    'phone' => 'regex:/^\+?[0-9]{10,15}$/',
    'zipcode' => 'regex:/^[0-9]{5}(-[0-9]{4})?$/'
];
```

### Complex Validation Sets
```php
$rules = [
    'username' => 'required|alpha_dash|between:3,20',
    'email' => 'required|email|not_in:admin@example.com,root@example.com',
    'age' => 'nullable|integer|min:13'
];
```

## API Reference 📚

### Core Methods
| Method | Description |
|--------|-------------|
| `Validator::make()` | Execute validation |
| `Validator::fails()` | Check if validation failed |
| `Validator::errors()` | Get all error messages |

### Validation Methods
| Method | Validates |
|--------|-----------|
| `isRequired()` | Non-empty value |
| `isInteger()` | Integer value |
| `isEmail()` | Valid email format |
| `validateMin()` | Minimum value/length |
| `validateBetween()` | Value within range |

## Error Handling ⚠️
Validation errors are collected with clear messages:
```plaintext
Invalid value for age with rule between:18,65.
email is required.
```

## Best Practices ✅
1. **Validate Early** - Validate as soon as data is received
2. **Use Specific Rules** - Prefer `email` over generic `regex`
3. **Combine Rules** - Chain related validations
4. **Sanitize After** - Clean data post-validation
5. **Test Edge Cases** - Empty, null, boundary values

## Example Validation 🎯

### User Registration
```php
$userData = [
    'username' => 'john_doe42',
    'email' => 'john@example.com',
    'password' => 'secure123',
    'age' => 25
];

$rules = [
    'username' => 'required|alpha_dash|between:3,20',
    'email' => 'required|email',
    'password' => 'required|min:8',
    'age' => 'nullable|integer|min:13'
];

Validator::make($userData, $rules);

if (Validator::fails()) {
    // Handle errors
    $errors = Validator::errors();
}
```

## Extending the Validator 🔧
Add custom rules by extending the class:

```php
class CustomValidator extends Woodpecker\Validator {
    protected static $validationRules = [
        ...parent::$validationRules,
        'strong_password' => 'isStrongPassword'
    ];
    
    private static function isStrongPassword(string $value): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $value);
    }
}
```

## Why This Validator? 🤔
- No external dependencies
- Clear, readable validation rules
- Extensible architecture
- Detailed error reporting
- Consistent with Woodpecker ecosystem

## License 📜
MIT License - See [LICENSE](LICENSE) for details.

---
**Part of Woodpecker Framework** 🌳  
**Crafted with care by Woodpecker**
```