<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 21/04/2016
 * Time: 11:45
 */

namespace AppBundle\Controller;


class Cryptage extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('boost_cryptage', array($this, 'cryptageFilter')),
            new \Twig_SimpleFilter('boost_decryptage', array($this, 'decryptageFilter')),
        );
    }


    //crypter
    public function cryptageFilter($str)
    {
        $cle = rtrim(ltrim(com_create_guid(),'{'),'}');
        $cle64 = base64_encode($cle);
        $izy = $cle.$str.$cle64;
        $cle64_1 = base64_encode($izy);
        return $cle64_1;
    }

    //decripter
    public function decryptageFilter($str)
    {
        $cle64_1 = base64_decode($str);
        $cle = substr($cle64_1,0,36);
        $queue = base64_encode($cle);
        $lenth = strlen($queue);
        $rambony = substr($cle64_1,-$lenth);
        if($rambony != $queue) return false;
        $result = str_replace($rambony,'',$cle64_1);
        $result = str_replace($cle,'',$result);
        return $result;
    }

    public function getName()
    {
        return 'boost_cryptage';
    }
}