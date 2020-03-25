<?php
    namespace AppBundle\Controller;

    use \PHPExcel_Style_Fill;
    
    class Boost
    {
        /**
         * crypter
         * @param $str
         * @return string
         */
        public static function boost($str)
        {
            $cle = Boost::getUuid();
            $cle64 = base64_encode($cle);
            $izy = $cle.$str.$cle64;
            $cle64_1 = base64_encode($izy);
            return $cle64_1;
        }

        /**
         * @param $str
         * @param $controler
         * @return bool|mixed
         */
        public static function deboost($str,$controler)
        {
            $cle64_1 = base64_decode($str);
            $cle = substr($cle64_1,0,13);
            $queue = base64_encode($cle);
            $lenth = strlen($queue);
            $rambony = substr($cle64_1,-$lenth);

            if($rambony != $queue)
            {
                $controler->get('security.context')->setToken(null);
                $controler->get('request')->getSession()->invalidate();
                return false;
            }

            $result = str_replace($rambony,'',$cle64_1);
            $result = str_replace($cle,'',$result);
            return $result;
        }

        /**
         * @param int $len
         * @return string
         */
        public static function getUuid($len = 13)
        {
//            $string = "";
//            $chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_";
//            srand((double)microtime()*1000000);
//            for($i=0; $i<$len; $i++) $string .= $chaine[rand()%strlen($chaine)];
//            return $string;
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_';
            $pass = array();
            $characters_length = strlen($characters) - 1;
            for ($i = 0; $i < $len; $i++) {
                $n = rand(0, $characters_length);
                $pass[] = $characters[$n];
            }
            return implode($pass);
        }

        public static function getGUID(){
            if (function_exists('com_create_guid')){
                return com_create_guid();
            }else{
                mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
                $charid = strtoupper(md5(uniqid(rand(), true)));
                $hyphen = chr(45);// "-"
                $uuid = chr(123)// "{"
                    .substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12)
                    .chr(125);// "}"
                return $uuid;
            }
        }

        //mois chiffre to lettre
        public static function getMoisLettre($mois,$abreviation = true,$majuscule = true)
        {
            $lettre = '';
            if($mois == 1) $lettre = ($abreviation) ? 'JAN' : 'JANVIER';
            if($mois == 2) $lettre = ($abreviation) ? 'FEV' : 'FEVRIER';
            if($mois == 3) $lettre = ($abreviation) ? 'MAR' : 'MARS';
            if($mois == 4) $lettre = ($abreviation) ? 'AVR' : 'AVRIL';
            if($mois == 5) $lettre = ($abreviation) ? 'MAI' : 'MAI';
            if($mois == 6) $lettre = ($abreviation) ? 'JUI' : 'JUIN';
            if($mois == 7) $lettre = ($abreviation) ? 'JUL' : 'JUILLET';
            if($mois == 8) $lettre = ($abreviation) ? 'AOU' : 'AOUT';
            if($mois == 9) $lettre = ($abreviation) ? 'SEP' : 'SEPTEMBRE';
            if($mois == 10) $lettre = ($abreviation) ? 'OCT' : 'OCTOBRE';
            if($mois == 11) $lettre = ($abreviation) ? 'NOV' : 'NOVEMBRE';
            if($mois == 12) $lettre = ($abreviation) ? 'DEC' : 'DECEMBRE';

            if(!$majuscule) $lettre = strtolower($lettre);

            return $lettre;
        }

        public static function parseNumber($number, $dec_point=null)
        {
            if (empty($dec_point)) {
                $locale = localeconv();
                $dec_point = $locale['decimal_point'];
            }
            $result = floatval(str_replace($dec_point, '.', preg_replace('/[^\d'.preg_quote($dec_point).']/', '', $number)));
            return (substr($number,0,1) == '-') ? -$result : $result;
        }

        public static function cellColor($objPHPExcel,$cells,$color){
            $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => $color
                )
            ));
        }

        public static function cellTextColor($objPHPExcel,$cells,$color,$bold=false,$size=false)
        {
            $array_temp = array();
            $array_temp['bold'] = $bold;
            $array_temp['color'] = array('rgb' => $color);
            if(!is_bool($size)) $array_temp['size'] = $size;

            $objPHPExcel->getActiveSheet()->getStyle($cells)->applyFromArray(
                array('font'  => $array_temp)
            );
        }

        public static function getNextChar($char)
        {
            if($char == 'Z') return 'A';
            $char++;
            return $char;
        }
    }
?>