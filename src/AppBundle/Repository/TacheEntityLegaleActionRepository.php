<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/09/2018
 * Time: 11:31
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheEntity;
use AppBundle\Entity\TacheEntityLegaleAction;
use AppBundle\Entity\TacheLegaleAction;
use Doctrine\ORM\EntityRepository;
use RevisionBundle\Controller\Functions;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TacheEntityLegaleActionRepository extends EntityRepository
{
    /**
     * @param TacheLegaleAction $tacheLegaleAction
     * @param TacheEntity|null $tacheEntity
     * @return mixed
     */
    public function getByTacheEntityTacheLegaleAction(TacheLegaleAction $tacheLegaleAction,TacheEntity $tacheEntity = null)
    {
        if (is_null($tacheEntity)) return null;
        return $this->createQueryBuilder('e')
            ->where('e.tacheEntity = :tacheEntity')
            ->andWhere('e.tacheLegaleAction = :tacheLegaleAction')
            ->setParameters([
                'tacheEntity' => $tacheEntity,
                'tacheLegaleAction' => $tacheLegaleAction
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param TacheEntity $tacheEntity
     * @param $annee
     * @return array
     */
    public function getTacheEntityLegaleActions(TacheEntity $tacheEntity,$annee)
    {
        $dossier = $tacheEntity->getDossier();
        $tacheEntity = (!is_null($tacheEntity->getTacheEntity())) ? $tacheEntity->getTacheEntity() : $tacheEntity;
        $tacheEntityLegaleActions = $this->createQueryBuilder('tela')
            ->where('tela.tacheEntity = :tacheEntity')
            ->setParameter('tacheEntity',$tacheEntity)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($tacheEntityLegaleActions as $tacheEntityLegaleAction)
        {
            $results[] = $this->getDatesInYearCurrent($tacheEntityLegaleAction,$dossier,$annee);
        }

        return $results;
    }

    /**
     * @param TacheEntityLegaleAction $tacheEntityLegaleAction
     * @param Dossier|null $dossier
     * @param null $annee
     * @return object
     */
    public function getDatesInYearCurrent(TacheEntityLegaleAction $tacheEntityLegaleAction, Dossier $dossier = null, $annee = null)
    {
        $cloture = $dossier->getCloture();
        $liste = [];
        $endStartYear = Functions::getStartEndInAnnee($annee);
        $action = $tacheEntityLegaleAction->getTacheLegaleAction();
        $date = trim($action->getDateAction());
        $date = str_replace("  ", " ", $date);
        if ($date != '')
        {
            if (!$cloture || $cloture == 0) $cloture = 12;

            /** Valeur constante: [15/07] */
            $CONSTANT = preg_match("#^\[[0-9]{2}/[0-9]{2}\]$#", $date);

            /** Condition SI selon cloture: SI(cloture=12;[15/05];[15/(cloture+4)]) */
            $IF_CLOTURE = preg_match("#^SI\((.+)\)$#i", $date) && preg_match("#\(cloture[+,-][0-9]\)#", $date, $var);

            /** Cloture plus N mois: [cloture+3] */
            $CLOTURE_PLUS = preg_match("#^\[cloture[+][0-9]+\]$#i", $date);

            /** Une date + jours NB ouvrable: [01/05]+(JO+2)] */
            $DATE_PLUS_JO = preg_match("#^\[[0-9]{2}/[0-9]{2}[+]\(JO[+][0-9]{1,}\)\]$#", $date);

            /** A chercher dans infoperdos: [infoperdos] */
            $INFOPERDOS = preg_match("#^\[infoperdos\]$#i", $date);

            $jourAdditif = intval($tacheEntityLegaleAction->getJourAdditif());
            if ($CONSTANT) {
                $value = preg_replace("#^\[(.+)\]$#", "$1", $date);
                $value = \DateTime::createFromFormat("d/m/Y", "$value/$annee");

                if ($jourAdditif != 0)
                {
                    if ($jourAdditif > 0) $value->add(new \DateInterval('P'.$jourAdditif.'D'));
                    else $value->sub(new \DateInterval('P'.abs($jourAdditif).'D'));
                }

                $liste[] = $value->setTime(0,0,0);
            } elseif ($IF_CLOTURE) {
                $langage = new ExpressionLanguage();
                $value = preg_replace("#^SI\((.+)\)$#i", "IF($1)", $date);
                $value = preg_replace("#(\=){1}#i", "==", $value);
                $value = preg_replace("#^(.+)[;]{1}(.+)[;](.+)$#i", "$1?$2:$3", $value);
                preg_match("#\(cloture[+,-][1-9]\)#", $date, $cloture_oper);
                if (count($cloture_oper)) {
                    $item = $langage->evaluate($cloture_oper[0], ['cloture' => $cloture]);
                    if (intval($item) > 12) $item = $item - 12;
                    $item = str_pad($item, 2, "0", STR_PAD_LEFT);
                    $value = preg_replace("#\(cloture[+,-][1-9]\)#", $item, $value);
                    $value = preg_replace("#^IF\((.+)\)$#i", "$1", $value);
                    $value = str_replace("[", '"', $value);
                    $value = str_replace("]", '"', $value);
                    $value = $langage->evaluate($value, ['cloture' => $cloture]);
                    $value = \DateTime::createFromFormat("d/m/Y", "$value/$annee");

                    if ($jourAdditif != 0)
                    {
                        if ($jourAdditif > 0) $value->add(new \DateInterval('P'.$jourAdditif.'D'));
                        else $value->sub(new \DateInterval('P'.abs($jourAdditif).'D'));
                    }

                    $liste[] = $value->setTime(0,0,0);
                }
            } elseif ($CLOTURE_PLUS) {
                $langage = new ExpressionLanguage();
                $item = str_replace("[", "(", $date);
                $item = str_replace("]", ")", $item);
                $item = $langage->evaluate($item, ['cloture' => $cloture]);
                if (intval($item) > 12) $item = $item - 12;
                $item = str_pad($item, 2, "0", STR_PAD_LEFT);
                $value = \DateTime::createFromFormat("d/m/Y", "01/$item/$annee");
                $value->add(new \DateInterval('P1M'));
                $value->sub(new \DateInterval('P1D'));

                if ($jourAdditif != 0)
                {
                    if ($jourAdditif > 0) $value->add(new \DateInterval('P'.$jourAdditif.'D'));
                    else $value->sub(new \DateInterval('P'.abs($jourAdditif).'D'));
                }

                $liste[] = $value->setTime(0,0,0);
            } elseif ($DATE_PLUS_JO) {
                preg_match("#^\[[0-9]{2}/[0-9]{2}#", $date, $base);
                if (count($base) > 0) {
                    $item = str_replace("[", "", $base[0]);
                    $date_base = \DateTime::createFromFormat("d/m/Y", "$item/$annee");
                    //$date_base = new \DateTime();
                    preg_match("#\(JO[+][0-9]{1,}\)\]$#", $date, $jo_part);
                    preg_match("#[0-9]{1,}#", $jo_part[0], $plus);

                    if (count($plus) > 0) {
                        $nb_jo = 0;

                        if ($jourAdditif != 0)
                        {
                            if ($jourAdditif > 0) $date_base->add(new \DateInterval('P'.$jourAdditif.'D'));
                            else $date_base->sub(new \DateInterval('P'.abs($jourAdditif).'D'));
                        }

                        while ($nb_jo < intval($plus[0])) {
                            $date_base->add(new \DateInterval('P1D'));
                            if ($date_base->format('w') != 6 && $date_base->format('w') != 0) {
                                $nb_jo++;
                            }
                        }
                        $value = $date_base;
                        $liste[] = $value->setTime(0,0,0);
                    }
                }
            } elseif ($INFOPERDOS) {
                $declaration = preg_match("#D[eaé]claration#ui", $action->getNom());

                if ($declaration)
                {
                    $jour_declaration = intval($dossier->getTvaDate());
                    $periode_declaration = $dossier->getTvaMode();
                    if ($jour_declaration && trim($jour_declaration) != '')
                    {
                        // 5e jour de 5e mois
                        if ($jour_declaration == 55)
                        {
                            $mois = $cloture + 5;
                            if ($mois > 12) $mois -= 12;

                            $mois = str_pad($mois, 2, "0", STR_PAD_LEFT);
                            $value = \DateTime::createFromFormat("d/m/Y", "05/$mois/$annee");

                            if ($jourAdditif != 0)
                            {
                                if ($jourAdditif > 0) $value->add(new \DateInterval('P'.$jourAdditif.'D'));
                                else $value->sub(new \DateInterval('P'.abs($jourAdditif).'D'));
                            }

                            $liste[] = $value->setTime(0,0,0);
                        }
                        else
                        {
                            //Mensuel
                            $jour = str_pad($jour_declaration, 2, "0", STR_PAD_LEFT);
                            $mois = str_pad(strval($cloture), 2, "0", STR_PAD_LEFT);
                            $anneeMoins = $annee - 1;
                            $value = \DateTime::createFromFormat("d/m/Y", "$jour/$mois/$anneeMoins");
                            $value->setTime(0,0,0);

                            for ($i = 1; $i <= 24; $i++)
                            {
                                $v = clone  $value;//$value->add(new \DateInterval('P1M'));
                                $v->setTime(0,0,0);
                                $v->add(new \DateInterval('P'.$i.'M'));

                                if ($jourAdditif != 0)
                                {
                                    if ($jourAdditif > 0) $v->add(new \DateInterval('P'.$jourAdditif.'D'));
                                    else $v->sub(new \DateInterval('P'.abs($jourAdditif).'D'));
                                }

                                $liste[] = $v;
                            }
                        }
                    }
                }
            }
        }

        return (object)
        [
            'dossier' => $dossier,
            'tacheEntityLegaleAction' => $tacheEntityLegaleAction,
            'liste' => $liste
        ];
    }
}