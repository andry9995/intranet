<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 12/12/2019
 * Time: 12:40
 */

namespace AppBundle\Controller;


class Fonction
{
    public static function initColumns($columns, $excelRows)
    {
        $res = [];
        foreach ($columns as $c) {
            foreach ($excelRows as $r) {
                $res[$c] = array_search(strtolower($c), array_map('strtolower', $excelRows));
            }
        }
        return $res;
    }

    public static function getDateFromExcel($dateString){
        $res = null;
        if($dateString !== null) {
            if (strpos($dateString, '/') !== false) {
                $datetmp = explode('/', $dateString);
                $day = $datetmp[1];
                $month = $datetmp[0];
                $year = $datetmp[2];

                $res = \DateTime::createFromFormat('d/m/Y', $day . '/' . $month . '/' . $year);

            } else {

                $res = \PHPExcel_Shared_Date::ExcelToPHPObject($dateString);
            }
        }

        if($res === false)
            $res =  null;

        return $res;
    }
}