<?php
/**
 * This Software is part of aryelgois\utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\utils;

/**
 * Util functions
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 * @version 0.1
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
