<?php
namespace Woodpecker;

class Router {
    private $routes = [];          // Stores the list of defined routes
    private $middleware = [];      // Stores the defined middleware

    /**
     * Adds a new route to the router.
     *
     * This method stores route information such as the HTTP method, the 
     * URL parameters, the action to perform (callable or string), and 
     * an optional middleware to run.
     *
     * @param string $method The HTTP method for the route (GET, POST, etc.)
     * @param array $url The URL parameters expected for this route.
     * @param string|callable $action The action to execute for this route.
     * @param string $middleware Optional middleware to be executed.
     * @throws WPException If one of the first three parameters is empty.
     */
    private function addRoute(string $method, array $url, string|callable $action, string $middleware = ''): void {
        if (empty($method) || empty($url) || empty($action)) {
            throw new WPException(" router =>addRoute function: One of the first three entries is empty.");
        }
        $this->routes[] = ['method' => strtoupper($method), 'url' => $url, 'action' => $action, 'middleware' => $middleware];
    }

    /**
     * Parses the current URL and returns the query parameters as an array.
     *
     * This method extracts and sanitizes query parameters from the
     * current request's URI.
     *
     * @return array An associative array of query parameters.
     */
    private function getUrlParse(): array {
        $urlParse = parse_url($_SERVER['REQUEST_URI']);
        if (isset($urlParse['query'])) {
            parse_str($urlParse['query'], $params);
            foreach ($params as $key => $val) {
                if (is_numeric($val)) {
                    $params[$key] = (int)$val; // Cast numeric values to integers
                }
            }
            return $params;
        }
        return [];
    }

   /**
     * Gets the result based on the HTTP method.
     *
     * @return array An array of sanitized input from the request.
     */
    private function getResultMethod(string $method): array
    {
        $input = [];

        switch ($method) {
            case 'GET':
                $input = $_GET;
                break;
            case 'POST':
                if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                   $postData = file_get_contents('php://input');
                   if ($postData !== false) {
                       $input = json_decode($postData, true);
                   } else {
                       throw new WPException(" router =>Failed to read POST JSON input.");
                   }
                } else {
                    $input = $_POST;
                }
                break;
            case 'PUT':
            case 'DELETE':
            case 'HEAD':
            case 'OPTIONS':
                $putData = file_get_contents('php://input');
                 if($putData !== false){
                   $input = json_decode($putData, true);
                 } else {
                   throw new WPException(" router => Failed to read input for $method method.");
                 }
                break;
                
            default:
                break;
        }
        
         foreach ($input as $key => $value) {
            $input[$key] = $this->sanitizeInput($value);
        }

