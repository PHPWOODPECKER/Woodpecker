<?php

namespace Woodpecker\DataBase;

use Woodpecker\Exceptions;
use Woodpecker\Facade;

/**
 * Class DataBase
 */

class DataBase extends Facade
{
    private static ?PDO $pdo = null; // Allow for null PDO initially
    
    /**
     * Establishes a connection to the MySQL database using PDO.
     *
     * This method initializes the database connection using the provided
     * host, database name, username, and password. It sets the PDO attributes
     * to enable exception handling for errors and sets the default fetch mode.
     *
     * @param array $connList The database address.
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
    try {
        self::$pdo = new PDO(
            $ConnList['driver'] . ":host=" . $ConnList['host'] . ";dbname=" . $ConnList['dbname'] . ";charset=" . $ConnList['charset'] ?? 'utf8mb4',
            $ConnList['username'],
            $ConnList['password']
        );
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new WPException("Connection failed: " . $e->getMessage());
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
    private static function prepareAndExecute(string $query, array $params = []): \PDOStatement
    {
        if (!self::$pdo) {
            throw new WPException('No database connection established.');
        }

        try {
            $stmt = self::$pdo->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute(); 
            return $stmt; 
        } catch (PDOException $e) {
            throw new WPException("prepareAndExecute error: " . $e->getMessage());
        }
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $table The name of the table to create.
     * @param array $columns An array of column definitions.
     * @throws WPException If an error occurs during table creation.
     * @access public
     * @static
     * @return void
     */
    public static function createTable(string $table, array $columns): void
    {
        if (empty($table)) {
            throw new WPException('Table name cannot be empty.');
        }
        if (empty($columns)) {
            throw new WPException('Columns cannot be empty.');
        }
        $table = self::validateInput(['table' => $table])['table'];
        $columns = self::validateInput($columns);

        $sql = "CREATE TABLE IF NOT EXISTS `$table` (";
        $colDefinitions = [];
        foreach ($columns as $columnName => $columnDetails) {
          if (!is_array($columnDetails) || !isset($columnDetails['type'])){
            throw new WPException('Column definition must be an array with a "type" key.');
          }
            $colDefinition = "`{$columnName}` {$columnDetails['type']}";
            if(isset($columnDetails['length'])) {
                $colDefinition .= "({$columnDetails['length']})";
            }
            if(isset($columnDetails['notnull']) && $columnDetails['notnull']) {
              $colDefinition .= " NOT NULL";
            }
            if(isset($columnDetails['auto_increment']) && $columnDetails['auto_increment']){
              $colDefinition .= " AUTO_INCREMENT";
            }
            if(isset($columnDetails['primary_key']) && $columnDetails['primary_key']){
              $colDefinition .= " PRIMARY KEY";
            }

            $colDefinitions[] = $colDefinition;
        }
        $sql .= implode(', ', $colDefinitions);
        $sql .= ")";

        self::prepareAndExecute($sql);
    }

    /**
     * Drops a table from the database.
     *
     * @param string $table The name of the table to drop.
     * @throws WPException If an error occurs during table dropping.
     * @access public
     * @static
     * @return void
     */
    public static function dropTable(string $table): void
    {
        if (empty($table)) {
            throw new WPException('Table name cannot be empty.');
        }
        $table = self::validateInput(['table' => $table])['table'];
        
        $sql = "DROP TABLE IF EXISTS `$table`";
        self::prepareAndExecute($sql);
    }


    /**
     * Adds a new column to the table
     *
     * @param string $table
     * @param string $columnName
     * @param array $columnDetails
     * @throws WPException
     * @access public
     * @static
     * @return void
     */
    public static function addColumn(string $table, string $columnName, array $columnDetails): void
    {
        if (empty($table) || empty($columnName) || empty($columnDetails)) {
          throw new WPException('Table name, column name and column details cannot be empty.');
        }
        if (!is_array($columnDetails) || !isset($columnDetails['type'])){
          throw new WPException('Column definition must be an array with a "type" key.');
        }
        $table = self::validateInput(['table' => $table])['table'];
        $columnName = self::validateInput(['column' => $columnName])['column'];
        $columnDetails = self::validateInput($columnDetails);
        
        $sql = "ALTER TABLE `$table` ADD COLUMN `$columnName` {$columnDetails['type']}";
        if(isset($columnDetails['length'])) {
            $sql .= "({$columnDetails['length']})";
        }
        if(isset($columnDetails['notnull']) && $columnDetails['notnull']) {
            $sql .= " NOT NULL";
        }
        if(isset($columnDetails['auto_increment']) && $columnDetails['auto_increment']){
          $sql .= " AUTO_INCREMENT";
        }
        if(isset($columnDetails['primary_key']) && $columnDetails['primary_key']){
          $sql .= " PRIMARY KEY";
        }

        self::prepareAndExecute($sql);
    }

      /**
       * Drops a column from the table
       *
       * @param string $table
       * @param string $columnName
       * @throws WPException
       * @access public
       * @static
       * @return void
       */
    public static function dropColumn(string $table, string $columnName): void
    {
        if (empty($table) || empty($columnName)) {
          throw new WPException('Table name and column name cannot be empty.');
        }
      $table = self::validateInput(['table' => $table])['table'];
      $columnName = self::validateInput(['column' => $columnName])['column'];
      
        $sql = "ALTER TABLE `$table` DROP COLUMN `$columnName`";
        self::prepareAndExecute($sql);
    }
      /**
       * Rename a column
       *
       * @param string $table
       * @param string $oldColumnName
       * @param string $newColumnName
       * @param array $columnDetails
       * @throws WPException
       * @access public
       * @static
       * @return void
       */
    public static function renameColumn(string $table, string $oldColumnName, string $newColumnName, array $columnDetails): void
    {
        if (empty($table) || empty($oldColumnName) || empty($newColumnName) || empty($columnDetails)) {
          throw new WPException('Table name, old column name, new column name, and column details cannot be empty.');
        }
        if (!is_array($columnDetails) || !isset($columnDetails['type'])){
          throw new WPException('Column definition must be an array with a "type" key.');
        }
        
      $table = self::validateInput(['table' => $table])['table'];
      $oldColumnName = self::validateInput(['oldColumn' => $oldColumnName])['oldColumn'];
      $newColumnName = self::validateInput(['newColumn' => $newColumnName])['newColumn'];
      $columnDetails = self::validateInput($columnDetails);

        $sql = "ALTER TABLE `$table` CHANGE COLUMN `$oldColumnName` `$newColumnName` {$columnDetails['type']}";
        if(isset($columnDetails['length'])) {
           $sql .= "({$columnDetails['length']})";
        }
        if(isset($columnDetails['notnull']) && $columnDetails['notnull']) {
           $sql .= " NOT NULL";
        }
        if(isset($columnDetails['auto_increment']) && $columnDetails['auto_increment']){
          $sql .= " AUTO_INCREMENT";
        }
        if(isset($columnDetails['primary_key']) && $columnDetails['primary_key']){
          $sql .= " PRIMARY KEY";
        }

        self::prepareAndExecute($sql);
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
