<?php

namespace Woodpecker\Support;

/**
 * Class Collection
 *
 * This class provides a fluent, convenient interface for working with arrays of data.
 * It is inspired by Laravel's Collection class and offers a variety of methods for
 * manipulating and processing data within a collection.
 *
 * @package Woodpecker\Support
 */
class Collection {

  /**
   * @var array The underlying array of items in the collection.
   */
  protected array $collection = [];

  /**
   * Collection constructor.
   *
   * @param array $collection An array of items to initialize the collection with.
   */
  public function __construct(array $collection = []) {
    $this->collection = $collection;
  }
  
  /**
   * Convert the collection to a string representation.
   *
   * @return string The string representation of the collection.
   */
  public function __toString(): string 
  {
    return json_encode($this->collection, JSON_THROW_ON_ERROR);
  }
  
  /**
   * Magic method to get an item from the collection by key.
   *
   * @param string $key The key of the item to retrieve.
   * @return mixed The value of the item at the given key.
   */
  public function __get(string $key): mixed 
  {
    return $this->collection[$key] ?? null;
  }
  
  /**
   * Magic method to set an item in the collection by key.
   *
   * @param string $key The key of the item to set.
   * @param mixed $value The value to set.
   */
  public function __set(string $key, mixed $value): void 
  {
    $this->collection[$key] = $value;
  }

  /**
   * Convert the collection to JSON.
   *
   * @return string JSON representation of the collection.
   */
  public function toJson(): string 
  {
    return json_encode($this->collection, JSON_THROW_ON_ERROR);
  }

  /**
   * Get all of the items in the collection.
   *
   * @return array All items in the collection.
   */
  public function all(): array 
  {
    return $this->collection;
  }

  /**
   * Count the number of items in the collection.
   *
   * @return int The number of items in the collection.
   */
  public function count(): int 
  {
    return count($this->collection);
  }

  /**
   * Calculate the average value of the items in the collection.
   *
   * @return float The average value of the items in the collection. Returns 0 if the collection is empty.
   */
  public function average(): float 
  {
    if ($this->count() === 0) {
      return 0;
    }

    return array_sum($this->collection) / $this->count();
  }

  /**
   * Chunk the collection into smaller collections of the given size.
   *
   * @param int $size The size of each chunk.
   * @return array A multi-dimensional array where each sub-array is a chunk of the original collection.
   */
  public function chunk(int $size): array 
  {
    if ($size <= 0) {
      return [$this->collection]; // Return the entire collection as a single chunk
    }

    return array_chunk($this->collection, $size);
  }

  /**
   * Collapse a collection of arrays into a single, flat collection.
   *
   * @return Collection A new Collection instance containing the collapsed array.
   */
  public function collapse(): Collection 
  {
    $collapse = [];

    foreach ($this->collection as $item) {
      if (is_array($item)) {
        $collapse = array_merge($collapse, $item);
      } else {
        $collapse[] = $item;
      }
    }

    return new Collection($collapse);
  }

  /**
   * Combine the values of the collection with the keys of another array.
   *
   * @param array $keys An array of keys to combine with the collection's values.
   * @return Collection A new Collection instance with the combined key-value pairs.
   * @throws WPException If the number of items and keys are not equal.
   */
  public function combine(array $keys): Collection 
  {
    if ($this->count() !== count($keys)) {
      throw new WPException("Number of items and keys must be equal.");
    }

    return new Collection(array_combine($keys, $this->collection));
  }

  /**
   * Determine if the collection contains a given value.
   *
   * @param mixed $value The value to search for.
   * @return bool True if the value exists in the collection, false otherwise.
   */
  public function contains(mixed $value): bool 
  {
    return in_array($value, $this->collection, true);
  }

  /**
   * Get the items in the collection that are not present in the given array.
   *
   * @param array $items An array of items to diff against.
   * @return Collection A new Collection instance containing the diffed items.
   */
  public function diff(array $items): Collection 
  {
    return new Collection(array_diff($this->collection, $items));
  }

