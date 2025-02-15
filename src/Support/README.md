
# Woodpecker Helper Classes

This repository contains helper classes designed to simplify common tasks in PHP web development, such as request handling, response generation, and redirection.

## Table of Contents

- [Introduction](#introduction)
- [Classes](#classes)
    - [Redirect](#redirect)
    - [Request](#request)
    - [Response](#response)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Introduction

The Woodpecker Helper Classes provide a set of utilities to streamline web development tasks. These classes are designed to be modular and easy to integrate into existing projects.

## Classes

### Redirect

The `Redirect` class handles HTTP redirection and session management.

#### Methods

-   **`to(string $path, int $status = 302, bool $exit = false): void`**

    Redirects to the specified path with an optional HTTP status code.

    -   `$path`: The URL path to redirect to.
    -   `$status`: The HTTP status code to use for the redirect (default: 302).
    -   `$exit`: Whether to terminate script execution after redirecting (default: false).

    ```php
    Redirect::to('/new/path', 301);
    ```

-   **`action(array|string $action, array $parameter = []): void`**

    Calls a method on a specified class.

    -   `$action`: The class and method to call (e.g., `['ClassName', 'methodName']` or `'ClassName@methodName'`).
    -   `$parameter`: The parameters to pass to the method.

    ```php
    Redirect::action(['MyController', 'handleAction'], ['param1' => 'value1']);
    Redirect::action('MyController@handleAction', ['param1' => 'value1']);
    ```

-   **`with(string $key, string $value): void`**

    Stores a key-value pair in the session for later retrieval.

    -   `$key`: The key under which the value is stored in the session.
    -   `$value`: The value to be stored.

    ```php
    Redirect::with('message', 'Operation successful!');
    ```

-   **`withInput(): void`**

    Stores all input data in the session (implementation details may vary).

    ```php
    Redirect::withInput();
    ```

### Request

The `Request` class handles HTTP requests, input validation, and data retrieval.

#### Methods

-   **`__construct(array $input)`**

    Class constructor to inject the input array into the instance.

    -   `$input`: The array of input data (e.g., `$_POST`, `$_GET`).

    ```php
    $request = new Request($_POST);
    ```

-   **`validate(array $validate): bool|array`**

    Validates the input against the given rules.

    -   `$validate`: An associative array of validation rules.
    -   Returns: `true` if validation fails, `false` otherwise.

    ```php
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
    ];
    $errors = $request->validate($rules);
    if ($errors) {
        // Handle validation errors
    }
    ```

-   **`input(string $key): string|null`**

    Retrieves an input value by key.

    -   `$key`: The key of the input value to retrieve.
    -   Returns: The input value or `null` if not found.

    ```php
    $name = $request->input('name');
    ```

-   **`all(): array`**

    Gets all input values as an associative array.

    ```php
    $allInput = $request->all();
    ```

-   **`only(array $keys): array`**

    Retrieves a subset of input values by keys.

    -   `$keys`: An array of keys to retrieve from the input.

    ```php
    $subset = $request->only(['name', 'email']);
    ```

-   **`except(array $keys): array`**

    Excludes specified keys from the input array.

    -   `$keys`: An array of keys to exclude from the input.

    ```php
    $filtered = $request->except(['password', 'password_confirmation']);
    ```

-   **`has(string $key): bool`**

    Checks if a specific key exists in the input.

    -   `$key`: The key to check for existence.

    ```php
    if ($request->has('name')) {
        // Key exists
    }
    ```

-   **`filled(string $key): bool`**

    Checks if a specific key is filled (not empty).

    -   `$key`: The key to check.

    ```php
    if ($request->filled('name')) {
        // Key exists and is not empty
    }
    ```

### Response

The `Response` class handles HTTP responses, allowing for setting status codes, headers, and body content. It also supports JSON responses and file downloads.

#### Methods

-   **`setStatusCode(int $code): self`**

    Sets the HTTP status code for the response.

    -   `$code`: The status code to set.

    ```php
    $response = new Response();
    $response->setStatusCode(200);
    ```

-   **`setHeader(string $name, string $value): self`**

    Sets a specific header for the response.

    -   `$name`: The name of the header.
    -   `$value`: The value of the header.

    ```php
    $response->setHeader('Content-Type', 'application/json');
    ```

-   **`headers(array $headers): self`**

    Sets multiple headers at once.

    -   `$headers`: An associative array of headers.

    ```php
    $response->headers([
        'Content-Type' => 'application/json',
        'Cache-Control' => 'no-cache',
    ]);
    ```

-   **`setBody(string $body): self`**

    Sets the body content for the response.

    -   `$body`: The body content.

    ```php
    $response->setBody('Hello, world!');
    ```

-   **`json(array $data, int $status = 200): self`**

    Sets the response body as JSON encoded data.

    -   `$data`: The data to encode as JSON.
    -   `$status`: Optional HTTP status code for the JSON response (default: 200).

    ```php
    $response->json(['message' => 'Success'], 200);
    ```

-   **`send(): void`**

    Sends the HTTP response to the client.

    ```php
    $response->send();
    ```

-   **`download(string $url, string $path): void`**

    Downloads a file from a given URL and saves it to a specified path.

    -   `$url`: The URL to download the file from.
    -   `$path`: The path to save the downloaded file.

    ```php
    $response->download('http://example.com/file.pdf', '/path/to/save/file.pdf');
    ```

## Usage

To use these classes, include them in your project and instantiate them as needed. Ensure that the necessary dependencies (e.g., `Validator`) are available.

```php
<?php

use Woodpecker\Helper\Request;
use Woodpecker\Helper\Response;
use Woodpecker\Helper\Redirect;

// Example usage of the Request class
$request = new Request($_POST);
$name = $request->input('name');

// Example usage of the Response class
$response = new Response();
$response->json(['message' => 'Hello, ' . $name]);
$response->send();

// Example usage of the Redirect class
Redirect::to('/success');