        return $input;
    }


    /**
     * Sanitizes the input data to prevent XSS attacks.
     *
     * This method converts special characters to HTML entities.
     *
     * @param mixed $input The input data to sanitize (can be an array or string).
     * @return mixed The sanitized input data.
     */
    private function sanitizeInput($input): mixed {
        if (is_array($input)) {
            foreach ($input as &$value) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        } else {
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
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
    private function chackParam(array $request, array $defined): bool {
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
     * @return array|null The matched route or null if not found.
     */
    private function findRoute(string $method): array {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                return $route;
            }
        }
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
     * @throws WPException If the class or method does not exist.
     */
    private function invokeCF($class, $function, $params) {
        $originalFunction = $function;
        $request = false;
      if(strpos($function, 'request:') !== false)
      {
            $function = str_replace('request:', '', $function);
            $request = true;
      }
        if (class_exists(trim($class)) && method_exists(trim($class), trim($function))) {
            $newClass = new $class();
            if($request){
                $params = new Support\Request($params);
                call_user_func_array([$newClass, $function], [$params]);
            } else{
               call_user_func_array([$newClass, $function], array_values($params));
            }

        } else {
            throw new WPException(" router =>invoke function: not found class: $class or function: $originalFunction.");
        }
    }


    /**
     * Executes the router engine to find and execute a route.
     *
     * This method checks the request method against defined routes,
     * processes middleware, and executes the corresponding action.
     *
     * @return void
     */
    public function run(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $route = $this->findRoute($method);
        if ($route) {
            $result = $this->getResultMethod($method);

            if (!$this->chackParam($route['url'], $result)) {
                $this->runMiddleware($route['middleware']);
                $values = [];
                foreach ($route['url'] as $key) {
                  if (isset($result[$key])) {
                    $values[$key] = $result[$key];
                  }
                }


                if (is_callable($route['action'])) 
                {
                    call_user_func_array($route['action'], array_values($values));
                } 
                elseif (is_string($route['action']) && strpos($route['action'], '@')) 
                {
                    list($class, $function) = explode('@', $route['action']);
                    $this->invokeCF($class, $function, $values);
                } 
                elseif(is_array($route['action'])){
                  $this->invokeCF($route['action'][0], $route['action'][1], $values);
                }
                else 
                {
                    throw new WPException(" router =>run function: The action value or the second input of the function is not accepted.");
                }
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
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     */
    public function get(array $url, string|array|callable $callback, string $middleware = ''): void {
        $this->addRoute('GET', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the POST method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP POST requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     */
    public function post(array $url, string|array|callable $callback, string $middleware = ''): void {
        $this->addRoute('POST', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the PUT method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP PUT requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     */
    public function put(array $url, string|callable $callback, string $middleware = ''): void {
        $this->addRoute('PUT', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the DELETE method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP DELETE requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     */
    public function delete(array $url, string|callable $callback, string $middleware = ''): void {
        $this->addRoute('DELETE', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the HEAD method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP HEAD requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     */
    public function head(array $url, string|callable $callback, string $middleware = ''): void {
        $this->addRoute('HEAD', $url, $callback, $middleware);
    }

    /**
     * Registers a route for the OPTIONS method.
     *
     * This method is a shortcut for defining routes that respond to
     * HTTP OPTIONS requests.
     *
     * @param array $url The URL parameters required for the route.
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     */
    public function options(array $url, string|callable $callback, string $middleware = ''): void {
        $this->addRoute('OPTIONS', $url, $callback, $middleware);
    }

    /**
     * Registers a route for multiple HTTP methods.
     *
     * This method allows defining a single route that can respond
     * to multiple HTTP methods (e.g., GET, POST).
     *
     * @param string $methods A string containing HTTP methods separated by '|'.
     * @param array $uri The URL parameters required for the route.
     * @param string|callable $callback The action to execute for this route.
     * @param string $middleware Optional middleware to execute before the route.
     * @return void
     * @throws WPException If an invalid method is provided.
     */
    public function multiMethod(string $methods, array $uri, string|callable $callback, string $middleware = ''): void {
        foreach (explode('|', $methods) as $method) {
            if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'])) {
                $this->addRoute($method, $uri, $callback, $middleware);
            } else {
                throw new WPException(" router =>multiMethod function: This method '$method' is not acceptable.");
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
    public function middleware(string $name, callable $callback): void {
        if (!isset($this->middleware[$name])) {
            $this->middleware[$name] = $callback;
        } else {
            throw new WPException(" router =>This middleware already exists.");
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
    private function runMiddleware($middleware): void {
        if (!empty($middleware)) {
            if (isset($this->middleware[$middleware]) && is_callable($this->middleware[$middleware])) {
                $this->middleware[$middleware](); // Execute the middleware
            } else {
                throw new WPException(" router =>middleware: not found.");
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
    public function url(string $url, string|callable $action, string $middleware = ''): void {
        $preg = preg_replace('/{(\w+)}/', '([^/]+)', $url);
        $preg = '#^' . $preg . '$#';

        $rurl = strpos($_SERVER['REQUEST_URI'], '?') ? (substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))) : $_SERVER['REQUEST_URI'];

        if (preg_match($preg, $rurl, $matches)) {
            array_shift($matches); // Remove the first match, which is the entire URL

            $this->runMiddleware($middleware);

            if (is_callable($action)) {
                call_user_func_array($action, $matches);
            } elseif (is_string($action) && strpos($action, '@')) {
                list($class, $function) = explode('@', $action);
                $this->invokeCF($class, $function, $matches);
            } else {
                throw new WPException(" router =>url function: The action value or the second input of the function is not accepted.");
            }
        }
    }
}
?>
