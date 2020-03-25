<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Site;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Validator\Constraints\IsNull;

class SiteRepository extends EntityRepository
{
    /**
     * @param Client|null $client
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function getSiteByClient(Client $client = null)
    {
        $sites = $this->getEntityManager()
            ->getRepository('AppBundle:Site')
            ->createQueryBuilder('s')
            ->leftJoin('s.client','c')
            ->select('s')
            ->where("s.nom != ''")
            ->andWhere("s.status = :status")
            ->setParameter('status', 1);

        if (!is_null($client))
        {
            $sites = $sites
                ->andWhere("s.client = :client")
                ->setParameter('client', $client);
        }

        return $sites
            ->orderBy('c.nom')
            ->addOrderBy('s.nom')
            ->getQuery()
            ->getResult();
    }
}