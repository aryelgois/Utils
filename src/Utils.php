<?php
/**
 * This Software is part of aryelgois\Utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils;

/**
 * Util functions
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class Utils
{
    /*
     * Debugging
     * =========================================================================
     */

    /**
     * Exports a value, wrapping in <pre><code>, and echo.
     *
     * @param mixed $val the value to be exported
     */
    public static function varHtml($val)
    {
        echo "<pre><code>\n" . var_export($val, true) . "\n</code></pre>\n";
    }

    /*
     * Numbers
     * =========================================================================
     */

    /**
     * Compares if a value is between a minimum and maximum value.
     *
     * @param number $val The value tested
     * @param number $min The minimun value
     * @param number $max The maximum value
     * @param string $opt Tells if $min and $max are inclusives or exclusives:
     *                    'EE' => Both exclusives
     *                    'EI' => Min exclusive, Max inclusive
     *                    'IE' => Min inclusive, Max exclusive
     *                    'II' => Both inclusives (default)
     *
     * @return boolean
     */
    public static function numberBetween($val, $min, $max, $opt = 'II')
    {
        switch ($opt) {
            case 'EE':
                return ($val > $min && $val < $max);
                break;
            case 'EI':
                return ($val > $min && $val <= $max);
                break;
            case 'IE':
                return ($val >= $min && $val < $max);
                break;
            case 'II':
            default:
                return ($val >= $min && $val <= $max);
        }
    }

    /**
     * Limits a value to a minimum and maximum boundaries.
     *
     * @param number $val The value tested
     * @param number $min The minimun value
     * @param number $max The maximum value
     *
     * @return number $val|$min|$max
     */
    public static function numberLimit($val, $min, $max)
    {
        return ($val > $min ? ($val < $max ? $val : $max) : $min);
    }

    /**
     * Limits a value to a minimum and maximum boundaries, wrapping the overflow.
     *
     * EXAMPLE:
     * - numberWrap(120, 0, 100) would return 19 because it is the overflow in
     *   the range 0-100.
     *
     * @param number $val The value tested
     * @param number $min The minimun value
     * @param number $max The maximum value
     *
     * @return number
     */
    public static function numberWrap($val, $min, $max)
    {
        while (!self::numberBetween($val, $min, $max)) {
            if ($val < $min) {
                $val += $max - $min + 1; // +1 because $min
            } elseif ($val > $max) {     // and $max are
                $val -= $max - $min + 1; // inclusives
            }
        }
        return $val;
    }

    /**
     * Checks if input contains only digits (numbers)
     *
     * @author Tim Boormans <info@directwebsolutions.nl>
     * @link   http://php.net/manual/en/function.ctype-digit.php#108712
     * @see    http://php.net/manual/en/function.ctype-digit.php#118098
     *
     * @param mixed $digit
     *
     * @return boolean
     */
    public static function isDigit($digit)
    {
        if (is_int($digit) && $digit >= 0) {
            return true;
        } elseif (is_string($digit)) {
            return ctype_digit($digit);
        } else {
            // booleans, floats and others
            return false;
        }
    }

    /*
     * Array
     * =========================================================================
     */

    /**
     * Returns only array entries NOT listed in a blacklist
     *
     * @param array $array     Original array to operate on
     * @param array $blacklist Keys not allowed in the array
     *
     * @return array
     */
    public static function arrayBlacklist($array, $blacklist)
    {
        return array_diff_key($array, array_flip($blacklist));
    }

    /**
     * Runs a callback in a set of arrays
     *
     * It passes each array as the first argument, followed by all the others,
     * then it merges the results.
     *
     * @param callable $callback  A function to receive all arrays
     * @param mixed[]  ...$arrays Multiple arrays
     *
     * @return mixed[]
     */
    protected static function arrayCallUserFuncEachFirst(
        callable $callback,
        ...$arrays
    ) {
        $diffs = [];
        foreach ($arrays as $key => $array) {
            $others = $arrays;
            unset($others[$key]);
            $diffs[] = call_user_func($callback, $array, ...$others);
        }
        return array_merge(...$diffs);
    }

    /**
     * Tests if an array has an associative key (not integer)
     *
     * NOTE:
     * - Even if the keys are non sequential numeric, the array is not
     *   considered 'associative'
     *
     * @param array $array An array whose keys will be tested
     *
     * @return boolean
     */
    public static function arrayIsAssoc($array)
    {
        foreach (array_keys($array) as $key) {
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Merges two or more arrays in a zipper style:
     *
     * If array length is not equal, those missing will be skipped.
     * In the end, all values will be in returned array.
     *
     * It is possible to pass a string, whose characters will be treated as
     * single values, so the return would be an array of characters.
     *
     * Examples:
     * - [$a[0], $b[0], $a[1], $b[1], ...]
     * - [$a[0], $b[0], $c[0], $a[1], $b[1], $c[1], ...]
     * - [$a[0], $b[0], $c[0], $a[1], $c[1]]             // b is shorter
     * - [$a[0], $b[0], $c[0], $a[1]]                    // a is longer
     *
     * @param mixed[] $arrays Arrays or Strings (multiple parameters)
     *
     * @return array
     */
    public static function arrayInterpolate(...$arrays)
    {
        if (count($arrays) == 0) {
            return [];
        }
        $len = $result = [];

        // Determine longest array and cache lengths
        foreach ($arrays as $array) {
            $len[] = (is_string($array))
                ? strlen($array)
                : count($array);
        }
        $max = max($len);

        // Interpolate values
        for ($i = 0; $i < $max; $i++) {
            foreach ($arrays as $current => $array) {
                if ($len[$current] >= $i + 1) {
                    $result[] = $array[$i];
                }
            }
        }
        return $result;
    }

    /**
     * Gets the value from a nested array, following a list of keys
     *
     * @author ymakux
     * @see    https://stackoverflow.com/a/44189105
     *
     * @param array        $array Array manipulated
     * @param array|string $path  List of keys to follow
     * @param string       $glue  Glue to explode the $path
     *
     * @return mixed
     */
    public static function arrayPathGet(array &$array, $path, $glue='.')
    {
        if (!is_array($path)) {
            $path = explode($glue, $path);
        }

        $ref = &$array;

        foreach ((array) $path as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            } else {
                return null;
            }
        }

        return $ref;
    }

    /**
     * Sets the value in a nested array, following a list of keys
     *
     * @author ymakux
     * @see    https://stackoverflow.com/a/44189105
     *
     * @param array        $array Array manipulated
     * @param array|string $path  List of keys to follow
     * @param mixed        $value New value to be set
     * @param string       $glue  Glue to explode the $path
     */
    public static function arrayPathSet(array &$array, $path, $value, $glue='.')
    {
        if (!is_array($path)) {
            $path = explode($glue, (string) $path);
        }

        $ref = &$array;

        foreach ($path as $parent) {
            if (isset($ref) && !is_array($ref)) {
                $ref = array();
            }
            $ref = &$ref[$parent];
        }

        $ref = $value;
    }

    /**
     * Unsets a key value from a nested array, following a list of keys
     *
     * @author ymakux
     * @see    https://stackoverflow.com/a/44189105
     *
     * @param array        $array Array manipulated
     * @param array|string $path  List of keys to follow
     * @param string       $glue  Glue to explode the $path
     */
    public static function arrayPathUnset(&$array, $path, $glue='.')
    {
        if (!is_array($path)) {
            $path = explode($glue, $path);
        }

        $key = array_shift($path);

        if (empty($path)) {
            unset($array[$key]);
        } else {
            self::arrayPathUnset($array[$key], $path);
        }
    }

    /**
     * Computes the unique values in a set of arrays
     *
     * @param mixed[] ...$arrays Multiple arrays to compare
     *
     * @return mixed[]
     */
    public static function arrayUniqueDiff(...$arrays)
    {
        return self::arrayCallUserFuncEachFirst('array_diff', ...$arrays);
    }

    /**
     * Computes the unique values in a set of arrays, based on its keys
     *
     * @param mixed[] ...$arrays Multiple arrays to compare
     *
     * @return mixed[]
     */
    public static function arrayUniqueDiffKey(...$arrays)
    {
        return self::arrayCallUserFuncEachFirst('array_diff_key', ...$arrays);
    }

    /**
     * Returns only array entries listed in a whitelist
     *
     * @param array $array     Original array to operate on
     * @param array $whitelist Keys allowed in the array
     *
     * @return array
     */
    public static function arrayWhitelist($array, $whitelist)
    {
        return array_intersect_key($array, array_flip($whitelist));
    }

    /*
     * Recursive
     * =========================================================================
     */

    /**
     * Recursively searches an array for a value.
     *
     * @author jwueller
     * @link https://stackoverflow.com/posts/4128377/revisions
     *
     * @param mixed   $needle   The value to be searched
     * @param array   $haystack The array to be searched in
     * @param boolean $strict   If the comparision is strict '===' or not '=='
     *
     * @return boolean If $needle is found
     */
    public static function recInArray($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::recInArray($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recursively sorts the keys of an array.
     *
     * @param array   &$arr The array to be sorted
     * @param integer $flag Sort flag
     *
     * @return boolean true on success or false on failure
     *
     * @see http://php.net/manual/en/function.sort.php
     */
    public static function recKsort(&$arr, $flag = SORT_REGULAR)
    {
        if (!is_array($arr)) {
            return false;
        }
        ksort($arr, $flag);
        foreach ($arr as &$arr1) {
            self::recKsort($arr1, $flag);
        }
        return true;
    }

    /**
     * Recursively lists files and directories inside the specific path.
     *
     * @param string $directory The directory that will be scanned
     *
     * @return array "path" => array|'file'
     */
    public static function recScandir($directory)
    {
        $arr = [];
        foreach (scandir($directory) as $f) {
            if ($f == '.' || $f == '..') {
                continue;
            } elseif (is_dir($directory . '/' . $f)) {
                $arr[$f] = self::recScandir($directory . '/' . $f);
            } else {
                $arr[$f] = 'file';
            }
        }
        return $arr;
    }

    /*
     * Miscellaneous
     * =========================================================================
     */

    /**
     * Finds the Browser name inside the user agent.
     *
     * @return string Browser name|'Other'
     */
    public static function getBrowserName()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
            return 'Opera';
        } elseif (strpos($user_agent, 'Edge')) {
            return 'Edge';
        } elseif (strpos($user_agent, 'Chrome')) {
            return 'Chrome';
        } elseif (strpos($user_agent, 'Safari')) {
            return 'Safari';
        } elseif (strpos($user_agent, 'Firefox')) {
            return 'Firefox';
        } elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
            return 'Internet Explorer';
        }
        return 'Other';
    }
}
