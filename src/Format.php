<?php
/**
 * This Software is part of aryelgois/utils and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\Utils;

/**
 * Formatting functions
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/utils
 */
class Format
{
    /**
     * Days of week in Portuguese
     *
     * @const string[]
     */
    const DAYS_OF_WEEK = [
        'Domingo',
        'Segunda',
        'Terça',
        'Quarta',
        'Quinta',
        'Sexta',
        'Sábado'
    ];

    /**
     * Months in Portuguese
     *
     * @const string[]
     */
    const MONTHS = [
        'Janeiro',
        'Fevereiro',
        'Março',
        'Abril',
        'Maio',
        'Junho',
        'Julho',
        'Agosto',
        'Setembro',
        'Outubro',
        'Novembro',
        'Dezembro'
    ];

    /**
     * Formats Brazilian CNPJ
     *
     * @param string $cnpj 14 digits
     *
     * @return string
     */
    public static function cnpj($cnpj)
    {
        return substr($cnpj, 0, 2) . '.'
             . substr($cnpj, 2, 3) . '.'
             . substr($cnpj, 5, 3) . '/'
             . substr($cnpj, 8, 4) . '-'
             . substr($cnpj, 12, 2);
    }

    /**
     * Formats Brazilian CPF
     *
     * @param string $cpf 11 digits
     *
     * @return string
     */
    public static function cpf($cpf)
    {
        return substr($cpf, 0, 3) . '.'
             . substr($cpf, 3, 3) . '.'
             . substr($cpf, 6, 3) . '-'
             . substr($cpf, 9, 2);
    }

    /**
     * Formats any date into date('j \d\e F \d\e Y')
     *
     * NOTES:
     * - The first day of the month is '1º'
     * - The month is in Portuguese
     *
     * @param string $date Any English textual datetime description
     *
     * @return string
     *
     * @see http://php.net/manual/en/datetime.formats.date.php
     */
    public static function date($date)
    {
        $d = explode('/', date('j/n/Y', strtotime($date)));
        if ($d[0] == '1') {
            $d[0] .= 'º';
        }
        $d[1] = self::MONTHS[$d[1] - 1];
        return implode(' de ', $d);
    }

    /**
     * Formats two dates to represent a range
     *
     * EXAMPLES:
     * - ('2017-08-30', '2017-08-15')        => 'Datas: 15/08/2017 a 30/08/2017'
     * - ('2017-07-24', '2017-07-25', false) => '24/07/2017 e 25/07/2017'
     * - ('2017-05-01', '2017-05-01')        => 'Data: 01/05/2017'
     *
     * @param string  $date0  Any English textual datetime description
     * @param string  $date1  Ditto
     * @param boolean $prefix If the return should have a prefix
     *
     * @return string
     *
     * @see http://php.net/manual/en/datetime.formats.date.php
     */
    public static function dateRange($date0, $date1, $prefix = true)
    {
        $date0 = strtotime('Today 00:00:00', strtotime($date0));
        $date1 = strtotime('Today 00:00:00', strtotime($date1));
        $range = [date('d/m/Y', min($date0, $date1))];
        if ($date0 != $date1) {
            $range[] = date('d/m/Y', max($date0, $date1));
        }
        $prefix = ($prefix ? 'Data' . ($date0 != $date1 ? 's' : '') . ': ' : '');
        $glue = (strtotime('+ 1 day', $date0) == $date1 ? ' e ' : ' a ');
        return $prefix . implode($glue, $range);
    }

    /**
     * Formats Brazilian Document (CPF or CNPJ)
     *
     * @param string  $doc     @see Validation::document()
     * @param boolean $prepend If should prepend the document name
     *
     * @return string Formatted document
     * @return string Unformatted document if it is invalid
     */
    public static function document($doc, $prepend = false)
    {
        $document = Validation::document($doc);
        if ($document['type'] == 1) {
            return ($prepend ? 'CPF: ' : '') . self::cpf($document['valid']);
        } elseif ($document['type'] == 2) {
            return ($prepend ? 'CNPJ: ' : '') . self::cnpj($document['valid']);
        }
        return $document['valid'] ?? $doc;
    }

    /**
     * Formats money value based in a country code
     *
     * @param float  $val     Value to be formatted
     * @param string $country Country whose money conventions will be used
     *
     * @return string
     */
    public static function money($val, $country = 'BR')
    {
        switch ($country) {
            case 'US':
                $arr = ['US$', ',', '.'];
                break;
            case 'BR':
            default:
                $arr = ['R$', '.', ','];
        }
        return ($arr[0] . ' ' . number_format($val, 2, $arr[2], $arr[1]));
    }

    /**
     * Human readable filesize()
     *
     * @author rommel at rommelsantor dot com
     * @link http://php.net/manual/en/function.filesize.php#106569
     *
     * @param string  $filename Path to the file
     * @param integer $decimals Amount of decimals
     *
     * @return string
     */
    public static function filesize($filename, $decimals = 2)
    {
        $bytes = filesize($filename);
        $factor = floor((strlen($bytes) - 1) / 3);
        $size = 'BKMGTP';
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    /**
     * Implodes an array with ',' and 'and' before last element
     *
     * @param array  $pieces Array to be imploded
     * @param string $last   String to join the last item
     * @param string $glue   String to join the other items
     *
     * @return string
     */
    public static function naturalLanguageJoin(
        array $arr,
        string $last = null,
        string $glue = null
    ) {
        $last_item = array_pop($arr);
        if ($arr) {
            return implode($glue ?? ', ', $arr)
                . ' ' . ($last ?? 'and') . ' '
                . $last_item;
        }
        return $last_item;
    }

    /**
     * Indents a multiline string, adding spaces after every "\n"
     *
     * NOTES:
     * - It does not indent the first line
     *
     * @param string  $str    Text to be indented
     * @param integer $indent Amount of indents to be added
     * @param integer $tab    Size of one indent
     *
     * @return string
     */
    public static function stringIndent($str, $indent, $tab = 4)
    {
        return str_replace("\n", "\n" . str_repeat(' ', $indent * $tab), $str);
    }

    /**
     * Replaces the first occurence in a string
     *
     * @param string $search  Substring to be searched
     * @param string $replace Substring to replace
     * @param string $subject String where replacement will happen
     *
     * @return string
     */
    public static function stringReplaceFirst($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);
        if ($pos !== false)
            return substr_replace($subject, $replace, $pos, strlen($search));
        return $subject;
    }

    /**
     * Replaces keys of an array by its values in a string
     *
     * NOTES:
     * - Be careful with infinit loops
     *
     * @param mixed   $subject Where replacement will happen
     * @param mixed[] $map     A map of search => replace to be performed
     * @param boolean $repeat  If it should repeat until there is nothing to replace
     *
     * @return string
     *
     * @see http://php.net/manual/en/function.str-replace.php
     */
    public static function multiReplace($map, $subject, $repeat = false)
    {
        do {
            $count = $c = 0;
            foreach ($map as $search => $replace) {
                $subject = str_replace($search, $replace, $subject, $c);
                $count += $c;
            }
        } while ($repeat && $count > 0);
        return $subject;
    }
}
