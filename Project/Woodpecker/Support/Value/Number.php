
<?php

namespace Woodpecker\Support;

class Number {
  /**
   * Checks if a number is within a specific range.
   *
   * @param int|float $number The number to check.
   * @param int|float $min The minimum value of the range.
   * @param int|float $max The maximum value of the range.
   * @param bool $inclusive Whether to include the min and max values in the range.
   * @return bool
   */
  public static function inRange($number, $min, $max, $inclusive = true): bool {
    if ($inclusive) {
      return $number >= $min && $number <= $max;
    } else {
      return $number > $min && $number < $max;
    }
  }

  /**
   * Formats a number with grouped thousands.
   *
   * @param int|float $number The number to format.
   * @param int $decimals Sets the number of decimal points.
   * @param string $decimalPoint Sets the separator for the decimal point.
   * @param string $thousandsSeparator Sets the thousands separator.
   * @return string
   */
  public static function format($number, $decimals = 0, $decimalPoint = '.', $thousandsSeparator = ','): string {
    return number_format($number, $decimals, $decimalPoint, $thousandsSeparator);
  }

  /**
   * Returns the absolute value of a number.
   *
   * @param int|float $number The number to get the absolute value from.
   * @return int|float
   */
  public static function absolute($number) {
    return abs($number);
  }

  /**
   * Rounds a number to the nearest integer.
   *
   * @param float $number The number to round.
   * @param int $precision The optional number of decimal digits to round to.
   * @param int $mode The rounding mode (PHP_ROUND_HALF_UP, PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN, PHP_ROUND_HALF_ODD).
   * @return float
   */
  public static function round($number, $precision = 0, $mode = PHP_ROUND_HALF_UP): float {
    return round($number, $precision, $mode);
  }

  /**
   * Ceils a number.
   *
   * @param float $number The number to ceil.
   * @return float
   */
  public static function ceil($number): float {
    return ceil($number);
  }

  /**
   * Floors a number.
   *
   * @param float $number The number to floor.
   * @return float
   */
  public static function floor($number): float
  {
    return floor($number);
  }

  /**
   * Generates a random integer between a minimum and maximum value.
   *
   * @param int $min The minimum value to generate.
   * @param int $max The maximum value to generate.
   * @return int
   */
  public static function randomInt(int $min = 0, int $max = PHP_INT_MAX): int
  {
    return random_int($min, $max);
  }

  /**
   * Checks if a value is numeric.
   *
   * @param mixed $value The value to check.
   * @return bool
   */
  public static function isNumeric($value) {
    return is_numeric($value);
  }

  /**
   * Returns the sum of all numbers in an array.
   *
   * @param array $numbers The array of numbers.
   * @return int|float
   */
  public static function sum(array $numbers) {
    return array_sum($numbers);
  }

  /**
   * Returns the average of all numbers in an array.
   *
   * @param array $numbers The array of numbers.
   * @return float|null
   */
  public static function average(array $numbers): ?float
  {
    $count = count($numbers);
    if ($count === 0) {
      return null;
    }
    return self::sum($numbers) / $count;
  }

  /**
   * Clamps a number between a minimum and maximum value.
   *
   * @param int|float $number The number to clamp.
   * @param int|float $min The minimum value.
   * @param int|float $max The maximum value.
   * @return int|float
   */
  public static function clamp($number, $min, $max) {
    return max($min, min($max, $number));
  }
}
