<?php
namespace Woodpecker\DataBase;

// Import necessary Toolss for working with database tables from the namespace Woodpecker\DataBase\Table\Tools
use Woodpecker\DataBase\Tools;
use Woodpecker\Facade;

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
class Table extends Facade
{

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
    public static function init(): void 
    {
    $config = require_once("/../../config.php");
      $ConnList = $config['Database'];
      
    self::$primaryKey = $ConnList['primarykey'] ?? 'id';
    try {
        self::$pdo = new PDO(
            $ConnList['driver'] . ":host=" . $ConnList['host'] . ";dbname=" . $ConnList['dbname'] . ";charset=" . $ConnList['charset'],
            $ConnList['username'],
            $ConnList['password']
        );
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new WPException(" db => Connection failed: " . $e->getMessage());
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
    public static function disconnection(): void 
    {
        self::$pdo = null; 
    }

    /**
     * Prepares and executes a given SQL query .
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
    private static function prepareAndExecute(string $query, array $params = []): PDOStatement 
    {
        try {
            $stmt = self::$pdo->prepare($query);
            foreach ($params as $key => $value) {
                 $stmt->bindValue(":$key", $value); 
            }
            $stmt->execute(); 
            return $stmt; 
        } catch (PDOException $e) {
            throw new WPException(" db => prepareAndExecute error: " . $e->getMessage()); 
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
    public static function select(string $table, string $field): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        $field = self::validateInput(['field' => $field])['field']; 
        $sql = "SELECT `$field` FROM `$table`"; 

        try {
            $stmt = self::prepareAndExecute($sql);  
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => Select function error: " . $e->getMessage());
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
    public static function find(string $table, string $field, string $value): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        $field = self::validateInput(['field' => $field])['field']; 
        $value = self::validateInput(['value' => $value])['value']; 
        $query = "SELECT * FROM `$table` WHERE `$field` = :value";  

        try {
            $stmt = self::prepareAndExecute($query, ['value' => $value]); 
             return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => Find function error: " . $e->getMessage()); 
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
    public static function findWith(string $table, bool $with, array $conditions): Tools 
    {
       $findtype = $with ? ' AND ' : ' OR '; 
        $table = self::validateInput(['table' => $table])['table']; 

        $where = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            $field = self::validateInput(['field' => $field])['field']; 
            $value = self::validateInput(['value' => $value])['value'];
            $where[] = "`$field` = :$field"; 
            $params["$field"] = $value; 
        }

        if (count($where) === 0) {
            throw new WPException(" db => No conditions provided for WHERE clause."); 
        }
      
        $query = "SELECT * FROM `$table` WHERE " . implode($findtype, $where); 

        try {
            $stmt = self::prepareAndExecute($query, $params);  
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => FindWith function error: " . $e->getMessage()); 
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
    public static function getAll(string $table): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        try {
            $stmt = self::prepareAndExecute("SELECT * FROM `$table`"); 
             return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => GetAll function error: " . $e->getMessage());  
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
    public static function getLast(string $table): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        $query = "SELECT * FROM `$table` ORDER BY `" .self::$primaryKey. "` DESC LIMIT 1"; 
       
        try {
            $stmt = self::prepareAndExecute($query);  
           return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
           throw new WPException(" db => GetLast function error: " . $e->getMessage());  
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
    public static function getFirst(string $table): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        $primaryKey1 = self::$primaryKey; 
        $query = "SELECT * FROM `$table` ORDER BY `" . self::$primaryKey ."` ASC LIMIT 1";  
         try {
             $stmt = self::prepareAndExecute($query);  
             return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
             throw new WPException(" db => GetFirst function error: " . $e->getMessage()); 
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
    public static function getGrouped(string $table, string $field): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        $field = self::validateInput(['field' => $field])['field']; 

        try {
            $query = "SELECT `$field`, COUNT(*) as count FROM `$table` GROUP BY `$field`";  
            $stmt = self::prepareAndExecute($query); 
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => GetGrouped function error: " . $e->getMessage());  
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
    public static function getRandom(string $table): Tools 
    {
        $table = self::validateInput(['table' => $table])['table']; 

        try {
            $query = "SELECT * FROM `$table` ORDER BY RAND() LIMIT 1";
            $stmt = self::prepareAndExecute($query); 
            return new Tools(self::$pdo, $table, self::$primaryKey, $stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new WPException(" db => GetRandom function error: " . $e->getMessage());  
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
    public static function total(string $table): int 
    {
        $table = self::validateInput(['table' => $table])['table']; 

        try {
            $stmt = self::prepareAndExecute("SELECT COUNT(*) FROM `$table`");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new WPException(" db => Total function error: " . $e->getMessage()); 
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
    public static function totalField(string $table, string $field, string $value): int
    {
       $table = self::validateInput(['table' => $table])['table']; 
        $field = self::validateInput(['field' => $field])['field']; 
        $value = self::validateInput(['value' => $value])['value'];
        $query = "SELECT COUNT(*) FROM `$table` WHERE `$field` = :value"; 

        try {
            $stmt = self::prepareAndExecute($query, [':value' => $value]); 
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new WPException(" db => TotalField function error: " . $e->getMessage()); 
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
    public static function save(string $table, array $record): void 
    {
        $table = self::validateInput(['table' => $table])['table']; 
        $record = self::validateInput($record); 

        try {
            if (!isset($record[self::$primaryKey])) {
                self::insert($table, $record); 
            } else {
                self::update($table, $record);  
            }
        } catch (PDOException $e) {
            throw new WPException(" db => Save function error: " . $e->getMessage());  
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
    private static function update(string $table, array $values): void 
    {
         $query = "UPDATE `$table` SET "; 
        $query .= implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($values))); 
        $primaryKey1 = self::$primaryKey;  
         $query .= " WHERE `$primaryKey1` = :primaryKey"; 

        $values['primaryKey'] = $values[self::$primaryKey]; 

        try {
            $stmt = self::prepareAndExecute($query, $values); 
        } catch (PDOException $e) {
           throw new WPException(" db => Update function error: " . $e->getMessage()); 
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
    private static function insert(string $table, array $values): void 
    {
        unset($values[self::$primaryKey]); 
        $query = "INSERT INTO `$table` (" . implode(', ', array_map(fn($key) => "`$key`", array_keys($values))) . ")";
        $query .= " VALUES (" . implode(', ', array_map(fn($key) => ":$key", array_keys($values))) . ")"; 

        try {
            $stmt = self::prepareAndExecute($query, $values);  
        } catch (PDOException $e) {
             throw new WPException(" db => Insert function error: " . $e->getMessage()); 
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
     * @param string $key the primarykey to find item.
     * @throws WPException If an error occurs during the update operation.
     * @access public
     * @static
     * @return void
     */
    public static function updateField(string $table, string $field, string $value, string $key): void 
    {
        $table = self::validateInput(['table' => $table])['table'];  
        $field = self::validateInput(['field' => $field])['field'];  
        $value = self::validateInput(['value' => $value])['value'];
        $key = self::validateInput(['key' => $key])['key'];
        
        $query = "UPDATE `$table` SET `$field` = :value WHERE `" . self::$primaryKey . "` = :primaryKey"; 

        try {
           $stmt = self::prepareAndExecute($query, ['value' => $value, 'primaryKey' => $key]);  
        } catch (PDOException $e) {
            throw new WPException(" db => updateField function error: " . $e->getMessage()); 
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
                   $sanitizedInput[$key] = preg_replace('/[^a-zA-Z0-9_]/', '', $value); 
                  break;
              case 'field':
                   $sanitizedInput[$key] = preg_replace('/[^a-zA-Z0-9_]/', '', $value); 
                  break;
              case 'value':
                  $sanitizedInput[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                  break;
              default:
                   $sanitizedInput[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
              }
          }
          else
              $sanitizedInput[$key] = $value; 
          }
        return $sanitizedInput;
      }
      else if(is_string($input))
          return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return $input;
    }
}