  /**
   * Run a callback over each item in the collection.
   *
   * @param callable $callable A callback function to execute for each item. The callback should accept the value and key of the item.
   * @return bool True if the callback returns true for all items, false otherwise.
   */
  public function every(callable $callable): bool 
  {
    foreach ($this->collection as $key => $value) {
      if (!$callable($value, $key)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Run a map over each of the items.
   *
   * @param callable $callable The callable to use.
   * @return Collection A new Collection instance containing the mapped items.
   */
  public function map(callable $callable): Collection 
  {
    $mapped = array_map($callable, $this->collection);
    return new Collection($mapped);
  }

  /**
   * Create a new collection.
   *
   * @param array $collection The array to create a collection from.
   * @return Collection A new Collection instance.
   */
  public static function make(array $collection): Collection 
  {
    return new Collection($collection);
  }

  /**
   * Run a flat map over each of the items.
   *
   * @param callable $callable The callable to use.
   * @return Collection A new Collection instance containing the flattened mapped items.
   */
  public function flatMap(callable $callable): Collection 
  {
    $mapped = array_map($callable, $this->collection);
    $collapsed = [];

    foreach ($mapped as $item) {
      if (is_array($item)) {
        $collapsed = array_merge($collapsed, $item);
      } else {
        $collapsed[] = $item;
      }
    }

    return new Collection($collapsed);
  }

  /**
   * Get an item from the collection by key.
   *
   * @param string $key The key of the item to retrieve.
   * @return mixed The value of the item at the given key.
   */
  public function get(string $key): mixed 
  {
    return $this->collection[$key] ?? null;
  }

  /**
   * Get the maximum value of the items in the collection.
   *
   * @return mixed The maximum value in the collection.
   */
  public function max(): mixed 
  {
    return max($this->collection);
  }

  /**
   * Get the minimum value of the items in the collection.
   *
   * @return mixed The minimum value in the collection.
   */
  public function min(): mixed 
  {
    return min($this->collection);
  }

  /**
   * Get the keys of the collection.
   *
   * @return array An array containing the keys of the collection.
   */
  public function keys(): array 
  {
    return array_keys($this->collection);
  }

  /**
   * Filter the collection using the given callback.
   *
   * @param callable $callable A callback function to use for filtering. The callback should accept the value and key of the item.
   * @return Collection A new Collection instance containing the filtered items.
   */
  public function filter(callable $callable): Collection 
  {
    return new Collection(array_filter($this->collection, $callable, ARRAY_FILTER_USE_BOTH));
  }

  /**
   * Key an array by one of the array's values.
   *
   * @param string $key The key to use.
   * @return Collection A new Collection instance with the re-keyed items.
   */
  public function keyBy(string $key): Collection 
  {
    $result = [];

    foreach ($this->collection as $item) {
      if (is_array($item) && array_key_exists($key, $item)) {
        $result[$item[$key]] = $item;
      }
    }

    return new Collection($result);
  }
  
  public function groupBy(string $key): Collection 
  {
    $result = [];
    
    foreach ($this->collection as $item){
      if(!array_key_exists($key, $result)){
        $result[$key] = [];
      }
      
      $result[$key][] = $item;
    }
    
    return new Collection($result);
  }

  /**
   * Prepend an item to the beginning of the collection.
   *
   * @param mixed $value The value to prepend.
   * @param string|null $key The key to prepend. If null, the value is prepended without a key.
   * @return Collection A new Collection instance with the prepended item.
   */
  public function prepend(mixed $value, string $key = null): Collection 
  {
    if ($key === null) {
      $newCollection = [$value, ...$this->collection];
    } else {
      $newCollection = [$key => $value] + $this->collection;
    }

    return new Collection($newCollection);
  }

  /**
   * Pop the last item off of the collection.
   *
   * @return mixed The popped value.
   */
  public function pop(): mixed 
  {
    return array_pop($this->collection);
  }

  /**
   * Pull an item from the collection by its key.
   *
   * @param string $key The key of the item to pull.
   * @return mixed The value of the item at the given key, or null if the key does not exist.
   */
  public function pull(string $key): mixed 
  {
    if (array_key_exists($key, $this->collection)) {
      $value = $this->collection[$key];
      unset($this->collection[$key]);
      return $value;
    }

    return null;
  }

  /**
   * Put an item into the collection by its key.
   *
   * @param string $key The key of the item to put.
   * @param mixed $value The value of the item to put.
   * @return Collection A new Collection instance with the new item.
   */
  public function put(string $key, mixed $value): Collection 
  {
    $this->collection[$key] = $value;
    return new Collection($this->collection);
  }

  /**
   * Reduce the collection to a single value.
   *
   * @param callable $callable A callback function to use for reducing. The callback should accept the carry and item of the item.
   * @param mixed $initial The initial value of the carry.
   * @return mixed The result of the reduction.
   */
  public function reduce(callable $callable, mixed $initial = null): mixed 
  {
    return array_reduce($this->collection, $callable, $initial);
  }

  /**
   * Reject all items in the collection that pass the given truth test.
   *
   * @param callable $callable A callback function to use for rejecting. The callback should accept the value and key of the item.
   * @return Collection A new Collection instance containing the rejected items.
   */
  public function reject(callable $callable): Collection 
  {
    $result = [];

    foreach ($this->collection as $key => $value) {
      if (!$callable($value, $key)) {
        $result[$key] = $value;
      }
    }

    return new Collection($result);
  }

  /**
   * Reverse the order of the items in the collection.
   *
   * @return Collection A new Collection instance with the reversed items.
   */
  public function reverse(): Collection 
  {
    return new Collection(array_reverse($this->collection));
  }

  /**
   * Get a random item from the collection.
   *
   * @return mixed The value of the random item.
   */
  public function random(): mixed 
  {
    if (empty($this->collection)) {
      return null;
    }

    $keys = array_keys($this->collection);
    $randKey = $keys[array_rand($keys)];
    return $this->collection[$randKey];
  }

  /**
   * Zip the collection together with one or more arrays.
   *
   * @param array $items An array of items to zip with the collection.
   * @return Collection A new collection of arrays, where each array contains the nth element from each of the input arrays.
   */
  public function zip(array $items): Collection 
  {
    $result = [];
    $count = min($this->count(), count($items));

    for ($i = 0; $i < $count; $i++) {
      $result[] = [$this->collection[$i], $items[$i]];
    }

    return new Collection($result);
  }

  /**
   * Returns all unique items in the collection.
   *
   * @param string|null $key The key to use for uniqueness. If null, the entire item is used.
   * @return Collection A new Collection instance containing the unique items.
   */
  public function unique(string|null $key = null): Collection 
  {
    if (is_string($key)) {
      $result = [];
      $existingValues = [];

      foreach ($this->collection as $item) {
        if (is_array($item) && array_key_exists($key, $item)) {
          $value = $item[$key];
          if (!in_array($value, $existingValues, true)) {
            $result[] = $item;
            $existingValues[] = $value;
          }
        }
      }

      return new Collection($result);
    }

    return new Collection(array_unique($this->collection, SORT_REGULAR));
  }

  /**
   * Reset the keys on the underlying array.
   *
   * @return array An array with reset keys.
   */
  public function values(): array 
  {
    return array_values($this->collection);
  }

  /**
   * Create a new collection consisting of every n-th element.
   *
   * @param int $limit The number of elements to take.
   * @return Collection A new Collection instance containing the taken items.
   */
  public function take(int $limit): Collection 
  {
    return new Collection(array_slice($this->collection, 0, $limit));
  }

  /**
   * Sorts the items in the collection.
   *
   * @param int $options The sort options.
   * @param bool $descending Whether to sort in descending order.
   * @return Collection A new Collection instance containing the sorted items.
   */
  public function sort(int $options = SORT_REGULAR, bool $descending = false): Collection 
  {
    $collection = $this->collection;

    if ($descending) {
      rsort($collection, $options);
    } else {
      sort($collection, $options);
    }

    return new Collection($collection);
  }

  /**
   * Slice the underlying collection array.
   *
   * @param int $offset The starting position for the slice.
   * @param int|null $length The length of the slice.
   * @return Collection A new Collection instance containing the sliced items.
   */
  public function slice(int $offset, int $length = null): Collection 
  {
    return new Collection(array_slice($this->collection, $offset, $length, true));
  }

  /**
   * Randomly shuffle the items in the collection.
   *
   * @return Collection A new Collection instance with the shuffled items.
   */
  public function shuffle(): Collection 
  {
    $collection = $this->collection;
    shuffle($collection);
    return new Collection($collection);
  }
}