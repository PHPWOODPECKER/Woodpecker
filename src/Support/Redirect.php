<?php
namespace Woodpecker\Helper;


class Redirect {
  
  /**
   * Redirects to the specified path with an optional HTTP status code.
   *
   * @param string $path The URL path to redirect to.
   * @param int $status The HTTP status code to use for the redirect. Default is 302.
   * @return self Returns an instance of the current class for method chaining.
   * 
   * @throws WPException if the $status code is not valid.
   */
  public static function to(string $path, int $status = 302, bool $exit = false): void
  {
    // Validate provided status code
    if (!in_array($status, [200, 301, 302, 303, 307, 308], true)) {
      throw new WPException(" redirect => redirect =>Invalid HTTP status code: $status");
    }

    // Set the response status code
    http_response_code($status);
    
    // Make the redirect
    header("Location: $path");
    
    if($exit){
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
      // Call the method in the class if it's an array
      if (class_exists($action[0]) && method_exists($action[0], $action[1])) {
        call_user_func_array([new $action[0](), $action[1]], $parameter);
      } else {
        throw new WPException(" redirect => redirect =>Class or method does not exist: " . implode('::', $action));
      }
    } elseif (is_string($action) && strpos($action, '@')) {
      // Explore the class and method from the string format 'Class@method'
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
    
    // Store value in session
    $_SESSION[$key] = $value;
  }
  
  /**
   * Store all input data in the session for retrieval later on.
   * 
   * This is intentionally left empty. You might want to implement functionality
   * to save input data from a form submission.
   */
  public static function withInput() {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
  }
}

?>

