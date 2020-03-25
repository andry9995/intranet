<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 23/06/2016
 * Time: 16:40
 */

namespace AppBundle\Repository;

use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityRepository;

class EtapeTraitementRepository extends EntityRepository
{
    public function getByCode($code, $to_array = false)
    {
        $qb = $this->getEntityManager()
            ->getRepository('AppBundle:EtapeTraitement')
            ->createQueryBuilder('e');
        $etape = $qb
            ->select()
            ->where('e.code = :code')
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery();

        if ($to_array) {
            return $etape->getArrayResult();
        }
        return $etape->getResult();
    }

    public function getReel()
    {
        $etapes = $this->getEntityManager()
            ->getRepository('AppBundle:EtapeTraitement')
            ->createQueryBuilder('etape')
            ->select('etape')
            ->where('etape.typeEtape = :type')
            ->setParameters(array(
                'type' => 1
            ))
            ->getQuery()
            ->getResult();
        return $etapes;
    }

    /**
     * Get Listes Postes d'une Etape de Traitement
     *
     * @param EtapeTraitement $etape
     * @return array
     */
    public function getPostes(EtapeTraitement $etape)
    {
        $postes = [];
        if ($etape->getPostes()) {
            foreach ($etape->getPostes() as $poste) {
                $organisation = $this->getEntityManager()
                    ->getRepository('AppBundle:Organisation')
                    ->find($poste);
                if ($organisation) {
                    $postes[] = $organisation;
                }
            }
        }
        return $postes;
    }

    /**
     * Get Listes postes en indiquant ce qui est affecté
     * à une Etape de Traitement
     *
     * @param EtapeTraitement $etape
     * @return array
     */
    public function getPostesListeByEtape(EtapeTraitement $etape)
    {
        $postes = $this->getEntityManager()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getPostes($etape);
        $poste_ids = [];
        /** @var Organisation $poste */
        foreach ($postes as $poste) {
            $poste_ids[] = $poste->getId();
        }

        $org_postes = $this->getEntityManager()
            ->getRepository('AppBundle:Organisation')
            ->createQueryBuilder('org')
            ->select('org')
            ->innerJoin('org.organisationNiveau', 'niveau')
            ->addSelect('niveau')
            ->where('niveau.isPoste = :is_poste')
            ->andWhere("org.nom != '' AND org.nom IS NOT NULL")
            ->setParameters(array(
                'is_poste' => true
            ))
            ->orderBy('org.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $liste = [];
        /** @var Organisation $org_poste */
        foreach ($org_postes as $org_poste) {
            $liste[] = [
                'org_id' => $org_poste->getId(),
                'org_nom' => $org_poste->getNom(),
                'niveau_id' => $org_poste->getOrganisationNiveau()->getId(),
                'niveau' => $org_poste->getOrganisationNiveau()->getTitre(),
                'niveau_is_poste' => $org_poste->getOrganisationNiveau()->getIsPoste(),
                'niveau_rang' => $org_poste->getOrganisationNiveau()->getRang(),
                'is_selected' => in_array($org_poste->getId(), $poste_ids) ? true : false,
                'etape_id' => $etape->getId(),
                'etape' => $etape->getLibelle(),
                'etape_code' => $etape->getCode(),
            ];
        }

        return $liste;
    }
}