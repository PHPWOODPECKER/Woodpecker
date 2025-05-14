<?php
declare(strict_types=1);

namespace Woodpecker;

use ReflectionMethod;
use Woodpecker\Facade;
use Woodpecker\Support\Request;
use Woodpecker\Support\RateLimiter;

class Router extends Facade {
    protected static $routes = [];     // Stores all registered routes
    protected static $middleware = []; // Stores all registered middleware

    /**
     * Adds a new route to the router.
     *
     * This method stores route information such as the HTTP method, the 
     * URL parameters, the action to perform (callable or string), and 
     * an optional middleware to run.
     *
     * @param string $method The HTTP method for the route (GET, POST, etc.)
     * @param array $url The URL parameters expected for this route.
     * @param array|callable $action The action to execute for this route.
     * @param string $middleware Optional middleware to be executed.
     * @throws WPException If one of the first three parameters is empty.
     */
    protected static function addRoute(string $method, array $url, array|callable $action, string $middleware = ''): void 
    {
        if (empty($method) || empty($url) || empty($action)) {
            throw new WPException("router => addRoute function: One of the first three entries is empty.");
        }
        self::$routes[] = [
            'method' => strtoupper($method), 
            'url' => $url,
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Parses the current URL and returns the query parameters as an array.
     *
     * This method extracts and sanitizes query parameters from the
     * current request's URI.
     *
     * @return array An associative array of query parameters.
     */
    protected static function getUrlParse(): array 
    {
        $urlParse = parse_url($_SERVER['REQUEST_URI']);
        if (array_key_exists('query', $urlParse)) {
            parse_str($urlParse['query'], $params);
            $params = array_map(fn($val) => is_numeric($val) ? (int)$val : $val, $params);
            return $params;
        }
        return self::getInputData('GET');
    }

    /**
     * Gets the result based on the HTTP method.
     *
     * This method retrieves and sanitizes input data based on the HTTP method.
     *
     * @param string $method The HTTP method (GET, POST, etc.).
     * @return array An array of sanitized input from the request.
     */
    protected static function getResultMethod(string $method): array 
    {

        $input = match ($method) {
            'GET' => self::getUrlParse(),
            'POST' => self::getPostData(),
            'PUT', 'DELETE', 'HEAD', 'OPTIONS' => self::getInputData($method),
            default => []
        };
        return $input;
    }

    /**
     * Retrieves POST data from the request.
     *
     * This method handles JSON and form-data POST requests.
     *
     * @return array The POST data as an associative array.
     * @throws WPException If reading JSON input fails.
     */
    protected static function getPostData(): array 
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $postData = file_get_contents('php://input');
            if ($postData === false) {
                throw new WPException("router => Failed to read POST JSON input.");
            }
            return json_decode($postData, true) ?? [];
        }
        return $_POST;
    }

    /**
     * Retrieves input data for PUT, DELETE, HEAD, and OPTIONS requests.
     *
     * This method reads raw input data and decodes it from JSON.
     *
     * @param string $method The HTTP method (PUT, DELETE, etc.).
     * @return array The input data as an associative array.
     * @throws WPException If reading input data fails.
     */
    protected static function getInputData(string $method): array 
    {
        $inputData = file_get_contents('php://input');
        if ($inputData === false) {
            throw new WPException("router => Failed to read input for $method method.");
        }
        
        if($method !== $_SERVER['REQUEST_METHOD']){
          throw new WPException("router => The Request method does not match. ");
        }
        
        return json_decode($inputData, true) ?? [];
    }

