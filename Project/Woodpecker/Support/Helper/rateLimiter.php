<?php

namespace Woodpecker\Support;

use Woodpecker\Facade;
use Woodpecker\Support\Session;
use WPException;

class RateLimiter extends Facade 
{
    protected static string $key;
    protected static int $time;
    protected static int $rate;
    protected static array $config;
    
    public static function by(string $key): self
    {
        self::$key = 'rate_limiter_' . $key;
        return new static();
    }
    
    public static function perMinute(int $rate): self
    {
        return self::make(60, $rate);
    }
    
    public static function perHour(int $rate): self
    {
        return self::make(3600, $rate);
    }
    
    public static function perDay(int $rate): self
    {
        return self::make(86400, $rate);
    }
    
    public static function make(int $time = 60, int $rate = 10): self
    {
        if (empty(self::$key)) {
            throw new WPException("Key must be set using the by() method.");
        }

        $configList = require_once(__DIR__ . '/../../../config.php');
        $configList['Session']['timeout'] = 86400;
        
        self::$config = $configList['Session'];
        Session::init($configList['Session']);
        
        self::$time = $time;
        self::$rate = $rate;
        
        return new static();
    }
    
    public static function check(): bool
    {
        Session::init(self::$config);
        
        $data = Session::get(self::$key, ['timestamp' => 0, 'count' => 0]);


          if (time() - $data['timestamp'] > self::$time) {
            self::reset();
            return true;
        }
        
        return $data['count'] < self::$rate;
    }
    
    public static function attempt(): bool
    {
        if (!self::check()) {
            return false;
        }
        
        $data = Session::get(self::$key, ['timestamp' => 0, 'count' => 0]);
        

        if ($data['timestamp'] === 0 || time() - $data['timestamp'] > self::$time) {
            self::reset();
            $data = ['timestamp' => time(), 'count' => 1];
        } else {
            $data['count']++;
        }
        
        Session::set(self::$key, $data);
        return true;
    }
    
    public static function exceeded(): bool
    {
        return !self::check();
    }
    
    public static function reset(): void
    {
        Session::set(self::$key, [
            'timestamp' => time(),
            'count' => 0
        ]);
    }
    
    public static function remaining(): int
    {
        $data = Session::get(self::$key, ['timestamp' => 0, 'count' => 0]);
        
        if (time() - $data['timestamp'] > self::$time) {
            return self::$rate;
        }
        
        return max(0, self::$rate - $data['count']);
    }
    
    public static function retryAfter(): int
    {
        $data = Session::get(self::$key, ['timestamp' => 0, 'count' => 0]);
        return max(0, self::$time - (time() - $data['timestamp']));
    }
}