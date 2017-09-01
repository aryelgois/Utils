<?php
/**
 * This Software is part of aryelgois\utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\utils;

use Utils;

/**
 * Encryptation functions
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 * @version 1.0
 */
class Encrypt
{
    /**
     * Replaces some characters in a email by '*'
     *
     * EXAMPLES:
     * - 'foo@example.com'            => '***@example.com'
     * - 'foobar@example.com'         => 'f****r@example.com'
     * - 'verylong.email@example.com' => 've******.***il@example.com'
     *
     * @param sting $email Email to be encrypted
     *
     * @return string
     *
     * @todo Option to encrypt after '@'
     */
    public static function email($email)
    {
        $tmp = explode('@', $email);
        $len = strlen($tmp[0]);
        if ($len <= 3) {
            $tmp[0] = '***';
        } elseif ($len <= 7) {
            $tmp[0] = $tmp[0][0] . str_repeat('*', $len - 2) . $tmp[0][$len - 1];
        } else {
            $tmp[0] = substr($tmp[0], 0, 2) . substr(preg_replace('/[^\.]{1}/', '*', $tmp[0]), 2, -2) . substr($tmp[0], -2);
        }
        return implode('@', $tmp);
    }
    
    /**
     * Offsets every ASCII character from '!' to '~' by 47 bits, wrapping overflow
     *
     * NOTES:
     * - Characters out of the range are kept
     *
     * @param string $str String to be offsetted
     *
     * @return string
     *
     * @todo verify if is binary-safe (for outside range characters)
     */
    public static function rot47($str)
    {
        $result = '';
        foreach (str_split($str) as $char) {
            $ord = ord($char);
            $result .= (namespace\Utils::numberBetween($ord, 33, 126))
                ? chr(namespace\Utils::numberWrap($ord + 47, 33, 126))
                : $char;
        }
        return $result;
    }
    
    /**
     * Applies rot47, url encodes and wraps a string it in an element with
     * 'rot47' class. Useful to hidde data from robots.
     *
     * NOTES:
     * - The actual data is in a data-encrypt attribute, and it is required that
     *   Javascript decode it and replace the inner content
     *
     * @param string $str String to be encrypted
     * @param string $msg Message for <noscript>
     *
     * @return string HTML
     */
    public static function rot47Html($str, $msg = 'ative o Javascript para visualizar')
    {
        return '<span class="rot47" data-encrypt="' . rawurlencode(self::rot47($str)) . '"><noscript><em><small>(' . $msg . ')</small></em></noscript></span>';
    }
}
