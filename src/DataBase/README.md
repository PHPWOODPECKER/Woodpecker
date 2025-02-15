# Woodpecker Database Management System

This repository provides a set of PHP classes designed for efficient and secure database management. It includes the `DataBase`, `Table`, and `Tools` classes, each offering distinct functionalities for database interactions, from connection management to data manipulation and utility functions.

## Table of Contents

- [Features](#features)
- [DataBase Class](#database-class)
    - [Key Features](#database-key-features)
    - [Methods](#database-methods)
    - [Usage Examples](#database-usage-examples)
- [Table Class](#table-class)
    - [Key Features](#table-key-features)
    - [Methods](#table-methods)
    - [Usage Examples](#table-usage-examples)
- [Tools Class](#tools-class)
    - [Key Features](#tools-key-features)
    - [Methods](#tools-methods)
    - [Usage Examples](#tools-usage-examples)
- [Exceptions](#exceptions)
- [Installation](#installation)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Secure Database Interactions**: Utilizes PDO for secure database connections and SQL query execution, preventing SQL injection vulnerabilities.
- **CRUD Operations**: Comprehensive support for Create, Read, Update, and Delete operations across database tables.
- **Utility Functions**: Includes a suite of helper functions for data formatting, validation, and other common tasks.
- **Exception Handling**: Custom exceptions for graceful error handling during database operations.

## DataBase Class

The `DataBase` class is responsible for establishing and managing connections to a MySQL database. It provides methods for connecting, disconnecting, and executing SQL queries.

### Key Features

- **Connection Management**: Establishes and closes the database connection.
- **SQL Execution**: Prepares and executes SQL queries with parameter binding to prevent SQL injection.
- **Table Management**: Methods to create, drop, add, rename, and drop columns in a database table.

### Methods

- `connection(array $ConnList)`: Establishes a connection to the database using the provided connection parameters.
- `disconnection()`: Closes the database connection.
- `createTable(string $tableName, array $columns)`: Creates a new table with specified columns.
- `dropTable(string $tableName)`: Drops an existing table.
- `addColumn(string $tableName, string $columnName, array $columnDetails)`: Adds a new column to an existing table.
- `dropColumn(string $tableName, string $columnName)`: Drops a column from a table.
- `renameColumn(string $tableName, string $oldColumnName, string $newColumnName, array $columnDetails)`: Renames a column in a table.
- `prepareAndExecute(string $query, array $params = [])`: Prepares and executes a SQL statement.

### Usage Examples

```php
use Woodpecker\DataBase\DataBase;

$connectionParams = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'my_database',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4'
];

DataBase::connection($connectionParams);
DataBase::createTable('users', [
    'id' => ['type' => 'INT', 'length' => 11, 'notnull' => true, 'auto_increment' => true, 'primary_key' => true],
    'name' => ['type' => 'VARCHAR', 'length' => 255, 'notnull' => true],
    'email' => ['type' => 'VARCHAR', 'length' => 255, 'notnull' => true]
]);
DataBase::disconnection();

// Example of prepareAndExecute
$stmt = DataBase::prepareAndExecute('SELECT * FROM users WHERE status = :status', ['status' => 'active']);
```
##Table Class

The Table class provides an interface for performing CRUD operations on database tables. It includes methods for selecting, inserting, updating, and deleting records, as well as utility functions for data retrieval and manipulation.

##Key Features

CRUD Operations: Supports creating, reading, updating, and deleting records.
- **Data Retrieval**: Fetches records based on various conditions.
Input Validation: Implements input validation to prevent SQL injection attacks.

##Methods
- `connection(array $connectionParams, string $primaryKey)`: Establishes a connection to the database using the provided connection parameters and sets the primary key.  
- `disconnection()` : Closes the database connection.
  
- `select(string $table, string $field)` : Retrieves a specific field from a table.  
- `find(string $table, string $field, string $value)` : Finds a record based on a specific field and value.  
- `findWith(string $table, bool $and, array $conditions)` : Finds records based on multiple conditions.  
- `getAll(string $table)` : Retrieves all records from a table.
- `getLast(string $table)` : Gets the last record based on the primary key.
- `getFirst(string $table)` : Gets the first record based on the primary key.
- `getGrouped(string $table, string $field)` : Groups records by a specific field and counts occurrences.
- `getRandom(string $table)` : Retrieves a random record from a table.
- `total(string $table)` : Counts the total number of records in a table.
- `totalField(string $table, string $field, string $value)` : Counts the total number of records for a specific field matching a given value.
- `save(string $table, array $record)` : Saves a record to the specified table, either inserting a new record or updating an existing one.
- `update(string $table, array $values)` : Updates an existing record in a specified table based on the primary key.
- `insert(string $table, array $values)` : Inserts a new record into a specified table.
- `updateField(string $table, string $field, string $value)` : Updates a specific field of a record in a table based on the primary key.
- `delete(string $table, string $value)`: Deletes a record from a table based on the primary key.
- `truncate(string $table)` : Truncates the given table, removing all records.
- `validateInput(array|string $input)`: Validates and sanitizes input values to prevent SQL injection.
- `run(string $input, callable|array $data = null, callable $callback = null)`: Executes a dynamic database operation based on the input string.
Usage Examples
```php
use Woodpecker\DataBase\Table;

$connectionParams = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'your_database',
    'charset' => 'utf8',
    'username' => 'your_username',
    'password' => 'your_password'
];

Table::connection($connectionParams, 'id'); // 'id' is the primary key

// Retrieve all records from a table
$allUsers = Table::getAll('users');

// Find a user by email
$user = Table::find('users', 'email', 'john@example.com');

// Count the total number of users
$totalUsers = Table::total('users');

// Save a new user record
Table::save('users', [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'status' => 'active'
]);

Table::disconnection();
```

##Tools Class

The Tools class provides a set of utility methods for common tasks such as data formatting, string manipulation, and record manipulation.

##Key Features

**Data Conversion**: Converts collections to arrays or JSON strings.
**Data Manipulation**: Includes methods for finding, deleting, updating, and grouping records.
**Collection Handling**: Provides functionalities to retrieve the first, last, or a random record from a collection.
Methods

- `__construct(\PDO $pdo, string $table, string $primaryKey, array $collection)` : Initializes a new instance of the Tools class.
- 
- `getArray()` : Returns the collection as an array.
- `getJson()`: Converts the collection to a JSON string.
- `find(string $field, $value)` : Searches the collection for records that match the given field and value.
- `delete()` : Deletes the first record in the collection from the database based on the primary key.
- `update(array $values)` : Updates the first record in the collection with new values.
- `count()` : Counts the number of records in the collection.
- `getLast()` : Returns the last record from the collection.
- `getFirst()` : Returns the first record from the collection.
- `getRandom()` : Returns a random record from the collection.
- `groupBy($key)` : Groups the collection by a specified key.
