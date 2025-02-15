<?php
namespace Woodpecker\DataBase;

use Woodpecker\Exceptions;


class Tools {
    
    private string $table;       // The name of the database table
    private string $primaryKey;  // The primary key of the table
    private array $collection;    // The collection of records (array of associative arrays)
    private \PDO $pdo;           // PDO instance for database interactions
    
    /**
     * Constructor for the Tool class.
     *
     * Initializes a Tool instance with the provided PDO connection, table name,
     * primary key, and data collection.
     *
     * @param \PDO $pdo         The PDO instance for communicating with the database.
     * @param string $table     The name of the table this tool interacts with.
     * @param string $primaryKey The primary key for the table.
     * @param array $collection  The collection of records to operate on.
     */
    public function __construct(\PDO $pdo, string $table, string $primaryKey, array $collection) {
        
        
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->collection = $collection;
        $this->pdo = $pdo;
    }

    /**
     * Returns the collection as an array.
     *
     * This method checks if the collection is a valid array and returns it.
     *
     * @return array The collection of records.
     * @throws WPException If the collection is not a valid array.
     */
    public function getArray(): array {
        if (!is_array($this->collection)) {
            throw new WPException(" db =>getArrayFunction: Collection is not a valid array.");
        }
        return $this->collection;
    }

    /**
     * Converts the collection to a JSON string.
     *
     * This method encodes the collection into a JSON format and handles 
     * any encoding errors gracefully.
     *
     * @return string The collection encoded as a JSON string.
     * @throws WPException If an error occurs during JSON encoding.
     */
    public function getJson(): string {
        try {
            return json_encode($this->collection, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new WPException(" db => db =>Error encoding JSON: " . $e->getMessage());
        }
    }

    /**
     * Finds records in the collection by field value.
     *
     * This method searches the collection for records that match the given 
     * field and value, returning an array of matching records.
     *
     * @param string $field The field to match on.
     * @param mixed $value The value to match against.
     * @return array An array of matching records.
     */
    public function find(string $field, $value): array {
        $rows = [];
        foreach ($this->collection as $row) {
            if (isset($row[$field]) && $row[$field] == $value) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Deletes the first record in the collection from the database.
     *
     * This method removes a record from the table based on the primary key 
     * of the first item in the collection.
     *
     * @throws WPException If the primary key is not found in the collection.
     * @throws WPException If an error occurs during the delete operation.
     */
    public function delete(): void {
        if (!isset($this->collection[0][$this->primaryKey])) {
            throw new WPException(" db => db =>Primary key '{$this->primaryKey}' not found in the collection.");
        }

        $value = $this->collection[0][$this->primaryKey];
        $query = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :value";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(":value", $value, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new WPException(" db =>deleteFunction: " . $e->getMessage());
        }
    }

    /**
     * Updates the first record in the collection with new values.
     *
     * This method updates the record in the database that matches the 
     * primary key of the first item in the collection with the specified 
     * values.
     *
     * @param array $values The new values for the update.
     * @throws WPException If the primary key is not found in the collection.
     * @throws WPException If an error occurs during the update operation.
     */
    public function update(array $values): void {
        if (!isset($this->collection[0][$this->primaryKey])) {
            throw new WPException(" db =>Primary key '{$this->primaryKey}' not found in the collection.");
        }

        // Add the primary key value to the value set for the update
        $values[$this->primaryKey] = $this->collection[0][$this->primaryKey];

        // Build the update query dynamically based on the provided values
        $query = "UPDATE `{$this->table}` SET ";
        $query .= implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($values)));
        $query .= " WHERE `{$this->primaryKey}` = :{$this->primaryKey}";

        try {
            $stmt = $this->pdo->prepare($query);

            // Bind the values to the statement
            foreach ($values as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
        } catch (\PDOException $e) {
            throw new WPException(" db =>updateFunction: " . $e->getMessage());
        }
    }

    /**
     * Counts the number of records in the collection.
     *
     * @return int The total number of records in the collection.
     */
    public function count(): int {
        return count($this->collection);
    }

    /**
     * Returns the last record from the collection.
     *
     * This method retrieves the last item from the collection. It throws 
     * an exception if the collection doesn't contain enough items.
     *
     * @return array The last record in the collection.
     * @throws WPException If the collection must have at least two values.
     */
    public function getLast(): array {
        if (count($this->collection) <= 2) {
            throw new WPException(" db =>getLastFunction: Collection must have at least 2 values.");
        }
        return $this->collection[count($this->collection) - 1];
    }

    /**
     * Returns the first record from the collection.
     *
     * This method retrieves the first item from the collection. It throws 
     * an exception if the collection doesn't contain enough items.
     *
     * @return array The first record in the collection.
     * @throws WPException If the collection must have at least two values.
     */
    public function getFirst(): array {
        if (count($this->collection) <= 2) {
            throw new WPException(" db =>getFirstFunction: Collection must have at least 2 values.");
        }
        return $this->collection[0];
    }

    /**
     * Returns a random record from the collection.
     *
     * This method randomly selects and returns one item from the collection.
     *
     * @return array A random record from the collection.
     */
    public function getRandom(): array {
        $randomKey = array_rand($this->collection);
        return $this->collection[$randomKey];
    }

    /**
     * Groups the collection by a specified key.
     *
     * This method organizes the collection into groups based on the 
     * specified key, returning an array of grouped records.
     *
     * @param mixed $key The key to group by.
     * @return array An associative array of grouped records.
     * @throws WPException If the collection must have at least two values.
     */
    public function groupBy($key): array {
        if (count($this->collection) <= 2) {
            throw new WPException(" db =>groupByFunction: Collection must have at least 2 values.");
        }
        
        $result = [];
        foreach ($this->collection as $item) {
            if (is_array($key)) {
                $group = implode('-', array_map(fn($k) => $item[$k] ?? '', $key));
            } else {
                $group = $item[$key] ?? '';
            }

            if (!isset($result[$group])) {
                $result[$group] = [];
            }
            $result[$group][] = $item;
        }
        return $result;
    }
}
?>
