<?php

namespace Woodpecker;

abstract class Facade
{
    /**
     * Singleton instance
     */
    protected static $instance = null;

    /**
     * Get singleton instance
     */
    protected static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Handle static calls
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::getInstance();
        
        if (!method_exists($instance, $method)) {
            throw new WPException("Method {$method} does not exist in " . static::class);
        }
        
        return $instance->$method(...$args);
    }

    /**
     * Handle dynamic calls for object context
     */
    public function __call(string $method, array $args): mixed
    {
        if (!method_exists($this, $method)) {
            throw new WPException("Method {$method} does not exist in " . static::class);
        }
        
        return $this->$method(...$args);
    }
}