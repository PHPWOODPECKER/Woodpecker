<?php

namespace Woodpecker\Support;

class Timer 
{
    // Static array to store all active timers
    private static array $timers = [];
    // Static boolean to control the tick loop
    private static bool $stoper = false;
    
    /**
     * Creates a one-time timer that executes a callable after a specified time.
     *
     * @param callable $callable The function to execute.
     * @param int $time The delay in milliseconds before execution.
     * @param mixed ...$params Additional parameters to pass to the callable.
     * @return string A unique ID for the timer.
     */
    public static function Timer(callable $callable, int $time, ...$params): string 
    {
        self::$stoper = false;
        $id = uniqid('Timer_');
        self::$timers[] = [
            'id' => $id,
            'callable' => $callable,
            'start' => microtime(true) * 1000,
            'time' => $time,
            'params' => $params,
            'type' => 'Timer'
        ];
        return $id;
    }

    /**
     * Creates a daily timer that executes a callable repeatedly at a specified interval.
     *
     * @param callable $callable The function to execute.
     * @param int $time The interval in milliseconds between executions.
     * @param mixed ...$params Additional parameters to pass to the callable.
     * @return string A unique ID for the timer.
     */
    public static function dailyTimer(callable $callable, int $time, ...$params): string
    {
        self::$stoper = false;
        $id = uniqid('dailyTimer_');
        self::$timers[] = [
            'id' => $id,
            'callable' => $callable,
            'time' => $time,
            'start' => microtime(true) * 1000, 
            'params' => $params,
            'type' => 'dailyTimer'
        ];
        return $id;
    }
  
    /**
     * Continuously checks and executes timers based on their type and schedule.
     */
    public static function tick(): void
    {
      if(empty(self::$timers)){
        return;
      }
        while (!self::$stoper) {
            if (empty(self::$timers)) {
                self::$stoper = true; 
                return;
            }
            
            foreach (self::$timers as $id => &$timer) {
                $now = microtime(true) * 1000;
                
                if ($timer['type'] === 'Timer') {
                    if ($now >= ($timer['time'] + $timer['start'])) {
                        call_user_func_array($timer['callable'], $timer['params']);
                        unset(self::$timers[$id]);
                    }
                } 
                
                elseif ($timer['type'] === 'dailyTimer') {
                    if ($now >= ($timer['time'] + $timer['start'])) {
                        call_user_func_array($timer['callable'], $timer['params']);
                        $timer['start'] = microtime(true) * 1000;
                    }
                }
            }
        }
    }
  
    /**
     * Stops the tick loop.
     */
    public static function stopTick(): void
    {
        self::$stoper = true;
    }
  
    /**
     * Stops a specific daily timer by its ID.
     *
     * @param string $id The unique ID of the timer to stop.
     */
    public static function stopDailyTimer(string $id): void
    {
        foreach (self::$timers as $key => $timer) {
            if ($timer['id'] === $id) {
                unset(self::$timers[$key]);
                break;
            }
        }
    }
}