    /**
     * Sanitizes input data to prevent XSS attacks.
     *
     * This method applies `htmlspecialchars` to all input values.
     *
     * @param mixed $input The input data to sanitize.
     * @return mixed The sanitized input data.
     */
    protected static function sanitizeInput($input): mixed 
    {
        if (is_array($input)) {
            return array_map(fn($value) => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $input);
        }
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Checks if the request parameters match the defined parameters.
     *
     * This method compares the keys of the request against the defined parameters
     * to ensure all required parameters are present.
     *
     * @param array $request The request parameters.
     * @param array $defined The defined parameters.
     * @return bool True if all defined parameters are present, false otherwise.
     */
    protected static function checkParam(array $request, array $defined): bool 
    {
        $requestKeys = array_keys($request);
        return empty(array_diff($requestKeys, $defined)) && empty(array_diff($defined, $requestKeys));
    }

    /**
     * Finds a route that matches the given HTTP method.
     *
     * This method searches through the defined routes to find one
     * that matches the specified HTTP method.
     *
     * @param string $method The HTTP method to match.
     * @return array|null The matched route or null and show 404 error if not found.
     */
    protected static function findRoute(string $method): ?array 
    {
        foreach (self::$routes as $route) {
            if ($route['method'] === $method) {
                return $route;
            }
        }
        http_response_code(404);
        include_once(__DIR__. "/../../Web/View/Errors/404Error.php");
        return null;
    }

    /**
     * Invokes a method from a specified class with the given parameters.
     *
     * This method creates an instance of the specified class and calls
     * the specified method with the given parameters.
     *
     * @param string $class The name of the class.
     * @param string $function The method to invoke.
     * @param array $params The parameters to pass to the method.
     * @param Support\Request $request The request object.
     * @return mixed.
     * @throws WPException If the class or method does not exist.
     */
    protected static function invokeCF($class, $function, $params, $request): mixed
    {
        if (!class_exists(trim($class)) || !method_exists(trim($class), trim($function))) {
            throw new WPException("router => invoke function: not found class: $class or function: $function.");
        }

        $newClass = new $class();
        $reflection = new ReflectionMethod($class, $function);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if ($paramType && $paramType == Support\Request::class) {
                $args[] = $request;
            } else {
                if (array_key_exists($paramName, $params)) {
                    $args[] = $params[$paramName];
                } else {
                    if ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        throw new WPException("router => invoke function: Missing parameter: $paramName > $paramType for function $function");
                    }
                }
            }
        }

