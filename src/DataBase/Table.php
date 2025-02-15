<?php
namespace Woodpecker\DataBase;

// Import necessary Toolss for working with database tables from the namespace Woodpecker\DataBase\Table\Tools
use Woodpecker\DataBase\Tools;

use PDO;
use PDOStatement;
/**
 * Class Table
 *
 * Provides an interface for connecting to a MySQL database using PDO (PHP Data Objects)
 * and performs various CRUD operations. This class is designed to securely
 * handle database interactions, including selections, inserts, updates,
 * and deletions, while also implementing input validation to mitigate SQL injection vulnerabilities.
 */
class Table {

    /**
     * @var PDO|null
     * @access private
     * @static
     * Stores the PDO instance used for the database connection.
     * This is a static property, so all instances of the Model class share the same connection.
     */
    private static $pdo = null;

    /**
     * @var string
     * @access private
     * @static
     * Stores the name of the primary key for the database table being manipulated.
     * This is necessary for update and delete operations.
     */
    private static $primaryKey;

    /**
     * @var mixed
     * @access public
     * @static
     * Stores the output from database query executions.
     * This can hold various data types depending on the query result (e.g., array, integer).
     */
    public static $output;

    /**
     * Establishes a connection to the MySQL database using PDO.
     *
     * This method initializes the database connection using the provided
     * host, database name, username, and password. It sets the PDO attributes
     * to enable exception handling for errors and sets the default fetch mode.
     *
     * @param array $ConnList The database address.
     * @param string $primaryKey The primary key field name for the table.
     * @throws WPException If the database connection fails.
     * @access public
     * @static
     * @return void
     */
    public static function connection(array $ConnList, string $primaryKey): void {
    // Set the primary key
    self::$primaryKey = $primaryKey;

    try {
        // Create a new PDO instance for database connection
        self::$pdo = new PDO(
            $ConnList['driver'] . ":host=" . $ConnList['host'] . ";dbname=" . $ConnList['dbname'] . ";charset=" . $ConnList['charset'],
            $ConnList['username'],
            $ConnList['password']
        );
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception handling
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Set default fetch mode to associative array
    } catch (PDOException $e) {
        throw new WPException(" db => Connection failed: " . $e->getMessage()); // Throw custom exception for connection failures
    }
}


    /**
     * Closes the database connection.
     *
     * This method sets the PDO instance to null, effectively closing the database connection.
     *
     * @access public
     * @static
     * @return void
     */
    public static function disconnection(): void {
        self::$pdo = null; // Set PDO instance to null, closing the connection
    }

    /**
     * Prepares and executes a given SQL query with parameters.
     *
     * This method takes an SQL query and an optional array of parameters.
     * It prepares the SQL statement, binds the parameters to the query, executes
     * the statement, and returns the PDOStatement object. This is used to improve efficiency and prevent SQL injection vulnerabilities.
     *
     * @param string $query The SQL query to execute.
     * @param array $params An associative array of parameters to bind to the query.
     * @throws WPException If an error occurs during query preparation or execution.
     * @access private
     * @static
     * @return PDOStatement The prepared and executed statement.
     */
    private static function prepareAndExecute(string $query, array $params = []): PDOStatement {
        try {
            $stmt = self::$pdo->prepare($query);  // Prepare the SQL statement
            foreach ($params as $key => $value) {
                 $stmt->bindValue(":$key", $value); // Bind parameters to the statement using named placeholders
            }
            $stmt->execute(); // Execute the prepared statement
            return $stmt; // Return the PDOStatement object
        } catch (PDOException $e) {
            throw new WPException(" db => prepareAndExecute error: " . $e->getMessage()); // Throw custom exception if query fails
        }
    }

    /**
     * Performs a SELECT query on a specified table and field.
     *
     * This method retrieves a specific field from the given table using a SELECT query.
     * It also includes input validation for the table and field names using the `validateInput` function.
     *
     * @param string $table The database table to query.
     * @param string $field The specific field to select from the table.
     * @throws WPException If an error occurs during the SELECT operation.
     * @access public
     * @static
     * @return Tools The result of the SELECT query wrapped in a Tools object.
     */
    public static function select(string $table, string $field): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $field = self::validateInput(['field' => $field])['field']; // Sanitize field name
        $sql = "SELECT `$field` FROM `$table`"; // Construct the SQL query

