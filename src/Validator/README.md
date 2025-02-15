# <img src="https://raw.githubusercontent.com/sallarizadi/GapGPT/main/assets/validator-logo.png" width="100"> Validator Class

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://example.com/build)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![GitHub Stars](https://img.shields.io/github/stars/yourusername/validator.svg?style=social&label=Star&maxAge=3600)](https://github.com/yourusername/validator)

## Overview

The `Validator` class is a robust and flexible PHP utility designed to validate input data against a set of predefined rules. It offers extensive validation capabilities, including checks for required fields, data types (integer, numeric, string, etc.), and custom validation logic using regular expressions. This class is essential for ensuring that user-provided data meets specific criteria before being processed, enhancing the reliability and security of your applications.

## ✨ Key Features

-   **Static Methods**: All methods are static, providing easy access without the need to instantiate the class. This simplifies usage and promotes a clean coding style.

-   **Comprehensive Validation Rules**: Supports a wide array of validation rules, including:
    -   `required`: Ensures that a field is not empty.
    -   `integer`: Validates if a value is an integer.
    -   `numeric`: Validates if a value is numeric.
    -   `string`: Validates if a value is a string.
    -   `boolean`: Validates if a value is a boolean.
    -   `array`: Validates if a value is an array.
    -   `nullable`: Validates if a value is not empty but treats null values differently.
    -   `url`: Validates if a value is a valid URL.
    -   `email`: Validates if a value is a valid email address.
    -   `alpha`: Validates if a value contains only alphabetic characters.
    -   `alpha_num`: Validates if a value contains only alphanumeric characters.
    -   Custom regex patterns for specialized validation needs.

-   **Flexible Parameter-Based Validation**:
    -   `min`: Validates if a value's length is greater than or equal to a minimum length.
    -   `max`: Validates if a value's length is less than or equal to a maximum length.
    -   `between`: Validates if a value's length is within a specified range.
    -   `in`: Validates if a value is present in a given list of values.
    -   `not_in`: Validates if a value is not present in a given list of values.
    -   `regex`: Validates if a value matches a given regular expression.

-   **Error Handling**: Efficiently collects and returns validation errors for each input field, making it straightforward to identify and address issues.

## 🧰 Class Properties

-   `$fails`: A boolean flag indicating whether any validation has failed. It is set to `true` if any validation fails.
-   `$errors`: An associative array that stores validation errors. Keys are input fields, and values are lists of error messages for those fields.
-   `$validationRules`: An associative array mapping validation rule names to their corresponding method names within the class.

## ⚙️ Methods

### `public static function fails(): bool`

Checks if the validation process has failed. Returns `true` if any validation has failed, otherwise `false`.

### `public static function errors(): string`

Returns all validation errors as a single string, with each set of errors for a field on a new line. Useful for displaying all errors at once.

### `public static function make(array $input, array $validates): void`

The main method to initiate validation on the input.

-   Sets `$errors` to empty.
-   Sets `$fails` to `false`.
-   Loops through every key in the `$validates` array.
-   Runs validation based on the rules provided.
-   Reports any errors encountered during validation.

## 🚀 Usage Example

```php
use Woodpecker\Validator;

$input = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'age' => '25'
];

$validates = [
    'username' => 'required|string|min:3|max:20',
    'email' => 'required|email',
    'age' => 'required|integer|min:18|max:99'
];

Validator::make($input, $validates);

if (Validator::fails()) {
    echo Validator::errors();
} else {
    echo "Validation passed!";
}
