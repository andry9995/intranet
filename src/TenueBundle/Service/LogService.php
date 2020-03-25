<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 05/07/2019
 * Time: 09:23
 */

namespace TenueBundle\Service;


use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Image;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Operateur;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;

class LogService
{
    private $entity_manager;

    public function __construct(EntityManager $em)
    {
        $this->entity_manager= $em;

    }

    public function Save(Image $image, EtapeTraitement $etapeTraitement, Operateur $operateur, $remarque, $ip){
        $log = new Logs();
        $log->setImage($image);
        $log->setEtapeTraitement($etapeTraitement);
        $log->setOperateur($operateur);
        $log->setRemarque($remarque);
        $log->setDateDebut(new \DateTime('now'));
        $log->setDateFin(new \DateTime('now'));
        $log->setIp($ip);

        $this->entity_manager->persist($log);
        try {
            $this->entity_manager->flush();
        } catch (OptimisticLockException $e) {
            return false;
        }

        return true;


    }

}