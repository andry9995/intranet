<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/08/2019
 * Time: 09:50
 */

namespace AppBundle\Functions;


class StringExtension
{
    /**
     * @param $string
     * @param bool $toUpper
     * @return string
     */
    public static function allFirst($string, $toUpper = true)
    {
        if (strlen($string) == 0) return '';

        $words = explode(' ', $string);
        $letters = '';
        foreach ($words as $value) {
            $letters .= substr($value, 0, 1);
        }

        if ($toUpper) $letters = strtoupper($letters);

        return $letters;
    }
}