<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 24/03/2020
 * Time: 09:55
 */

namespace TenueBundle\Service;


use AppBundle\Entity\Image;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;

class StatusImageService
{
    private $entity_manager;

    public function __construct(EntityManager $em)
    {
        $this->entity_manager = $em;
    }

    public function SetStatusImage(Image $image){

        $imageAtraiters = $this->entity_manager
            ->getRepository('AppBundle:ImageATraiter')
            ->findBy(['image' => $image]);

        $isImputee = $this->checkImputee($image);

        if(count($imageAtraiters) > 0){
            $imageAtraiter = $imageAtraiters[0];

            $imageAtraiter->setSaisie2(2);
            $imageAtraiter->setSaisie1(2);

            if($isImputee){
                $imageAtraiter->setStatus(8);
            }
            else{
                $imageAtraiter->setStatus(6);
            }
        }

        if($isImputee){
            $image->setSaisie1(3)
                ->setSaisie2(3)
                ->setCtrlSaisie(3)
                ->setImputation(3)
                ->setCtrlImputation(3);
        }
        else{
            $image->setSaisie1(3)
                ->setSaisie2(3)
                ->setCtrlSaisie(3);
        }

        $image->setUniverselle(1);

        try {
            $this->entity_manager->flush();
        } catch (OptimisticLockException $e) {
        }

    }

    private function checkImputee(Image $image){

        $tvaImputationControles = $this->entity_manager
            ->getRepository('AppBundle:TvaImputationControle')
            ->findBy(['image' => $image]);

        if(count($tvaImputationControles) > 0){
            foreach ($tvaImputationControles as $tvaImputationControle){
                if($tvaImputationControle->getPcc() !== null || $tvaImputationControle->getPccTva() !== null ||
                    $tvaImputationControle->getPccBilan() !== null || $tvaImputationControle->getTiers() !== null){
                    return true;
                }
            }
        }

        if($image->getValider() > 0)
            return true;

        return false;
    }

}