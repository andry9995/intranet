<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 28/03/2018
 * Time: 09:03
 */

namespace AppBundle\Functions;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializeToJSON
{
    public function serialize($data)
    {
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        return $serializer->serialize($data, 'json');
    }
}