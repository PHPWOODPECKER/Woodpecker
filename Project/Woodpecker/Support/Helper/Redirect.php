  <?php
  namespace Woodpecker\Support;
  
  use Woodpecker\Facade;
  
  class Redirect extends Facade {
  
    /**
     * Redirects the user to a specified path.
     *
     * @param string $path The path to redirect to. If it's a full URL, the user will be redirected there directly.
     *                     If it's a relative path (e.g., /user/), it will be appended to the site's base URL.
     * @param int $status The HTTP status code for the redirect (default: 302).
     * @param bool $exit Whether to terminate script execution after the redirect (default: false).
     *
     * @throws WPException If an invalid HTTP status code is provided.
     */
  
    public static function to(string $path, int $status = 302, bool $exit = false): void
    {
        if (!in_array($status, [200, 301, 302, 303, 307, 308], true)) {
            throw new WPException(" redirect => redirect => Invalid HTTP status code: $status");
        }
    
        if (filter_var($path, FILTER_VALIDATE_URL) === FALSE) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $baseURL = $protocol . '://' . $host;
    
            $path = $baseURL . $path;
        }
    
        http_response_code($status);
    
        header("Location: $path");
    
        if ($exit) {
            exit();
        }
    }
  
    
    /**
     * Calls a method on a specified class, passing the provided parameters.
     *
     * @param array|string $action The class and method to call or an action string in the format 'Class@method'.
     * @param mixed $parameter The parameters to pass to the method.
     * 
     * @throws WPException if the class or method does not exist.
     */
    public static function action(array|string $action, array $parameter = []): void
    {
      if (is_array($action)) {
        if (class_exists($action[0]) && method_exists($action[0], $action[1])) {
          call_user_func_array([new $action[0](), $action[1]], $parameter);
        } else {
          throw new WPException(" redirect => redirect =>Class or method does not exist: " . implode('::', $action));
        }
      } elseif (is_string($action) && strpos($action, '@')) {
        list($class, $function) = explode('@', $action);
        if (class_exists($class) && method_exists($class, $function)) {
          call_user_func_array([new $class(), $function], $parameter);
        } else {
          throw new WPException(" redirect =>Class or method does not exist: $class::$function");
        }
      } else {
        throw new WPException(" redirect =>Invalid action format. Expected array or string with @");
      }
    }
    
    /**
     * Store a key-value pair in the session for later retrieval.
     *
     * @param string $key The key under which the value is stored in the session.
     * @param string $value The value to be stored.
     * 
     * @throws WPException if session cannot be started.
     */
    public static function with(string $key, string $value): void 
    {
      if (session_status() == PHP_SESSION_NONE) {
        if (!session_start()) {
          throw new WPException(" redirect =>Unable to start session.");
        }
      }
      
      $_SESSION[$key] = $value;
    }
  }
