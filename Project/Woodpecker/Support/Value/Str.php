<?php
namespace Woodpecker\Support;

class Str {
  
  protected string $subject = '';
  
  public function __construct(string $subject) {
    $this->subject = $subject;
  }
  
  /**
   * Returns the portion of string after the first occurrence of a given value.
   *
   * @param string $search The value to search for.
   * @return string
   */
  public function after(string $search): string {
    return $search === '' ? $this->subject : array_reverse(explode($search, $this->subject, 2))[0];
  }
    
  /**
   * Returns the portion of string after the last occurrence of a given value.
   *
   * @param string $search The value to search for.
   * @return string
   */
  public function afterLast(string $search): string {
    if ($search === '') {
      return $this->subject;
    }

    $position = strrpos($this->subject, (string) $search);

    if ($position === false) {
      return $this->subject;
    }

    return substr($this->subject, $position + strlen($search));
  }
  
  /**
   * Returns the portion of string before the first occurrence of a given value.
   *
   * @param string $search The value to search for.
   * @return string
   */
  public function before(string $search): string {
    if ($search === '') {
      return $this->subject;
    }

    $result = strstr($this->subject, (string) $search, true);

    return $result === false ? $this->subject : $result;
  }
  
  /**
   * Returns a substring from the string.
   *
   * @param int $start The position where to start extracting.
   * @param int|null $length Maximum number of characters to extract.
   * @param string $encoding Character encoding.
   * @return string
   */
  public function substr(int $start, int|null $length = null, string $encoding = 'UTF-8'): string {
    return mb_substr($this->subject, $start, $length, $encoding);
  }

  /**
   * Returns the portion of string before the last occurrence of a given value.
   *
   * @param string $search The value to search for.
   * @return string
   */
  public function beforeLast(string $search): string {
    if ($search === '') {
      return $this->subject;
    }

    $pos = mb_strrpos($this->subject, $search);

    if ($pos === false) {
      return $this->subject;
    }

    return $this->substr(0, $pos);
  }
    
  /**
   * Get the portion between two strings.
   *
   * @param string $from The value you want to start at.
   * @param string $to The value you want to end at.
   * @return string
   */
  public function between(string $from, string $to): string {
    if ($from === '' || $to === '') {
      return $this->subject;
    }

    return $this->beforeLast($this->after($from), $to);
  }
  
  /**
   * Get the portion between two strings, from the first occurrence.
   *
   * @param string $from The value you want to start at.
   * @param string $to The value you want to end at.
   * @return string
   */
  public function betweenFirst(string $from, string $to): string {
    if ($from === '' || $to === '') {
      return $this->subject;
    }

    return $this->before($this->after($from), $to);
  }
  
  /**
   * Returns the character at a specific index.
   *
   * @param int $index The index of the character to retrieve.
   * @return string|false
   */
  public function charAt(int $index): mixed {
    $length = mb_strlen($this->subject);

    if ($index < 0 ? $index < -$length : $index > $length - 1) {
      return false;
    }

    return mb_substr($this->subject, $index, 1);
  }
  
  /**
   * Determine if a given string contains a given substring.
   *
   * @param string|array $needles The string(s) to search for.
   * @param bool $ignoreCase Whether to ignore case.
   * @return bool
   */
  public function contains(string|array $needles, bool $ignoreCase = false): bool {
    $haystack = $this->subject;

    if ($ignoreCase) {
      $haystack = mb_strtolower($haystack);
    }

    if (!is_iterable($needles)) {
      $needles = (array) $needles;
    }

    foreach ($needles as $needle) {
      if ($ignoreCase) {
        $needle = mb_strtolower($needle);
      }

      if ($needle !== '' && str_contains($haystack, $needle)) {
        return true;
      }
    }

    return false;
  }
    
  /**
   * Determine if a given string contains all array values.
   *
   * @param array $needles The values to search for.
   * @param bool $ignoreCase Whether to ignore case.
   * @return bool
   */
  public function containsAll(array $needles, bool $ignoreCase = false): bool {
    foreach ($needles as $needle) {
      if (!$this->contains($needle, $ignoreCase)) {
        return false;
      }
    }

    return true;
  }
    
  /**
   * Returns the length of the given string.
   *
   * @param string|null $encoding Character encoding.
   * @return int
   */
  public function length(string|null $encoding = null): int {
    return mb_strlen($this->subject, $encoding);
  }
    
  /**
   * Limits the number of characters in a string.
   *
   * @param int $limit The number of characters to limit to.
   * @param string $end The end string to append to the limited string.
   * @return string
   */
  public function limit(int $limit = 100, string $end = '...'): string {
    if (mb_strwidth($this->subject, 'UTF-8') <= $limit) {
      return $this->subject;
    }

    return rtrim(mb_strimwidth($this->subject, 0, $limit, '', 'UTF-8')) . $end;
  }
    
  /**
   * Repeats a string.
   *
   * @param int $times The number of times to repeat the string.
   * @return string
   */
  public function repeat(int $times): string {
    return str_repeat($this->subject, $times);
  }
    
  /**
   * Reverses a string.
   *
   * @return string
   */
  public function reverse(): string {
    return implode(array_reverse(mb_str_split($this->subject)));
  }
    
  /**
   * Replace text within a portion of a string.
   *
   * @param string $replace The replacement string.
   * @param int $offset The starting position for the replacement.
   * @param int|null $length The number of characters to replace.
   * @return string
   */
  public function substrReplace(string $replace, int $offset = 0, int|null $length = null): string {
    if ($length === null) {
      $length = strlen($this->subject);
    }
    
    return substr_replace($this->subject, $replace, $offset, $length);
  }
}