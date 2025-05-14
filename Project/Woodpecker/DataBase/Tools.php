<?php
namespace Woodpecker\DataBase;

use Woodpecker\Exceptions;
use Woodpecker\Support\Collection;

class Tools extends Collection {
    
    private string $table;
    private string $primaryKey;
    
    private \PDO $pdo;
    
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
    public function __construct(\PDO $pdo, string $table, string $primaryKey, array $collection) :void 
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->collection = $collection;
        $this->pdo = $pdo;
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
    public function getJson(): string 
    {
        try {
            return json_encode($this->collection, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new WPException(" db => db =>Error encoding JSON: " . $e->getMessage());
        }
    }


    public function delete(): void 
    {
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
    public function update(array $values): void 
    {
        if (!isset($this->collection[0][$this->primaryKey])) {
            throw new WPException(" db =>Primary key '{$this->primaryKey}' not found in the collection.");
        }

        $values[$this->primaryKey] = $this->collection[0][$this->primaryKey];


        $query = "UPDATE `{$this->table}` SET ";
        $query .= implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($values)));
        $query .= " WHERE `{$this->primaryKey}` = :{$this->primaryKey}";

        try {
            $stmt = $this->pdo->prepare($query);

            foreach ($values as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
        } catch (\PDOException $e) {
            throw new WPException(" db =>updateFunction: " . $e->getMessage());
        }
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
    public function groupBy($key): array 
    {
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