<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 02/10/2018
 * Time: 14:06
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Functions\GoogleCalendar;
use Doctrine\ORM\EntityRepository;

class GoogleCalendarConfigRepository extends EntityRepository
{
    /**
     * @param Client $client
     * @return mixed
     */
    public function getConfig(Client $client)
    {
        $config = $this->createQueryBuilder('gcs')
            ->where('gcs.client = :client')
            ->andWhere('gcs.identifiant IS NOT NULL')
            ->andWhere("gcs.identifiant <> ''")
            ->setParameter('client',$client)
            ->getQuery()
            ->getOneOrNullResult();

        if ($config)
        {
            $calendar = new GoogleCalendar();
            $calendar->setConfig($config);
            $calendar->setTimeMin(new \DateTime());
            $calendar->setTimeMax(new \DateTime());

            try
            {
                $calendar->getCalendar();
                return $config;
            }
            catch (\Google_Service_Exception $ex)
            {
                return null;
            }
        }

        return null;
    }
}