<?php
namespace Woodpecker;

/**
 * Class Validator
 *
 * Provides a set of static methods to validate input data based on predefined rules.
 * This class supports various types of validation such as required, integer, numeric, string, etc.,
 * and allows custom rules through regex and parameter-based constraints like min, max, between, etc.
 */
class Validator {
    /**
     * @var bool $fails
     * A flag indicating if any validation has failed.
     * It is set to `true` if all validations passed, otherwise `false`.
     */
    private static $fails;

    /**
     * @var array $errors
     * An associative array that stores validation errors, where keys are input fields and values
     * are lists of error messages for those fields.
     */
    private static $errors;

    /**
     * @var array $validationRules
     * An associative array that maps validation rule names to their corresponding method names within this class.
     * This array serves as a registry to lookup validation methods dynamically.
     */
    private static $validationRules = [
        'required' => 'isRequired',
        'integer' => 'isInteger',
        'numeric' => 'isNumeric',
        'string' => 'isString',
        'boolean' => 'isBoolean',
        'array' => 'isArray',
        'nullable' => 'isNullable',
        'url' => 'isUrl',
        'email' => 'isEmail',
        'alpha' => 'isAlpha',
        'alpha_dash' => 'isAlphaDash',
        'alpha_num' => 'isAlphaNum'
    ];

    /**
     * Checks if the validation process has failed.
     *
     * @return bool True if any of the validations failed, otherwise false.
     */
    public static function fails(): bool
    {
        return self::$fails;
    }

    /**
     * Returns all the validation errors as a single string.
     *
     * @return string A string containing all validation errors, each set of errors for a field on a new line.
     */
    public static function errors(): string
    {
        $errors = "";
        foreach (self::$errors as $key => $errorList) {
            $errors .= implode(", ", $errorList) . "\n";
        }
        return $errors;
    }
    
          /**
     * Validates if a value is not empty.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is not empty, otherwise false.
     */
    private static function isRequired(string $value): bool
    {
        return !empty($value);
    }

