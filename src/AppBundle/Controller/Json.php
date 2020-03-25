<?php

namespace AppBundle\Controller;

class Json extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('boost_cryptage', array($this, 'cryptageFilter')),
            new \Twig_SimpleFilter('boost_decryptage', array($this, 'decryptageFilter')),
            'json_decode'   => new \Twig_Filter_Method($this, 'jsonDecode'),
        );
    }

    public function jsonDecode($str) {
        return json_decode($str, true);
    }

    public function getName()
    {
        return 'json';
    }
}