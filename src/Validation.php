<?php
/**
 * This Software is part of aryelgois/utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils;

/**
 * Validation functions
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class Validation
{
    /*
     * Clean up
     * =========================================================================
     */

    /**
     * Sanitizes a string
     *
     * @param string $str A string to be sanitized
     *
     * @return string
     */
    public static function sanitizeInput($str)
    {
        return htmlspecialchars(stripslashes(trim($str)));
    }

    /**
     * Sanitizes all strings of an array
     *
     * @param mixed[] &$arr Only strings are sanitized
     */
    public static function sanitizeArray(&$arr)
    {
        foreach ($arr as $k => $v) {
            if (is_string($v)) {
                $arr[$k] = self::sanitizeInput($v);
            }
        }
    }

    /*
     * Specific cases
     * =========================================================================
     */

    /**
     * Validates address numbers
     *
     * @param string $address Format '00000-w', can omit punctuation ('w' is a leter or digit)
     *                        Or the literal 's/n': address without number
     *
     * @return string validated or false on failure
     */
    public static function addressNumber($address)
    {
        if (preg_match('/^\d{1,5}([\s\-]?[A-Z0-9]+|)$/i', $address)) {
            return strtoupper($address);
        } elseif (preg_match('/^s\.?[\s\/]?n\.?$/i', $address)) {
            return 's/n';
        }
        return false;
    }

    /**
     * Validates Brazilian CEP
     *
     * @param string $zipcode Format '00.000-000', can omit punctuation
     *
     * @return string validated or false on failure
     */
    public static function cep($zipcode)
    {
        if (preg_match("/^(\d{2})[\s\.]?(\d{3})[\s\-]?(\d{3})$/", $zipcode, $matches)) {
            return $matches[1] . $matches[2] . '-' . $matches[3];
        }
        return false;
    }

    /**
     * Validates Brazilian CNPJ
     *
     * @param string $cnpj Up to 14 digits, anything else is discarded
     *
     * @return string validated (only numbers)
     * @return false  if invalid
     */
    public static function cnpj($cnpj)
    {
        // Extract numbers
        $cnpj = preg_replace('/[^\d]/', '', $cnpj);

        // Check amount of numbers
        if (strlen($cnpj) > 14) {
            return false;
        }
        $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);

        // Check for same digit sequence
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calculate check digits
        $cd = [11 - self::mod11($cnpj)];
        if ($cd[0] >= 10) {
            $cd[0] = 0;
        }
        $cd[] = 11 - self::mod11($cnpj . $cd[0]);
        if ($cd[1] >= 10) {
            $cd[1] = 0;
        }

        // Verify
        if ($cd[0] == $cnpj[12] && $cd[1] == $cnpj[13]) {
            return $cnpj;
        }
        return false;
    }

    /**
     * Validate Brazilian CPF
     *
     * @author rafael-neri (modified)
     * @link https://gist.github.com/rafael-neri/ab3e58803a08cb4def059fce4e3c0e40
     *
     * @param string $cpf Up to 11 digits, anything else is discarded
     *
     * @return string validated (only numbers) or false if invalid
     */
    public static function cpf($cpf)
    {
        // Extract numbers
        $cpf = preg_replace('/[^\d]/', '', $cpf);

        // Check amount of numbers
        if (strlen($cpf) > 11) {
            return false;
        }
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Check for same digit sequence
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calculate check digits
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf{$c} != $d) {
                return false;
            }
        }

        return $cpf;
    }

    /**
     * Validates a date
     *
     * @param string $date   Date string
     * @param string $format Format to be tested
     *
     * @return boolean
     */
    public static function date($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return ($d && $d->format($format) == $date);
    }

    /**
     * Validates a date time
     *
     * This method is an alias to date(), for shorter and more descriptive code.
     *
     * @param string $datetime Date time string
     * @param string $format   Format to be tested
     *
     * @return boolean
     */
    public static function dateTime($datetime, $format = 'Y-m-d H:i:s')
    {
        return self::date($datetime, $format);
    }

    /**
     * Validates Brazilian Document (CPF or CNPJ)
     *
     * @param string $doc Document to be validated (11 or 14 digits, other
     *                    characters are discarded)
     *
     * @return mixed[] With keys 'type' and 'valid'
     * @return false   If document is invalid
     */
    public static function document($doc)
    {
        $type = 1;
        $valid = self::cpf($doc);
        if ($valid == false) {
            $type = 2;
            $valid = self::cnpj($doc);
        }
        if ($valid == false) {
            return false;
        }
        return ['type' => $type, 'valid' => $valid];
    }

    /**
     * Validates a telephone/cell phone number
     *
     * @param string $tel Maximum format '+00 (000) 90000-0000'
     *
     * @return string validated or false on failure
     */
    public static function tel($tel)
    {
        if (preg_match("/^(\+\d{2}|)\s?(\(?0?\d{2}\)?|)\s?(9?)\s?(\d{4})[\s\-]?(\d{4})$/", $tel, $matches)) {
            return ($matches[1] !== '' ? $matches[1] . ' ' : '')
                 . ($matches[2] !== '' ? implode('', array_diff(str_split($matches[2]), ['(',')'])) . ' ' : '')
                 . $matches[3]. $matches[4] . '-' . $matches[5];
        }
        return false;
    }

    /*
     * Helper
     * =========================================================================
     */

    /**
     * Luhn algorithm (modulus 10)
     *
     * @param mixed $number Numeric value whose check digit will be calculated
     *
     * @return integer
     *
     * @see http://en.wikipedia.org/wiki/Luhn_algorithm
     */
    public static function mod10($number)
    {
        $checksum = '';
        foreach (str_split(strrev((string) $number)) as $i => $d) {
            $checksum .= ($i % 2 == 0) ? $d * 2 : $d;
        }
        return array_sum(str_split($checksum)) * 9 % 10;
    }

    /**
     * Calculates modulus 11 to generate a check digit
     *
     * NOTES:
     * - May require post validation for numbers 0, 1 and 10.
     *
     * @param mixed   $number Numeric value whose check digit will be calculated
     * @param integer $base   Maximum multiplication value
     *
     * @return integer
     */
    public static function mod11($number, $base = 9)
    {
        return (self::mod11Pre($number, $base) % 11);
    }

    /**
     * Calculates modulus 11 but do not apply the modulus
     *
     * Useful when some calculation is required before '% 11'
     *
     * @param mixed   $number Numeric value whose check digit will be calculated
     * @param integer $base   Maximum multiplication value
     *
     * @return integer
     */
    public static function mod11Pre($number, $base = 9)
    {
        $checksum = 0;
        $factor = 2;
        foreach (str_split(strrev((string) $number)) as $d) {
            $checksum += $d * $factor;
            if (++$factor > $base) {
                $factor = 2;
            }
        }
        return $checksum;
    }
}
