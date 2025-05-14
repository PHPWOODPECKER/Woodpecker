<?php
namespace Woodpecker\Support;

use Woodpecker\Validator;

/**
 * Class Request
 *
 * Represents an HTTP request and provides methods for accessing and manipulating request data.
 */
class Request {
  
  protected array $input;
  
    /**
     * @param array $input
     *
     * An array containing the request input data (e.g., $_POST, $_GET).
     * This array is injected into the Request instance during construction.
     */
    public function __construct(array $input = []) {
      $this->input = $this->sanitize($input);
    }

    /**
     * Validate the input against the given rules.
     *
     * @param array $validate An associative array of validation rules.
     * @return self Returns the Request object for method chaining.
     * @throws WPException If validation fails, a WPException is thrown with the validation errors.
     */
    public function validate(array $validate): self
    {
        Validator::make($this->input, $validate);

        if(Validator::fails()){
          throw new WPException(Validator::errors());
        }
        
        return $this;
    }

    /**
     * Retrieve an input value by key.
     *
     * @param string $key The key of the input value to retrieve.
     * @return string|null The input value or null if not found.
     */
    public function input(string $key): ?string
    {
        return trim($this->input[$key] ?? null);
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
                $result[$key] = $this->input[$key];
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
            unset($this->input[$key]);
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
        return !empty($this->input[$key]);
    }

    /**
     * Get the path component of the request URI.
     *
     * @return string The path part of the URL.
     */
    public function path(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get the host and path component of the request URI.
     *
     * @return string The host and path part of the URL.
     */
    public function host(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_HOST) . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get the full request URI.
     *
     * @return string The complete URL requested by the user.
     */
    public function fullUrl(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the IP address of the client making the request.
     *
     * @return string The IP address.
     */
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get the HTTP request method (e.g., GET, POST).
     *
     * @return string The request method.
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if the request method matches a specified method.
     *
     * @param string $method The HTTP method to compare against (e.g., 'GET', 'POST').
     * @return bool True if the request method matches, false otherwise.
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Checks if any of the provided keys exist in the input.
     *
     * @param array $keys An array of keys to check for existence.
     * @return bool True if at least one key exists, false otherwise.
     */
    public function hasAny(array $keys): bool
    {
      foreach ($keys as $key){
        if($this->has($key)){
          return true;
        }
      }
      
      return false;
    }
    
    /**
     * Executes a callable if a specific key exists in the input.
     *
     * @param string $key The key to check for existence.
     * @param callable $callable The callable to execute if the key exists.  The input value associated with the key is passed as an argument to the callable.
     * @return void
     */
    public function whenHas(string $key, callable $callable): void
    {
      if($this->has($key)){
        $callable($this->input($key));
      }
    }
    
    /**
     * Merges an array of values into the input array, only if the key doesn't already exist.
     *
     * @param array $values An associative array of key-value pairs to merge into the input.
     * @return void
     */
    public function merge(array $values): void
    {
      foreach ($values as $key => $value){
        if($this->missing($key)){
            $this->input[$key] = $value; 
        }
      }
      
    }
    
    /**
     * Checks if a specific key is missing from the input array.
     *
     * @param string $key The key to check for absence.
     * @return bool True if the key is missing, false otherwise.
     */
    public function missing(string $key): bool
    {
      return !array_key_exists($key, $this->input);
    }
    
    /**
     * sanitize data
     */
    protected function sanitize(array $data): array {
      return array_map(fn($value) => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $data);
     }
    
}
