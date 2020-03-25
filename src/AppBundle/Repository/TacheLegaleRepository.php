<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 30/08/2017
 * Time: 14:37
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheLegale;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TacheLegaleRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getAllTacheWithChild()
    {
        $tacheLegales = [];
        /** @var TacheLegale[] $temps */
        $temps = $this->getAllTache();

        foreach ($temps as $temp)
        {
            $tacheLegaleActions = $this->getEntityManager()->getRepository('AppBundle:TacheLegaleAction')->getByTache($temp);
            if (count($tacheLegaleActions) > 0) $tacheLegales[] = $temp;
        }

        return $tacheLegales;
    }

    public function getAllTache($nomtache = null)
    {
        if ($nomtache) {
            $taches = $this->getEntityManager()
                ->getRepository('AppBundle:TacheLegale')
                ->createQueryBuilder('tache_legale')
                ->select('tache_legale')
                ->where('tache_legale.nom = :nomtache')
                ->setParameters(array(
                    'nomtache' => $nomtache,
                ))
                ->orderBy('tache_legale.nom')
                ->getQuery()
                ->getResult();
        } else {
            $taches = $this->getEntityManager()
                ->getRepository('AppBundle:TacheLegale')
                ->createQueryBuilder('tache_legale')
                ->select('tache_legale')
                ->orderBy('tache_legale.nom')
                ->getQuery()
                ->getResult();
        }
        return $taches;
    }

    public function getAllDossiersActions(Client $client, $nomtache = null)
    {
        $dossiers = $this->getEntityManager()
            ->getRepository('AppBundle:Dossier')
            ->getDossierByClient($client);
        $liste = [];

        /** @var Dossier $dossier */
        foreach ($dossiers as $dossier) {
            $liste = array_merge($liste, $this->getActions($dossier, $nomtache));
        }

        return $liste;
    }

    /**
     * @param Dossier $dossier
     * @param null $nomtache
     * @return array
     * @throws \Exception
     */
    public function getActions(Dossier $dossier, $nomtache = null)
    {
        $liste = [];
        $now = new \DateTime();
        $now->setTime(0,0);
        $current_year = $now->format('Y');
        $cloture = $dossier->getCloture();
        if (!$cloture || $cloture == 0) {
            $cloture = 12;
        }
        $taches = $this->getEntityManager()
            ->getRepository('AppBundle:TacheLegale')
            ->getAllTache($nomtache);
        /** @var \AppBundle\Entity\TacheLegale $tache */
        foreach ($taches as $tache) {
            /** Tester si le dossier est concerné par la Tache */
            $regime_fiscaux = $tache->getRegimeFiscal();
            $forme_activites = $tache->getFormeActivite();
            $forme_juridiques = $tache->getFormeJuridique();
            $date_clotures = $tache->getDateCloture();

            $is_regime_fiscal = count($regime_fiscaux) == 0 ||
                ($dossier->getRegimeFiscal() && in_array($dossier->getRegimeFiscal()->getId(), $regime_fiscaux));
            $is_forme_activite = count($forme_activites) == 0 ||
                ($dossier->getFormeActivite() && in_array($dossier->getFormeActivite()->getId(), $forme_activites));
            $is_forme_juridique = count($forme_juridiques) == 0 ||
                ($dossier->getFormeJuridique() && in_array($dossier->getFormeJuridique()->getId(), $forme_juridiques));
            $is_date_cloture = count($date_clotures) == 0 ||
                (in_array($cloture, $date_clotures));
            if ($is_regime_fiscal && $is_forme_activite && $is_forme_juridique && $is_date_cloture) {
                /** @var \AppBundle\Entity\TacheLegaleAction[] $actions */
                $actions = $tache->getActions();
                if (count($actions) > 0) {
                    foreach ($actions as $action) {
                        $date = trim($action->getDateAction());
                        $date = str_replace("  ", " ", $date);
                        if ($date != '') {
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
                            if ($CONSTANT) {
                                $value = preg_replace("#^\[(.+)\]$#", "$1", $date);
                                $value = \DateTime::createFromFormat("d/m/Y", "$value/$current_year");
                                $liste[] = [
                                    'dossier' => $dossier,
                                    'tache' => $tache,
                                    'action' => $action,
                                    'date' => $value->setTime(0,0),
                                ];
                            } elseif ($IF_CLOTURE) {
                                $langage = new ExpressionLanguage();
                                $value = preg_replace("#^SI\((.+)\)$#i", "IF($1)", $date);
                                $value = preg_replace("#(\=){1}#i", "==", $value);
                                $value = preg_replace("#^(.+)[;]{1}(.+)[;](.+)$#i", "$1?$2:$3", $value);
                                preg_match("#\(cloture[+,-][1-9]\)#", $date, $cloture_oper);
                                if (count($cloture_oper)) {
                                    $item = $langage->evaluate($cloture_oper[0], ['cloture' => $cloture]);
                                    if (intval($item) > 12) {
                                        $item = $item - 12;
                                    }
                                    $item = str_pad($item, 2, "0", STR_PAD_LEFT);
                                    $value = preg_replace("#\(cloture[+,-][1-9]\)#", $item, $value);
                                    $value = preg_replace("#^IF\((.+)\)$#i", "$1", $value);
                                    $value = str_replace("[", '"', $value);
                                    $value = str_replace("]", '"', $value);
                                    $value = $langage->evaluate($value, ['cloture' => $cloture]);
                                    $value = \DateTime::createFromFormat("d/m/Y", "$value/$current_year");
                                    $liste[] = [
                                        'dossier' => $dossier,
                                        'tache' => $tache,
                                        'action' => $action,
                                        'date' => $value->setTime(0,0),
                                    ];
                                }
                            } elseif ($CLOTURE_PLUS) {
                                $langage = new ExpressionLanguage();
                                $item = str_replace("[", "(", $date);
                                $item = str_replace("]", ")", $item);
                                $item = $langage->evaluate($item, ['cloture' => $cloture]);
                                if (intval($item) > 12) {
                                    $item = $item - 12;
                                }
                                $item = str_pad($item, 2, "0", STR_PAD_LEFT);
                                $value = \DateTime::createFromFormat("d/m/Y", "01/$item/$current_year");
                                $value->add(new \DateInterval('P1M'));
                                $value->sub(new \DateInterval('P1D'));
                                $liste[] = [
                                    'dossier' => $dossier,
                                    'tache' => $tache,
                                    'action' => $action,
                                    'date' => $value->setTime(0,0),
                                ];
                            } elseif ($DATE_PLUS_JO) {
                                preg_match("#^\[[0-9]{2}/[0-9]{2}#", $date, $base);
                                if (count($base) > 0) {
                                    $item = str_replace("[", "", $base[0]);
                                    $date_base = \DateTime::createFromFormat("d/m/Y", "$item/$current_year");
                                    preg_match("#\(JO[+][0-9]{1,}\)\]$#", $date, $jo_part);
                                    preg_match("#[0-9]{1,}#", $jo_part[0], $plus);

                                    if (count($plus) > 0) {
                                        $nb_jo = 0;
                                        while ($nb_jo < intval($plus[0])) {
                                            $date_base->add(new \DateInterval('P1D'));
                                            if ($date_base->format('w') != 6 && $date_base->format('w') != 0) {
                                                $nb_jo++;
                                            }
                                        }
                                        $value = $date_base;
                                        $liste[] = [
                                            'dossier' => $dossier,
                                            'tache' => $tache,
                                            'action' => $action,
                                            'date' => $value->setTime(0,0),
                                        ];
                                    }
                                }
                            } elseif ($INFOPERDOS) {
                                $declaration = preg_match("#d{e,é}claration#i", $action->getNom());
                                if ($declaration) {
                                    $jour_declaration = $dossier->getTvaDate();
                                    $periode_declaration = $dossier->getTvaMode();
                                    if ($jour_declaration && trim($jour_declaration) != '') {
                                        /** 5e jour de 5e mois */
                                        if ($jour_declaration == 55) {
                                            $mois = $cloture + 5;
                                            if ($mois > 12) {
                                                $mois = $mois - 12;
                                            }
                                            $mois = str_pad($mois, 2, "0", STR_PAD_LEFT);
                                            $value = \DateTime::createFromFormat("d/m/Y", "05/$mois/$current_year");
                                            $liste[] = [
                                                'dossier' => $dossier,
                                                'tache' => $tache,
                                                'action' => $action,
                                                'date' => $value->setTime(0,0),
                                            ];
                                        } else {
                                            /** Mensuel */
                                            $jour = str_pad($jour_declaration, 2, "0", STR_PAD_LEFT);
                                            $mois = str_pad(strval($cloture), 2, "0", STR_PAD_LEFT);
                                            $value = \DateTime::createFromFormat("d/m/Y", "$jour/$mois/$current_year");
                                            $value->add(new \DateInterval('P1M'));
                                            $liste[] = [
                                                'dossier' => $dossier,
                                                'tache' => $tache,
                                                'action' => $action,
                                                'date' => $value->setTime(0,0),
                                            ];
                                            for ($i = 1; $i < 12; $i++) {
                                                $value->add(new \DateInterval('P1M'));
                                                $liste[] = [
                                                    'dossier' => $dossier,
                                                    'tache' => $tache,
                                                    'action' => $action,
                                                    'date' => $value->setTime(0,0),
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        usort($liste, function($a, $b) {
            try {
                if (isset($a['date']) && isset($b['date'])) {
                    return $a['date'] > $b['date'];
                }
                return 0;
            } catch (\Exception $ex) {
                return 0;
            }
        });
        return $liste;
    }
}