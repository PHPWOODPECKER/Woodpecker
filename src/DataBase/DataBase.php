<?php

namespace Woodpecker\DataBase;

// Import the namespace for handling exceptions and errors in the project
use Woodpecker\Exceptions;

/**
 * Class DataBase
 * 
 * 
 */

class DataBase
{
    private static ?PDO $pdo = null; // Allow for null PDO initially
    
    /**
     * Establishes a connection to the MySQL database using PDO.
     *
     * This method initializes the database connection using the provided
     * host, database name, username, and password. It sets the PDO attributes
     * to enable exception handling for errors and sets the default fetch mode.
     *
     * @param array $ConnList The database address.
     * @param string $primaryKey The primary key field name for the table.
     * @throws DBException If the database connection fails.
     * @access public
     * @static
     * @return void
     */
    public static function connection(array $ConnList): void {
      
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
        throw new DBException("Connection failed: " . $e->getMessage()); // Throw custom exception for connection failures
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
     * @throws DBException If an error occurs during query preparation or execution.
     * @access private
     * @static
     * @return PDOStatement The prepared and executed statement.
     */
    private static function prepareAndExecute(string $query, array $params = []): \PDOStatement
    {
        if (!self::$pdo) {
            throw new DBException('No database connection established.');
        }

        try {
            $stmt = self::$pdo->prepare($query);  // Prepare the SQL statement
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value); // Bind parameters to the statement using named placeholders
            }
            $stmt->execute(); // Execute the prepared statement
            return $stmt; // Return the PDOStatement object
        } catch (PDOException $e) {
            throw new DBException("prepareAndExecute error: " . $e->getMessage()); // Throw custom exception if query fails
        }
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $tableName The name of the table to create.
     * @param array $columns An array of column definitions.
     * @throws DBException If an error occurs during table creation.
     * @access public
     * @static
     * @return void
     */
    public static function createTable(string $tableName, array $columns): void
    {
        if (empty($tableName)) {
            throw new DBException('Table name cannot be empty.');
        }
        if (empty($columns)) {
            throw new DBException('Columns cannot be empty.');
        }

        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (";
        $colDefinitions = [];
        foreach ($columns as $columnName => $columnDetails) {
          if (!is_array($columnDetails) || !isset($columnDetails['type'])){
            throw new DBException('Column definition must be an array with a "type" key.');
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
     * @param string $tableName The name of the table to drop.
     * @throws DBException If an error occurs during table dropping.
     * @access public
     * @static
     * @return void
     */
    public static function dropTable(string $tableName): void
    {
        if (empty($tableName)) {
            throw new DBException('Table name cannot be empty.');
        }
        $sql = "DROP TABLE IF EXISTS `$tableName`";
        self::prepareAndExecute($sql);
    }


    /**
     * Adds a new column to the table
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $columnDetails
     * @throws DBException
     * @access public
     * @static
     * @return void
     */
    public static function addColumn(string $tableName, string $columnName, array $columnDetails): void
    {
        if (empty($tableName) || empty($columnName) || empty($columnDetails)) {
          throw new DBException('Table name, column name and column details cannot be empty.');
        }
        if (!is_array($columnDetails) || !isset($columnDetails['type'])){
          throw new DBException('Column definition must be an array with a "type" key.');
        }
        $sql = "ALTER TABLE `$tableName` ADD COLUMN `$columnName` {$columnDetails['type']}";
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
       * @param string $tableName
       * @param string $columnName
       * @throws DBException
       * @access public
       * @static
       * @return void
       */
    public static function dropColumn(string $tableName, string $columnName): void
    {
        if (empty($tableName) || empty($columnName)) {
          throw new DBException('Table name and column name cannot be empty.');
        }
        $sql = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
        self::prepareAndExecute($sql);
    }
      /**
       * Rename a column
       *
       * @param string $tableName
       * @param string $oldColumnName
       * @param string $newColumnName
       * @param array $columnDetails
       * @throws DBException
       * @access public
       * @static
       * @return void
       */
    public static function renameColumn(string $tableName, string $oldColumnName, string $newColumnName, array $columnDetails): void
    {
        if (empty($tableName) || empty($oldColumnName) || empty($newColumnName) || empty($columnDetails)) {
          throw new DBException('Table name, old column name, new column name, and column details cannot be empty.');
        }
        if (!is_array($columnDetails) || !isset($columnDetails['type'])){
          throw new DBException('Column definition must be an array with a "type" key.');
        }

        $sql = "ALTER TABLE `$tableName` CHANGE COLUMN `$oldColumnName` `$newColumnName` {$columnDetails['type']}";
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
}
