# <img src="https://raw.githubusercontent.com/sallarizadi/GapGPT/main/assets/router-logo.png" width="100"> Router Class

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://example.com/build)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![GitHub Stars](https://img.shields.io/github/stars/yourusername/router.svg?style=social&label=Star&maxAge=3600)](https://github.com/yourusername/router)

## Overview

The `Router` class is a powerful PHP utility designed to manage HTTP routing in web applications. It allows developers to define routes for various HTTP methods (GET, POST, PUT, DELETE, etc.) and handle incoming requests efficiently. The class supports middleware, input sanitization, and dynamic route handling, making it an essential component for building RESTful APIs and web applications.

## ✨ Key Features

-   **Dynamic Routing**: Easily define routes for different HTTP methods and map them to specific actions or controllers.
-   **Middleware Support**: Register middleware functions that can be executed before route actions, allowing for pre-processing of requests.
-   **Input Sanitization**: Automatically sanitize input data to prevent XSS attacks and ensure data integrity.
-   **Flexible Parameter Handling**: Supports dynamic URL parameters, allowing for clean and readable route definitions.
-   **Error Handling**: Throws exceptions for invalid routes or actions, ensuring robust error management.

## 🧰 Class Properties

-   `$routes`: An array that stores the list of defined routes, including HTTP methods, URL parameters, actions, and middleware.
-   `$middleware`: An array that stores the defined middleware functions.

## ⚙️ Methods

### `public function run(): void`

Executes the router engine to find and execute a route based on the current request method. It processes middleware and invokes the corresponding action.

### `public function get(array $url, string|array|callable $callback, string $middleware = ''): void`

Registers a route for the GET method.

### `public function post(array $url, string|array|callable $callback, string $middleware = ''): void`

Registers a route for the POST method.

### `public function put(array $url, string|array|callable $callback, string $middleware = ''): void`

Registers a route for the PUT method.

### `public function delete(array $url, string|array|callable $callback, string $middleware = ''): void`

Registers a route for the DELETE method.

### `public function head(array $url, string|array|callable $callback, string $middleware = ''): void`

Registers a route for the HEAD method.

### `public function options(array $url, string|array|callable $callback, string $middleware = ''): void`

Registers a route for the OPTIONS method.

### `public function multiMethod(string $methods, array $uri, string|callable $callback, string $middleware = ''): void`

Registers a route for multiple HTTP methods.

### `public function url(string $url, string|callable $action, string $middleware = ''): void`

Registers a URL route with an action. This method processes the URL and action provided and matches it against the current request URI to execute the specified action.

-   **Parameters**:
    -   `string $url`: The URL pattern expected.
    -   `string|callable $action`: The action to execute for this route.
    -   `string $middleware`: Optional middleware to execute before the route.

### `public function middleware(string $name, callable $callback): void`

Defines a middleware function that can be executed before route actions.

### `private function sanitizeInput($input): mixed`

Sanitizes the input data to prevent XSS attacks by converting special characters to HTML entities.

### `private function findRoute(string $method): array`

Finds a route that matches the given HTTP method.

### `private function invokeCF($class, $function, $params)`

Invokes a method from a specified class with the given parameters.

### `private function runMiddleware($middleware): void`

Executes the specified middleware.

## 🚀 Usage Example

```php
use Woodpecker\Router;

$router = new Router();

// Define a GET route
$router->get(['user', '{id}'], function($id) {
    echo "User ID: " . $id;
});

// Define a POST route
$router->post(['user'], function() {
    echo "User created!";
});

// Define a URL route
$router->url("/path/{param}", function($param) {
    echo "$param executed!";
});

// Run the router
$router->run();