    /**
     * Validates if a value is an integer.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is an integer, otherwise false.
     */
    private static function isInteger(string $value): bool 
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validates if a value is numeric.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is numeric, otherwise false.
     */
    private static function isNumeric(string $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validates if a value is a string.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is a string, otherwise false.
     */
    private static function isString(string $value): bool
    {
        return is_string($value);
    }

    /**
     * Validates if a value is a boolean.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is a boolean, otherwise false.
     */
    private static function isBoolean(string $value): bool
    {
        return is_bool($value);
    }

    /**
     * Validates if a value is an array.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is an array, otherwise false.
     */
    private static function isArray(string $value): bool
    {
        return is_array($value);
    }

     /**
     * Validates if a value is not empty (similar to required but treats null differently).
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is not empty, otherwise false.
     */
    private static function isNullable(string $value): bool
    {
       return !empty($value);
    }

    /**
     * Validates if a value is a URL.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is a valid URL, otherwise false.
     */
    private static function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validates if a value is an email address.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value is a valid email address, otherwise false.
     */
    private static function isEmail(string $value): bool 
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates if a value contains only alphabetic characters.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value contains only alphabetic characters, otherwise false.
     */
    private static function isAlpha(string $value): bool
    {
        return preg_match("/^[a-zA-Z]+$/", $value);
    }

    /**
     * Validates if a value contains only alphabetic characters, digits, spaces, and underscores.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value matches the pattern, otherwise false.
     */
    private static function isAlphaDash(string $value): bool
    {
        return preg_match("/^[a-zA-Z0-9 _]+$/", $value);
    }

    /**
     * Validates if a value contains only alphanumeric characters.
     *
     * @param string $value The value to validate.
     * @return bool Returns true if the value contains only alphanumeric characters, otherwise false.
     */
    private static function isAlphaNum(string $value): bool
    {
        return preg_match("/^[a-zA-Z0-9]+$/", $value);
    }

    /**
     * Validates if a value's length is greater than or equal to a minimum length.
     *
     * @param string $valid The validation rule which contains the minimum length as `min:length`.
     * @param string $value The value to validate.
     * @return bool Returns true if the value's length is greater than or equal to the minimum, otherwise false.
     */
    private static function validateMin(string $valid, string $value): bool
    {
        $min = (int) str_replace('min:', '', $valid);
        return strlen($value) >= $min;
    }

    /**
     * Validates if a value's length is less than or equal to a maximum length.
     *
     * @param string $valid The validation rule which contains the maximum length as `max:length`.
     * @param string $value The value to validate.
     * @return bool Returns true if the value's length is less than or equal to the maximum, otherwise false.
     */
    private static function validateMax(string $valid, string $value): bool
    {
        $max = (int) str_replace('max:', '', $valid);
        return strlen($value) <= $max;
    }

     /**
     * Validates if a value's length is within the given range.
     *
     * @param string $valid The validation rule which contains the range as `between:min,max`.
     * @param string $value The value to validate.
     * @return bool Returns true if the value's length is within the specified range, otherwise false.
     */
    private static function validateBetween(string $valid, string $value): bool
    {
        $range = explode(',', str_replace('between:', '', $valid));
        return strlen($value) >= (int)$range[0] && strlen($value) <= (int)$range[1];
    }

     /**
     * Validates if a value is present in the given list of values.
     *
     * @param string $valid The validation rule which contains the list of values as `in:value1,value2,...`.
     * @param string $value The value to validate.
     * @return bool Returns true if the value is in the specified list, otherwise false.
     */
    private static function validateIn(string $valid, string $value): bool
    {
        $values = explode(',', str_replace('in:', '', $valid));
        return in_array($value, $values);
    }

    /**
     * Validates if a value is not present in the given list of values.
     *
     * @param string $valid The validation rule which contains the list of values as `not_in:value1,value2,...`.
     * @param string $value The value to validate.
     * @return bool Returns true if the value is not in the specified list, otherwise false.
     */
    private static function validateNotIn(string $valid, string $value): bool
    {
        $values = explode(',', str_replace('not_in:', '', $valid));
        return !in_array($value, $values);
    }

    /**
     * Validates if a value matches the given regular expression.
     *
     * @param string $valid The validation rule which contains the regex pattern as `regex:pattern`.
     * @param string $value The value to validate.
     * @return bool Returns true if the value matches the regex pattern, otherwise false.
     */
    private static function validateRegex(string $valid, string $value): bool
    {
        $pattern = str_replace('regex:', '', $valid);
        return preg_match($pattern, $value);
    }
    
    /**
     * Delegates the validation of a single value against a given validation rule.
     *
     * This method determines whether to call a specific validation method
     * from `$validationRules` or handle a parameter-based validation rule (e.g., min, max, regex).
     *
     * @param string $value The value to validate.
     * @param string $valid The validation rule to apply.
     * @return bool Returns true if the value passes the validation rule, otherwise false.
     */
    private static function validator(string $value, string $valid): bool {
        if (isset(self::$validationRules[$valid])) {
            return self::{self::$validationRules[$valid]}($value);
        }

        if (preg_match('/^(min|max|between|in|not_in|regex):/', $valid, $matches)) {
            $method = 'validate' . ucfirst($matches[1]);
            return self::$method($valid, $value);
        }

        return false;
    }
    
    /**
     * Checks the validation for a specific key in the input data against a list of validation rules.
     *
     * This method iterates through validation rules associated with a key, applies the validation,
     * and records errors if validation fails.
     *
     * @param array $input The input data array.
     * @param string $key The key to validate in input data.
     * @param array $validateList An array of validation rules to apply.
     */
    private static function checkValidate(array $input, string $key, array $validateList): void
    {
        if (isset($input[$key])) {
            $value = $input[$key];
            foreach ($validateList as $valid) {
                if (!self::validator($value, $valid)) {
                    self::$errors[$key][] = "Invalid value for $key with rule $valid.";
                    self::$fails = false;
                }
            }
        } else {
            self::$errors[$key][] = "$key is required.";
        }
    }

    /**
     * Performs the validation against a set of rules for a set of input data.
     *
     * This is the main method to initiate validation on the input. It sets `$errors` to empty, `$fails` to true,
     * then loops through every key in the `$validates` array, runs validation, and reports any errors
     *
     * @param array $input The input data array to validate.
     * @param array $validates An array of rules where the key is the input data key and the value is
     *  the string of the validation rules separated by '|'.
     */
    public static function make(array $input, array $validates): void
    {
      self::$errors = [];
      self::$fails = true;
        foreach ($validates as $key => $validete) {
            $valideteList = explode("|", $validete);
            self::checkValidate($input, $key, $valideteList);
        }
    }
}
?>
