<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

/**
 * Utils for arrays manager
 */
final class Arr
{

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is array';
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  iterable  $array
     * @param  string  $prepend
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
    
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array  $array
     * @param  string|int|null  $key
     * @param  mixed  $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
    
    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array  $array
     * @param  int  $options
     * @param  bool  $descending
     * @return array
     */
    public static function sortRecursive($array, $options = SORT_REGULAR, $descending = false)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value, $options, $descending);
            }
        }

        if (! array_is_list($array)) {
            $descending
                    ? krsort($array, $options)
                    : ksort($array, $options);
        } else {
            $descending
                    ? rsort($array, $options)
                    : sort($array, $options);
        }

        return $array;
    } /**
    * Check if an item or items exist in an array using "dot" notation.
    *
    * @param  \ArrayAccess|array  $array
    * @param  string|array  $keys
    * @return bool
    */
   public static function has($array, $keys)
   {
       $keys = (array) $keys;

       if (! $array || $keys === []) {
           return false;
       }

       foreach ($keys as $key) {
           $subKeyArray = $array;

           if (static::exists($array, $key)) {
               continue;
           }

           foreach (explode('.', $key) as $segment) {
               if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                   $subKeyArray = $subKeyArray[$segment];
               } else {
                   return false;
               }
           }
       }

       return true;
   }
   
   /**
     * Determine if the given key exists in the provided array.
     *
     * @param  array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if (is_float($key)) {
            $key = (string) $key;
        }

        return array_key_exists($key, $array);
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value);
    }
}