        try {
            $stmt = self::prepareAndExecute($sql);  // Execute the query
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC)); // Return results in associative array format wrapped in a Tools object
        } catch (PDOException $e) {
            throw new WPException(" db => Select function error: " . $e->getMessage()); // Throw custom exception if query fails
        }
    }

     /**
     * Finds a record in a table based on a field and its value.
     *
     * This method constructs a SELECT query to find a record that matches the specified field and value.
     * Input validation is performed on the table, field and value.
     *
     * @param string $table The database table to search in.
     * @param string $field The field to match against.
     * @param string $value The value to search for.
     * @throws WPException If an error occurs during the find operation.
     * @access public
     * @static
     * @return Tools The found record wrapped in a Tools object.
     */
    public static function find(string $table, string $field, string $value): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $field = self::validateInput(['field' => $field])['field']; // Sanitize field name
        $value = self::validateInput(['value' => $value])['value']; // Sanitize search value
        $query = "SELECT * FROM `$table` WHERE `$field` = :value";  // Construct SQL query

        try {
            $stmt = self::prepareAndExecute($query, ['value' => $value]); // Execute the query
             return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => Find function error: " . $e->getMessage()); // Throw custom exception if query fails
        }
    }

    /**
     * Finds records in a table with optional conditions.
     *
     * This method constructs a SELECT query to find records that match the given conditions.
     * The conditions can be combined using either AND or OR based on the provided boolean.
     *
     * @param string $table The database table to search in.
     * @param bool $with Determines if the conditions should be combined with AND or OR.
     * @param array $conditions An associative array of conditions (field => value).
     * @throws WPException If an error occurs during the find operation or if no conditions are specified.
     * @access public
     * @static
     * @return Tools The found records wrapped in a Tools object.
     */
    public static function findWith(string $table, bool $with, array $conditions): Tools {
       $findtype = $with ? ' AND ' : ' OR '; // Determine the logical operator based on $with
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name

        $where = [];
        $params = [];

         // Iterate through conditions and prepare the SQL where clause and parameters
        foreach ($conditions as $field => $value) {
            $field = self::validateInput(['field' => $field])['field']; // Sanitize field name
            $value = self::validateInput(['value' => $value])['value']; // Sanitize value
            $where[] = "`$field` = :$field"; // Construct part of where clause
            $params["$field"] = $value; // Store parameters for binding
        }

        if (count($where) === 0) {
            throw new WPException(" db => No conditions provided for WHERE clause."); // Throw exception if no conditions are provided
        }
      
        $query = "SELECT * FROM `$table` WHERE " . implode($findtype, $where); // Construct the full SQL query

        try {
            $stmt = self::prepareAndExecute($query, $params); // Execute query with parameters
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => FindWith function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

   /**
     * Retrieves all records from a specified table.
     *
     * This method fetches all records from the provided table name.
     *
     * @param string $table The database table to select records from.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return Tools All records wrapped in a Tools object.
     */
    public static function getAll(string $table): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        try {
            $stmt = self::prepareAndExecute("SELECT * FROM `$table`"); // Execute select query
             return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => GetAll function error: " . $e->getMessage());  // Throw exception if query fails
        }
    }

    /**
     * Retrieves the last record from a specified table based on the primary key.
     *
     * This method fetches the last record from the specified table.
     *
     * @param string $table The database table to query.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return Tools The last record wrapped in a Tools object.
     */
    public static function getLast(string $table): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $primaryKey1 = self::$primaryKey; // Get primary key from class variable
        $query = "SELECT * FROM `$table` ORDER BY `$primaryKey1` DESC LIMIT 1"; // Construct the SQL query
       
        try {
            $stmt = self::prepareAndExecute($query);  // Execute query
           return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
           throw new WPException(" db => GetLast function error: " . $e->getMessage());  // Throw exception if query fails
        }
    }

   /**
     * Retrieves the first record from a specified table based on the primary key.
     *
     * This method fetches the first record from the specified table.
     *
     * @param string $table The database table to query.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return Tools The first record wrapped in a Tools object.
     */
    public static function getFirst(string $table): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $primaryKey1 = self::$primaryKey; // Get primary key from class variable
        $query = "SELECT * FROM `$table` ORDER BY `$primaryKey1` ASC LIMIT 1";  // Construct the SQL query

         try {
             $stmt = self::prepareAndExecute($query);  // Execute query
             return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
             throw new WPException(" db => GetFirst function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

    /**
     * Groups records by a specified field and counts occurrences.
     *
     * This method constructs and executes a SQL query to group records by a specified field and count the occurrences within each group.
     *
     * @param string $table The database table to query.
     * @param string $field The field to group by.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return Tools The grouped records with counts wrapped in a Tools object.
     */
    public static function getGrouped(string $table, string $field): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $field = self::validateInput(['field' => $field])['field']; // Sanitize field name

        try {
            $query = "SELECT `$field`, COUNT(*) as count FROM `$table` GROUP BY `$field`";  // Construct the SQL query
            $stmt = self::prepareAndExecute($query); // Execute query
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => GetGrouped function error: " . $e->getMessage());  // Throw exception if query fails
        }
    }

    /**
     * Retrieves a random record from a specified table.
     *
     * This method fetches a single random record from the given table.
     *
     * @param string $table The database table to query.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return Tools A random record wrapped in a Tools object.
     */
    public static function getRandom(string $table): Tools {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name

        try {
            $query = "SELECT * FROM `$table` ORDER BY RAND() LIMIT 1"; // Construct random record select query
            $stmt = self::prepareAndExecute($query); // Execute the query
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => GetRandom function error: " . $e->getMessage());  // Throw exception if query fails
        }
    }

    /**
     * Counts the total number of records in a specified table.
     *
     * This method executes an SQL query to count all records in the provided table.
     *
     * @param string $table The database table to count records from.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return int The total count of records.
     */
    public static function total(string $table): int {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name

        try {
            $stmt = self::prepareAndExecute("SELECT COUNT(*) FROM `$table`"); // Execute query to count records
            return (int)$stmt->fetchColumn(); // Fetch count as an integer
        } catch (PDOException $e) {
            throw new WPException(" db => Total function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

    /**
     * Counts the total number of records for a specific field matching a given value.
     *
     * This method executes an SQL query to count the total number of records where a particular field matches a certain value.
     *
     * @param string $table The database table to query.
     * @param string $field The field to match against.
     * @param string $value The value to search for.
     * @throws WPException If an error occurs during the operation.
     * @access public
     * @static
     * @return int The total count of records matching the condition.
     */
    public static function totalField(string $table, string $field, string $value): int {
       $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $field = self::validateInput(['field' => $field])['field']; // Sanitize field name
        $value = self::validateInput(['value' => $value])['value']; // Sanitize value
        $query = "SELECT COUNT(*) FROM `$table` WHERE `$field` = :value"; // Construct the query with a condition

        try {
            $stmt = self::prepareAndExecute($query, [':value' => $value]); // Execute query with provided parameters
            return (int)$stmt->fetchColumn(); // Fetch count as an integer
        } catch (PDOException $e) {
            throw new WPException(" db => TotalField function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

    /**
     * Saves a record to the specified table, either inserting a new record or updating an existing one.
     *
     * This method either updates an existing record or inserts a new one depending on whether the primary key of the record is already set.
     *
     * @param string $table The database table to save the record to.
     * @param array $record An associative array of field values to save.
     * @throws WPException If an error occurs during the save operation.
     * @access public
     * @static
     * @return void
     */
    public static function save(string $table, array $record): void {
        $table = self::validateInput(['table' => $table])['table']; // Sanitize table name
        $record = self::validateInput($record); // Sanitize the entire record array

        try {
            if (!isset($record[self::$primaryKey])) {
                self::insert($table, $record); // If primary key is not set, insert a new record
            } else {
                self::update($table, $record);  // If primary key is set, update the existing record
            }
        } catch (PDOException $e) {
            throw new WPException(" db => Save function error: " . $e->getMessage());  // Throw exception if query fails
        }
    }

   /**
     * Updates an existing record in a specified table based on the primary key.
     *
     * This method executes an SQL update query to modify existing records.
     *
     * @param string $table The table to update the record in.
     * @param array $values An associative array of values to update.
     * @throws WPException If an error occurs during the update operation.
     * @access private
     * @static
     * @return void
     */
    private static function update(string $table, array $values): void {
         $query = "UPDATE `$table` SET "; // Start constructing the query
        $query .= implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($values))); // Generate SET part of the query dynamically
        $primaryKey1 = self::$primaryKey;  // Get primary key from the class variable
         $query .= " WHERE `$primaryKey1` = :primaryKey"; // Add where clause for identifying which record to update

        $values['primaryKey'] = $values[self::$primaryKey]; // Add primary key value to parameters

        try {
            $stmt = self::prepareAndExecute($query, $values); // Execute the query with the given values
        } catch (PDOException $e) {
           throw new WPException(" db => Update function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

    /**
     * Inserts a new record into a specified table.
     *
     * This method constructs and executes an SQL INSERT query to add a new record to the table.
     *
     * @param string $table The table to insert the record into.
     * @param array $values An associative array of values to insert.
     * @throws WPException If an error occurs during the insert operation.
     * @access private
     * @static
     * @return void
     */
    private static function insert(string $table, array $values): void {
        unset($values[self::$primaryKey]); // Remove the primary key before constructing insert query
        $query = "INSERT INTO `$table` (" . implode(', ', array_map(fn($key) => "`$key`", array_keys($values))) . ")"; // Generate columns of the insert statement dynamically
        $query .= " VALUES (" . implode(', ', array_map(fn($key) => ":$key", array_keys($values))) . ")"; // Generate values of the insert statement dynamically

        try {
            $stmt = self::prepareAndExecute($query, $values); // Execute the query with the given values
        } catch (PDOException $e) {
             throw new WPException(" db => Insert function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

  /**
     * Updates a specific field of a record in a table based on the primary key.
     *
     * This method updates a specified field of a record based on the primary key.
     *
     * @param string $table The table to update.
     * @param string $field The field to be updated.
     * @param string $value The new value for the specified field.
     * @throws WPException If an error occurs during the update operation.
     * @access public
     * @static
     * @return void
     */
    public static function updateField(string $table, string $field, string $value): void {
        $table = self::validateInput(['table' => $table])['table'];  // Sanitize table name
        $field = self::validateInput(['field' => $field])['field'];  // Sanitize field name
        $value = self::validateInput(['value' => $value])['value'];  // Sanitize value
        $primaryKey1 = self::$primaryKey; // Get primary key from class variable
        $query = "UPDATE `$table` SET `$field` = :value WHERE `$primaryKey1` = :primaryKey"; // Construct the SQL update query

        try {
           $stmt = self::prepareAndExecute($query, ['value' => $value, 'primaryKey' => $record[self::$primaryKey]]); // Execute the query with parameters
        } catch (PDOException $e) {
            throw new WPException(" db => updateField function error: " . $e->getMessage()); // Throw exception if query fails
        }
    }

    /**
    * Deletes a record from a table based on the primary key.
    *
    * This method deletes a single record from the given table which matches
    * the provided primary key.
    *
    * @param string $table The table to delete the record from.
    * @param string $value The primary key value of the record to delete.
    * @throws WPException If an error occurs during the delete operation.
    * @access public
    * @static
    * @return void
    */
    public static function delete(string $table, string $value): void
   {
     $table = self::validateInput(['table' => $table])['table'];
     $value = self::validateInput(['value' => $value])['value'];
     $primaryKey1 = self::$primaryKey;
     $query = "DELETE FROM `$table` WHERE `$primaryKey1` = :value";
        try {
             $stmt = self::prepareAndExecute($query, [':value' => $value]);
        } catch (PDOException $e) {
            throw new WPException(" db => Delete function error: " . $e->getMessage());
        }
   }

    /**
    * Truncates the given table, removing all records.
    *
    * This method clears out the provided table by using TRUNCATE,
    * which effectively deletes all the records very quickly.
    * Note: Be cautious when using this, it is irreversible.
    *
    * @param string $table The table to truncate.
    * @throws WPException If an error occurs during the truncate operation.
    * @access public
    * @static
    * @return void
    */
    public static function truncate(string $table): void
   {
       $table = self::validateInput(['table' => $table])['table'];
       $query = "TRUNCATE TABLE `$table`";

       try {
          $stmt = self::prepareAndExecute($query);
      } catch (PDOException $e) {
          throw new WPException(" db => truncate function error: " . $e->getMessage());
      }
   }

   /**
     * Validates and sanitizes input values to prevent SQL injection.
     *
     * This method filters and sanitizes input data.
     * It handles array or single value inputs. It checks for specific keys
     *  ('table', 'field', 'value') and applies different sanitization techniques.
     * For the 'table' and 'field' keys, it uses a more restrictive sanitization to only allow
     * alphanumeric characters and underscores, while 'value' is sanitized more broadly.
     * This aims to secure database interactions against SQL injection attacks.
     *
     * @param array|string $input The input data to validate and sanitize.
     * @access private
     * @static
     * @return array|string The sanitized input data.
     */
    private static function validateInput(array|string $input): array|string
    {
      if(is_array($input))
      {
        $sanitizedInput = [];
        foreach ($input as $key => $value) {
           if(is_string($value)) {
           switch($key){
              case 'table':
                   $sanitizedInput[$key] = preg_replace('/[^a-zA-Z0-9_]/', '', $value); // Sanitize table name
                  break;
              case 'field':
                   $sanitizedInput[$key] = preg_replace('/[^a-zA-Z0-9_]/', '', $value); // Sanitize field name
                  break;
              case 'value':
                  $sanitizedInput[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
// Sanitize value string
                  break;
              default:
                   $sanitizedInput[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
 // Sanitize other values
              }
          }
          else
              $sanitizedInput[$key] = $value; // return the original value if its not a string
          }
        return $sanitizedInput;
      }
      else if(is_string($input))
          return filter_var($input, FILTER_SANITIZE_STRING); // return string value after sanitizing
        return $input; // return original value if it's not an array or string
    }
    
    /**
     * Executes a dynamic database operation based on the input string, processing parameters and handling callbacks.
     *
     * This method interprets an input string in a specific format to dynamically determine the function
     * to call, the required parameters, and whether any additional operations (like data mapping or callbacks)
     * need to be executed. It ensures that the function is called with the correct number of parameters and
     * supports callbacks for further processing.
     * 
     * @param string $input The input string containing the function name and parameters.
     * @param callable|array|null $data The data to be mapped to input parameters (optional).
     * @param callable|null $callback A callback function to be executed after the main operation (optional).
     * 
     * @throws WPException If there is an issue with input validation, function execution, or callback handling.
     * @throws InvalidArgumentException If the provided data type is incorrect.
     * 
     * @access public
     * @static
     * @return mixed The result of the called function, or the value from the callback.
     */
    public static function run(string $input, callable|array $data = null, callable $callback = null) {
        // Define the expected number of parameters for each possible function.
        $numInput = [
            "select" => 2, "find" => 3, "findWith" => 3, "getAll" => 1, "getLast" => 1, 
            "getFirst" => 1, "getGrouped" => 2, "getRandom" => 1, "total" => 1, "totalField" => 3, 
            "save" => 2, "updateField" => 3, "delete" => 3, "truncate" => 1
        ];
    
        // Match and extract the function name, input parameters, and optional return value using regular expression.
        if (preg_match('/^(\w+)\|([^:]+)(?::\s*(.*))?$/', $input, $matches)) {
            $funcname = $matches[1]; // Extract function name
            $inputs = array_map('trim', explode('-', $matches[2])); // Extract and clean input parameters
            $return = isset($matches[3]) ? $matches[3] : ''; // Extract optional return statement
    
            // Validate if the function name is valid.
            if (!array_key_exists($funcname, $numInput)) {
                throw new WPException(" db => Wql: Function $funcname not found.");
            }
    
            // Validate if the number of input parameters matches the expected count.
            $expectedCount = $numInput[$funcname];
            $actualCount = count($inputs);
            if ($actualCount < $expectedCount) {
                throw new WPException(" db => Wql: Expected $expectedCount parameters, got $actualCount.");
            }
    
            // Handle data mapping for parameters based on the provided $data argument.
            if ($data) {
                if (is_array($data)) {
                    // Replace placeholders in inputs with actual data values from the $data array.
                    foreach ($inputs as $key => $inp) {
                        $word = str_replace('@', '', $inp);
                        if (array_key_exists($word, $data)) {
                            $inputs[$key] = $data[$word];
                        }
                    }
                } elseif (is_callable($data)) {
                    // Execute the callable $data function to perform additional processing on the inputs.
                    $data(self::class);
                }
            } else {
                throw new WPException(" db => Wql: Invalid data parameter type. Expected array or callable.");
            }
    
            // Execute the callback function if provided and valid.
            if ($callback && is_callable($callback) && !is_callable($data)) {
                $callback(self::class);
            }
    
            // Call the dynamically determined function with the processed inputs.
            $output = call_user_func_array([self::class, $funcname], $inputs);
    
            // Ensure the output is not null.
            if (is_null($output)) {
                throw new WPException(" db => Wql: The function $funcname returned null.");
            }
    
            // If the output is an object with a 'getArray' method, retrieve its array representation.
            if (is_object($output) && method_exists($output, 'getArray')) {
                $output = $output->getArray();
            }
    
            // Handle the optional return statement, accessing specific properties or indices in the output.
            if ($return) {
                $returnParts = explode('->', $return);
                $returnValue = $output;
    
                foreach ($returnParts as $part) {
                    if (is_array($returnValue) && isset($returnValue[$part])) {
                        $returnValue = $returnValue[$part];
                    } elseif (is_object($returnValue) && isset($returnValue->$part)) {
                        $returnValue = $returnValue->$part;
                    } else {
                        throw new WPException(" db => Invalid property or index: $part");
                    }
                }
                self::$output = $returnValue; // Store the result in the static $output property.
                return $returnValue;
            }
    
            self::$output = $output; // Store the result in the static $output property.
            return $output;
        } else {
            // If the input string does not match the expected format, throw a syntax error.
            throw new WPException(" db => Wql: syntax error.");
        }
    }

}