        call_user_func_array([$newClass, $function], $args);
    }

    /**
     * Executes the router engine to find and execute a route.
     *
     * This method checks the request method against defined routes,
     * processes middleware, and executes the corresponding action.
     *
     * @return void
     */
    public static function run(): void 
    {
      if(empty(self::$routes)){
        return;
      }
      
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $route = self::findRoute($method);
        if ($route) {
            $result = self::getResultMethod($method);
          
            if (self::checkParam($result, $route['url'])) {

                self::runMiddleware($route['middleware']);

                $values = array_intersect_key($result, array_flip($route['url']));

                $request = new Request($values);

                if (is_callable($route['action'])) {
                    call_user_func_array($route['action'], array_values($values));
                } elseif (is_array($route['action'])) {
                  $params = array_merge($route['action']['params'] ?? [], $values);
                  
                    self::invokeCF($route['action']['class'], $route['action']['method'], $params, $request);
                } else {
                    throw new WPException("router => run function: The action value or the second input of the function is not accepted.");
                }
            }else{
              throw new WPException("router => run: Invalid route parameters");
            }
        }

        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'HEAD') {
            ob_end_clean();
        }
    }

    /**
     * Registers a route for the GET method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP GET requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     */
    public static function get(array $url, array|callable $callback, string $middleware = ''): void 
    {
        self::addRoute('GET', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the POST method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP POST requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     */
    public static function post(array $url, array|callable $callback, string $middleware = ''): void 
    {
        self::addRoute('POST', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the PUT method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP PUT requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     */
    public static function put(array $url, array|callable $callback, string $middleware = ''): void 
    {
        self::addRoute('PUT', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the DELETE method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP DELETE requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     */
    public static function delete(array $url, array|callable $callback, string $middleware = ''): void 
    {
        self::addRoute('DELETE', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the HEAD method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP HEAD requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     */
    public static function head(array $url, array|callable $callback, string $middleware = ''): void 
    {
        self::addRoute('HEAD', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the OPTIONS method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP OPTIONS requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     */
    public static function options(array $url, array|callable $callback, string $middleware = ''): void 
    {
        self::addRoute('OPTIONS', $url, $callback, $middleware);
    }

    /**
     * Registers a route for multiple HTTP methods.
     *
     * This method allows defining a single route that can respond
     * to multiple HTTP methods (e.g., GET, POST).
     *
     * @param string $methods A string containing HTTP methods separated by '|'.
     * @param array $url The URL parameters required for the route.
     * @param array|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return self
     * @throws WPException If an invalid method is provided.
     */
    public static function multiMethod(string $methods, array $url, array|callable $callback, string $middleware = ''): void
    {
        foreach (explode('|', $methods) as $method) {
            if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'])) {
                self::addRoute($method, $url, $callback, $middleware);
            } else {
                throw new WPException("router => multiMethod function: This method '$method' is not acceptable.");
            }
        }
    }

    /**
     * Defines a middleware function.
     *
     * This method allows the registration of middleware that can be 
     * executed before route actions.
     *
     * @param string $name The name of the middleware.
     * @param callable $callback The callable to be executed as middleware.
     * @return void
     * @throws WPException If the middleware name already exists.
     */
    public static function middleware(string $name, callable $callback): void
    {
        if (!array_key_exists($name, self::$middleware)) {
            self::$middleware[$name] = $callback;
        } else {
            throw new WPException("router => This middleware already exists.");
        }
    }

    /**
     * Executes the specified middleware.
     *
     * This method runs the registered middleware function if it exists.
     *
     * @param string $middleware The name of the middleware to run.
     * @return void
     * @throws WPException If the middleware is not found.
     */
    protected static function runMiddleware($middleware): void
    {
        if (!empty($middleware)) {
            if (array_key_exists($middleware, self::$middleware) && is_callable(self::$middleware[$middleware])) {
                self::$middleware[$middleware]();
            } else {
                throw new WPException("router => middleware: not found.");
            }
        }
    }

    /**
     * Registers a URL route with an action.
     *
     * This method processes the URL and action provided and matches it
     * against the current request URI to execute the specified action.
     *
     * @param string $url The URL pattern expected.
     * @param string|callable $action The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     * @throws WPException If the action value is not accepted.
     */
    public static function url(string|array $url, array|callable $action, string $middleware = ''): void
    {
        $rate = is_array($url) && array_key_exists('rate', $url) ? $url['rate'] : '';
        $url = is_array($url) ? $url['url'] : $url;
        
      
        preg_match_all('/{(\w+)(?::([^}]+))?}/', $url, $matches, PREG_SET_ORDER);
        $pattern = $url;
        $paramNames = [];
    
        foreach ($matches as $match) {
            $paramName = $match[1];
            $paramPattern = $match[2] ?? '[^/]+';
            $pattern = str_replace($match[0], "($paramPattern)", $pattern);
            $paramNames[] = $paramName;
        }
    
        $pattern = '#^' . $pattern . '$#';
    
        $rurl = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    
        if (preg_match($pattern, $rurl, $matches)) {
            array_shift($matches);
    
            $params = [];
            foreach ($paramNames as $index => $name) {
                $params[$name] = $matches[$index];
            }
            
            $request = new Support\Request($params);
            
            if (!empty($rate)) {
              $limite = RateLimiter::by($request->ip())->perMinute((int) $rate);
                if (!$limite->attempt()) {
                    http_response_code(429);
                    include_once(__DIR__ . "/../../Web/View/Errors/429Error.php");
                    return;
                }
            }
    
            self::runMiddleware($middleware);
    
            if (is_callable($action)) {
                call_user_func_array($action, $params);
            } elseif (is_array($action)) {
                $class = $action['class'];
                $function = $action['method'];
                self::invokeCF($class, $function, $params, $request);
            } else {
                throw new WPException("router => url function: The action value or the second input of the function is not accepted.");
            }
        }
    }
}
