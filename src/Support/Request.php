<?php
namespace Woodpecker\Helper;

use Woodpecker\Validator;

class Request {
    // Class constructor to inject the input array into the instance
    public function __construct(private array $input) {}

    /**
     * Validate the input against the given rules.
     *
     * @param array $validate An associative array of validation rules.
     * @return bool True if validation fails, false otherwise.
     */
    public function validate(array $validate): bool
    {
        // Use the Validator to perform validation
        Validator::make($this->input, $validate);
        
        // Return whether the validation has failed
        return Validator::fails() ? true : Validator::errors();
    }

    /**
     * Retrieve an input value by key.
     *
     * @param string $key The key of the input value to retrieve.
     * @return string|null The input value or null if not found.
     */
    public function input(string $key): string
    {
        // Return the value if exists; otherwise null
        return trim($this->input[$key]);
    }  

    /**
     * Get all input values as an associative array.
     *
     * @return array The complete input array.
     */
    public function all(): array
    {
        return $this->input;
    }

    /**
     * Retrieve a subset of input values by keys.
     *
     * @param array $keys An array of keys to retrieve from the input.
     * @return array The subset of input values.
     */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if (isset($this->input[$key])) {
                $result[$key] = $this->input[$key]; // Store existing keys
            }
        }
        return $result;
    }

    /**
     * Exclude specified keys from the input array.
     *
     * @param array $keys An array of keys to exclude from the input.
     * @return array The filtered input array without the specified keys.
     */
    public function except(array $keys): array
    {
        foreach ($keys as $key) {
            unset($this->input[$key]); // Remove specified keys
        }
        return $this->input;
    }

    /**
     * Check if a specific key exists in the input.
     *
     * @param string $key The key to check for existence.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->input);
    }

    /**
     * Check if a specific key is filled (not empty).
     *
     * @param string $key The key to check.
     * @return bool True if the key exists and is not empty, false otherwise.
     */
    public function filled(string $key): bool
    {
        return !empty($this->input[$key]); // Check if it exists and is not empty
    }
}
