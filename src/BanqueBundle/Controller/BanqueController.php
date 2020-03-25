<?php

namespace BanqueBundle\Controller;

use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\HistoriqueCategorie;
use AppBundle\Entity\Lot;
use AppBundle\Entity\MethodeComptable;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Panier;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\ResponsableCsd;
use AppBundle\Entity\PrioriteImage;
use ImageBundle\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Tests\Compiler\J;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use AppBundle\Entity\Image;
use AppBundle\Entity\Client;
use AppBundle\Entity\Separation;
use AppBundle\Functions\CustomPdoConnection;
use Symfony\Component\Filesystem\Filesystem;
use TCPDF;

class BanqueController extends Controller
{
    public $pdo;

    //initisalisation pdo
    public function __construct()
    {
        $con = new CustomPdoConnection();
        $this->pdo = $con->connect();
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('BanqueBundle:Banque:index.html.twig');
    }

    public function siteAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('AccÃ¨s refusÃ©');

        $idata = json_decode($request->request->get('idata'), true);
        $data  = array();

        if(isset($idata['client'])){
            $client = $this->getDoctrine()
                            ->getRepository('AppBundle:Client')
                            ->find($idata['client']);
            $sites = $this->getDoctrine()
                            ->getRepository('AppBundle:Site')
                            ->getSiteByClient($client);
        }
        return new JsonResponse($sites);
    }

    public function dossierAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('AccÃ¨s refusÃ©');

        $idata = json_decode($request->request->get('idata'), true);
        $data  = array();

        if(isset($idata['client'])){
            $data['dossiers'] = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->getDossierClient($idata['client'], false);
        }
        return new JsonResponse($data);  
    }

    /**
     * @return Response
     */
    public function lotAction()
    {
        return $this->render('BanqueBundle:Banque:lot.html.twig');
    }

    public function statistiqueAction()
    {
        return $this->render('BanqueBundle:Banque:statistique.html.twig');
    }

    public function productiviteAction()
    {
        return $this->render('BanqueBundle:Banque:productivite.html.twig');
    }

    public function controleAction()
    {
        return $this->render('BanqueBundle:Banque:controle.html.twig');
    }

    public function panierAction()
    {
        return $this->render('BanqueBundle:Banque:panier.html.twig');
    }

    public function calculManquant()
    {
        /*$Objdossier = $this->getDoctrine()->getManager()->getRepository('AppBundle:Dossier')
                          ->createQueryBuilder('d')
                          ->where('d.id = :id')
                          ->setParameter('id', 9271)
                          ->getQuery()
                          ->getOneOrNullResult();
            $complet = $this->getDoctrine()->getRepository('AppBundle:TbimagePeriode')
                            ->getAnneeMoisExercices($Objdossier,2019);

            $moisc = $complet->ms;
            $moisc= array_reverse($moisc);
            echo sizeof($moisc);
        print_r($moisc);die();*/
        $q = 'UPDATE releve_manquant SET mois_complet="Abscence Totale" WHERE status=0';
        $this->pdo->exec($q);
        $query = "SELECT * FROM releve_manquant where status=1";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($datas as $d) {
            $Objdossier = $this->getDoctrine()->getManager()->getRepository('AppBundle:Dossier')
                ->createQueryBuilder('d')
                ->where('d.id = :id')
                ->setParameter('id', $d['dossier_id'])
                ->getQuery()
                ->getOneOrNullResult();
            $complet = $this->getDoctrine()->getRepository('AppBundle:TbimagePeriode')
                ->getAnneeMoisExercices($Objdossier, $d['exercice']);
            $moisc = $complet->ms;
            $moisc = array_reverse($moisc);
            $manq = explode(',', $d['mois']);
            $lib = "Incomplet";
            $test1 = true;
            $test2 = true;
            $test3 = true;
            $test4 = true;
            $rest1 = false;
            $rest2 = false;
            $rest3 = false;
            $manq = array_map('trim', $manq);
            if (sizeof($moisc) > 3) {
                if (sizeof($manq) == 1) {
                    if (in_array($moisc[0], $manq)) {
                        $lib = "m-1";
                    }
                } else if (sizeof($manq) == 2) {
                    if (in_array($moisc[0], $manq) && in_array($moisc[1], $manq)) {
                        $lib = "m-2";
                    }
                } else if (sizeof($manq) == 3) {
                    if (in_array($moisc[0], $manq) && in_array($moisc[1], $manq) && in_array($moisc[2], $manq)) {
                        $lib = "m-3";
                    }
                } else if (sizeof($manq) == 4) {
                    if (in_array($moisc[0], $manq) && in_array($moisc[1], $manq) && in_array($moisc[2], $manq) && in_array($moisc[3], $manq)) {
                        $lib = "m-4";
                    }
                }
            }
            $q = 'UPDATE releve_manquant SET mois_complet="' . $lib . '" WHERE id=' . $d['id'];
            $this->pdo->exec($q);
        }
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     *
     * @final since version 3.4
     */
    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    /**
     * @return array
     */
    public function getExercices($nbAnnee = 6, $debut = 1)
    {
        $date_now = new \DateTime();
        $current_year = intval($date_now->format('Y'));
        $exercices = array();

        for ($i = 0; $i < $nbAnnee; $i++) {
            $exercices[] = $current_year + $debut - $i;
        }

        return $exercices;
    }

    /**
     * Afficher situation image
     * @param Request $request
     * @return JsonResponse|Response
     *
     */
    public function showSituaImageAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // try {
            $param = array();
            $param['client'] = $request->request
                ->get('client');
            $param['dossier'] = $request->request
                ->get('dossier');
            $param['exercice'] = $request->request
                ->get('exercice');
            $param['periode'] = $request->request
                ->get('periode');
            $param['site'] = $request->request
                ->get('site');

            // Test value of periode
            switch ($param['periode']) {
                case 1:
                    // Aujourd'hui
                    $param['cas'] = 1;
                    $periodNow = new \DateTime();
                    $param['aujourdhui'] = $periodNow->format('Y-m-d');

                    break;
                case 2:
                    // Depuis une semaine
                    $param['cas'] = 2;
                    $periodeNow = new \DateTime();
                    $now = clone $periodeNow;
                    $oneWeek = date_modify($periodeNow, "-7 days");
                    $param['dateDeb'] = $oneWeek->format('Y-m-d');
                    $param['dateFin'] = $now->format('Y-m-d');

                    break;
                case 3:
                    // Depuis un mois
                    $param['cas'] = 3;
                    $periodeNow = new \DateTime();
                    $now = clone $periodeNow;
                    $oneMonth = date_modify($periodeNow, "-1 months");
                    $param['dateDeb'] = $oneMonth->format('Y-m-d');
                    $param['dateFin'] = $now->format('Y-m-d');

                    break;
                case 4:
                    // Tous les exercixe
                    $param['cas'] = 4;

                    break;
                case 5:
                    // Fourchette date debut et date fin
                    $param['cas'] = 5;
                    $debPeriode = $request->request
                        ->get('perioddeb');
                    $finPeriode = $request->request
                        ->get('periodfin');
                    if ((isset($debPeriode) && !is_null($debPeriode)) && (isset($finPeriode) && !is_null($finPeriode))) {
                        $param['dateDeb'] = $debPeriode;
                        $param['dateFin'] = $finPeriode;
                    }
                    break;
            }
            $sitImage = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getSituationImage($param);

            //---Count Client ---//
            $idossier = array();
            if ($param['client'] == 0) {
                $user = $this->getUser();
                $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
                $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");

                if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                    $query = $query->andWhere('c = :client')->setParameter('client', $user->getClient());
                }
                $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
                $clients = $query->getResult();
                $dossiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Dossier')
                    ->getAllDossier();
                foreach ($dossiers as $key => $value) {
                    $idossier[] = $value['iddossier'];
                }
                $sitImage['nbdossier'] = count($idossier);
                $sitImage['nbclient'] = count($clients);
            } else {
                $sitImage['nbclient'] = 1;
                $_client = $this->getDoctrine()
                    ->getRepository('AppBundle:Client')
                    ->find($param['client']);
                if ($param['dossier'] == 0) {
                    if ($_client) {
                        $dossiers = $this->getDoctrine()
                            ->getRepository('AppBundle:Dossier')
                            ->getAllDossier($_client);
                        foreach ($dossiers as $key => $value) {
                            $idossier[] = $value['iddossier'];
                        }
                    }
                } else {
                    if ($_client) {
                        $dossiers = $this->getDoctrine()
                            ->getRepository('AppBundle:Dossier')
                            ->getAllDossier($_client, $param['dossier']);
                        foreach ($dossiers as $key => $value) {
                            $idossier[] = $value['iddossier'];
                        }
                    }
                }
                $sitImage['nbdossier'] = count($dossiers);
            }

            //--Count numcompte
            $con = new CustomPdoConnection();
            $pdo = $con->connect();
            $indossier = implode(",", $idossier);


            //--Count releve bancaires manquants--//

            //---new querry for compte banque image sous categorie releve banque
            $queryReleve = "select distinct bc.id 
                          from releve r 
                          left join banque_compte bc on (bc.id=r.banque_compte_id)  
                          inner join banque bq on (bq.id = bc.banque_id)  
                          where bc.dossier_id in (" . $indossier . ") ";
            /*if ($banque != null)
                {
                    $query .= "AND bq.id = :banque";
                    $params['banque'] = $banque->getId();
                }*/
            $prepReleve = $pdo->prepare($queryReleve);
            $prepReleve->execute();
            $idsT = $prepReleve->fetchAll();
            $ids = [];
            foreach ($idsT as $item) {
                $ids[] = $item->id;
            }

            $ids = implode(",", $ids);

            $queryNumCompt = "SELECT DISTINCT(id) AS numcompte 
                        FROM banque_compte 
                        WHERE id IN (" . $ids . ") ";
            $prepNumCompt = $pdo->prepare($queryNumCompt);
            $prepNumCompt->execute(array());
            $resNumcompt = $prepNumCompt->fetchAll();
            $sitImage['numcomptbq'] = count($resNumcompt);
            $innumcompte = array();
            foreach ($resNumcompt as $value) {
                $innumcompte[] = $value->numcompte;
            }
            $innumcompte = implode(",", $innumcompte);
            /*return $banquesComptes = $this->createQueryBuilder('bc')
                    ->where('bc.id IN (:ids)')
                    ->setParameter('ids',$ids)
                    ->getQuery()
                    ->getResult();*/
            //---end new requete
            $today = new \DateTime();
            $compYear = $today->format('Y');
            $affichM = false;
            if ($param['exercice'] < $compYear) {
                $affichM = true;
            }
            $queryManquant = "SELECT D.id, banque_compte_id AS id_compt, R.mois, R.status, D.cloture, D.debut_activite
                        FROM releve_manquant R
                        INNER JOIN dossier D ON (R.dossier_id = D.id)  
                        WHERE banque_compte_id IN (" . $innumcompte . ") AND R.exercice = " . $param['exercice'] . " ";
            $prepManquant = $pdo->prepare($queryManquant);
            $prepManquant->execute();
            $resManquant = $prepManquant->fetchAll();

            $cpt_m = 0;
            $cpt_un = 0;
            $cpt_deux = 0;
            $cpt_trois = 0;
            $cpt_quatre = 0;
            $cpt_incpl = 0;
            $cpt_abstot = 0;
            $casAffich = "";
            $tab_cpt_m = array();
            $tab_cpt_m_un = array();
            $tab_cpt_m_deux = array();
            $tab_cpt_m_trois = array();
            $tab_cpt_m_quatre = array();

            $tab_m_incpl = array();
            $tab_m_un_incpl = array();
            $tab_m_deux_incpl = array();
            $tab_m_trois_incpl = array();
            $tab_m_quatre_incpl = array();

            $incpl_m = false;
            $incpl_m_un = false;
            $incpl_m_deux = false;
            $incpl_m_trois = false;
            $incpl_m_quatre = false;
            $tab_a_jour = array();
            $tab_abc_tot = array();

            $tab_incpl = array();
            if ($resManquant) {
                foreach ($resManquant as $key => $value) {
                    $debutExercice = $param['exercice'] . '-01';
                    $yearDeb = substr($value->debut_activite, 0, 4);
                    if ($yearDeb == $today->format('Y') && $param['exercice'] == $today->format('Y')) {
                        $mYD = substr($value->debut_activite, 5, 2);
                        $debutBoucl = $yearDeb . '-' . $mYD;
                    } elseif ($yearDeb < $today->format('Y') && $param['exercice'] == $today->format('Y')) {
                        $debutBoucl = $param['exercice'] . '-01';
                    } elseif ($param['exercice'] < $today->format('Y')) {
                        if ($yearDeb == $param['exercice']) {
                            $mYD = substr($value->debut_activite, 5, 2);
                            $debutBoucl = $param['exercice'] . '-' . $mYD;
                        } elseif ($yearDeb < $param['exercice']) {
                            $mYD = substr($value->debut_activite, 5, 2);
                            $debutBoucl = $param['exercice'] . '-01';
                        } elseif ($yearDeb > $param['exercice']) {
                            continue;
                        }
                    }
                    if ($param['exercice'] == $today->format('Y')) {
                        if ($today->format('d') == 31) {
                            $day = new \DateTime($today->format('Y') . '-' . $today->format('m') . '-30');
                        } else {
                            $day = clone $today;
                        }
                        $m = clone $day;
                        $m_now = $this->monthForRb($day, $debutBoucl);

                        $m_moin_un = clone $day;
                        $m_moin_un = $this->monthForRb(date_modify($m_moin_un, " -1 month"), $debutBoucl);

                        $m_moin_deux = clone $day;
                        $m_moin_deux = $this->monthForRb(date_modify($m_moin_deux, "-2 month"), $debutBoucl);
                        // var_dump($m_moin_deux);die();

                        $m_moin_trois = clone $day;
                        $m_moin_trois = $this->monthForRb(date_modify($m_moin_trois, "-3 month"), $debutBoucl);

                        $m_moin_quatre = clone $day;
                        $m_moin_quatre = $this->monthForRb(date_modify($m_moin_quatre, "-4 month"), $debutBoucl);
                    } else {
                        $day = new \DateTime($param['exercice'] . '-' . $value->cloture . '-01');

                        $m = clone $day;
                        $m_now = $this->monthForRb($day, $debutBoucl);

                        $m_moin_un = clone $day;
                        $m_moin_un = $this->monthForRb(date_modify($m_moin_un, " -1 month"), $debutBoucl);

                        $m_moin_deux = clone $day;
                        $m_moin_deux = $this->monthForRb(date_modify($m_moin_deux, "-2 month"), $debutBoucl);

                        $m_moin_trois = clone $day;
                        $m_moin_trois = $this->monthForRb(date_modify($m_moin_trois, "-3 month"), $debutBoucl);

                        $m_moin_quatre = clone $day;
                        $m_moin_quatre = $this->monthForRb(date_modify($m_moin_quatre, "-4 month"), $debutBoucl);
                    }
                    $month = $value->mois;
                    $tab_month = explode(',', $month);
                    if ($value->status === 0) {
                        if (!isset($tab_abc_tot[$value->id_compt])) {
                            $tab_abc_tot[$value->id_compt]['id'] = $value->id_compt;
                            $cpt_abstot++;
                        }
                    }
                    if ($value->status === 1) {
                        //--calcul for m
                        if ($affichM === true) {
                            $tab_m = explode(",", $m_now);
                            for ($i = count($tab_m) - 1; $i <= count($tab_m) - 1; $i++) {
                                $dateLimit = new \DateTime($tab_m[$i] . '-01');
                                $m_n = strpos($month, $tab_m[$i]);
                                if ($m_n === false) {
                                    if ($sitImage['numcomptbq'] == 1) {
                                        // echo('here in2');die();
                                        if (!isset($tab_cpt_m[$value->id_compt]) || !isset($tab_m_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                            $sitImage['casAffich'] = "m";
                                            for ($i = 0; $i <= count($tab_m) - 1; $i++) {
                                                $dateHere = new \DateTime($tab_m[$i] . '-01');
                                                if ($dateHere <= $dateLimit) {
                                                    $m_n = strpos($month, $tab_m[$i]);
                                                    if ($m_n === false) {
                                                        $test = true;
                                                    } elseif ($m_n >= 0) {
                                                        $incpl_m = true;
                                                        if (!isset($tab_m_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                            $tab_m_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                            // $cpt_incpl++;
                                                        }
                                                    }
                                                }
                                                if ($i == count($tab_m) - 1) {
                                                    break;
                                                }
                                            }
                                            if (!isset($tab_m_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                $tab_cpt_m[$value->id_compt]['id'] = $value->id_compt;
                                                if (!isset($tab_a_jour[$value->id_compt])) {
                                                    $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                                    $cpt_m++;
                                                }
                                            }
                                        }
                                        break;
                                        // continue;
                                    } else {
                                        for ($i = 0; $i <= count($tab_m) - 1; $i++) {
                                            $dateHere = new \DateTime($tab_m[$i] . '-01');
                                            if ($dateHere < $dateLimit) {
                                                $m_n = strpos($month, $tab_m[$i]);
                                                if ($m_n === false) {
                                                    $test = true;
                                                } else {
                                                    $incpl_m = true;
                                                    if (!isset($tab_m_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                        $tab_m_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                        //$cpt_incpl++;
                                                    }
                                                }
                                            }
                                            if ($i == count($tab_m) - 1) {
                                                break;
                                            }
                                        }
                                        if (!isset($tab_cpt_m[$value->id_compt]) && !isset($tab_m_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                            $tab_cpt_m[$value->id_compt]['id'] = $value->id_compt;
                                            if (!isset($tab_a_jour[$value->id_compt])) {
                                                $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                                $cpt_m++;
                                            }
                                        }
                                    }
                                } elseif ($m_n >= 0) {
                                    $incpl_m = true;
                                    if (!isset($tab_cpt_m[$value->id_compt]) && !isset($tab_m_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $tab_cpt_m[$value->id_compt]['id'] = $value->id_compt;
                                        // $cpt_m++;
                                    }
                                }
                                break;
                            }
                        }

                        //--calcul for m-1
                        $tab_m_un = explode(",", $m_moin_un);
                        for ($i = count($tab_m_un) - 1; $i <= count($tab_m_un) - 1; $i--) {
                            $dateLimit = new \DateTime($tab_m_un[$i] . '-01');
                            $m_un = strpos($month, $tab_m_un[$i]);
                            if ($m_un === false) {
                                //--verify incomplet by month if bq == 1
                                if ($sitImage['numcomptbq'] == 1) {
                                    if (!isset($tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $sitImage['casAffich'] = "mun";
                                        for ($i = 0; $i <= count($tab_m_un) - 1; $i++) {
                                            $dateHere = new \DateTime($tab_m_un[$i] . '-01');
                                            if ($dateHere <= $dateLimit) {
                                                $m_n = strpos($month, $tab_m_un[$i]);
                                                if ($m_n === false) {
                                                    $test = true;
                                                } else {
                                                    $incpl_m_un = true;
                                                    if (!isset($tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                        $tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                        // $cpt_incpl++;
                                                    }
                                                }
                                            }
                                            if ($i == count($tab_m_un) - 1) {
                                                break;
                                            }
                                        }
                                        if (!isset($tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                            $tab_cpt_m_un[$value->id_compt]['id'] = $value->id_compt;
                                            if (!isset($tab_a_jour[$value->id_compt])) {
                                                $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                                $cpt_un++;
                                            }
                                        }
                                    }
                                    break;
                                    // continue;
                                } else {
                                    //--verify incomplet by month
                                    for ($i = 0; $i <= count($tab_m_un) - 1; $i++) {
                                        $dateHere = new \DateTime($tab_m_un[$i] . '-01');
                                        if ($dateHere < $dateLimit) {
                                            $m_n = strpos($month, $tab_m_un[$i]);
                                            if ($m_n === false) {
                                                $test = true;
                                            } else {
                                                $incpl_m_un = true;
                                                if (!isset($tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                    $tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                    // $cpt_incpl++;
                                                }
                                            }
                                        }
                                        if ($i == count($tab_m_un) - 1) {
                                            break;
                                        }
                                    }
                                    if (!isset($tab_cpt_m_un[$value->id_compt]) && !isset($tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $tab_cpt_m_un[$value->id_compt]['id'] = $value->id_compt;
                                        if (!isset($tab_a_jour[$value->id_compt])) {
                                            $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                            $cpt_un++;
                                        }
                                    }
                                }
                            } elseif ($m_un >= 0) {
                                $incpl_m_un = true;
                                if (!isset($tab_cpt_m_un[$value->id_compt]) && !isset($tab_m_un_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                    $tab_cpt_m_un[$value->id_compt]['id'] = $value->id_compt;
                                    // $cpt_un++;
                                }
                            }
                            break;
                        }
                        //--calcul for m-2
                        $tab_m_deux = explode(",", $m_moin_deux);
                        for ($i = count($tab_m_deux) - 1; $i <= count($tab_m_deux) - 1; $i--) {
                            $dateLimit = new \DateTime($tab_m_deux[$i] . '-01');
                            $m_deux = strpos($month, $tab_m_deux[$i]);
                            if ($m_deux === false) {
                                if ($sitImage['numcomptbq'] == 1) {
                                    if (!isset($tab_cpt_m_deux[$value->id_compt]) && !isset($tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $sitImage['casAffich'] = "mdeux";
                                        for ($k = 0; $k <= count($tab_m_deux) - 1; $k++) {
                                            $dateHere = new \DateTime($tab_m_deux[$k] . '-01');
                                            if ($dateHere <= $dateLimit) {
                                                $m_n = strpos($month, $tab_m_deux[$k]);
                                                if ($m_n === false) {
                                                    $test = true;
                                                } else {
                                                    $incpl_m_deux = true;
                                                    if (!isset($tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                        $tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                        // $cpt_incpl++;
                                                    }
                                                }
                                            }
                                            if ($k == count($tab_m_deux) - 1) {
                                                break;
                                            }
                                        }
                                        if (!isset($tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                            $tab_cpt_m_deux[$value->id_compt]['id'] = $value->id_compt;
                                            if (!isset($tab_a_jour[$value->id_compt])) {
                                                $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                                $cpt_deux++;
                                            }
                                        }

                                    }
                                    break;
                                    // continue;
                                } else {
                                    for ($k = 0; $k <= count($tab_m_deux) - 1; $k++) {
                                        $dateHere = new \DateTime($tab_m_deux[$k] . '-01');
                                        if ($dateHere <= $dateLimit) {
                                            $m_n = strpos($month, $tab_m_deux[$k]);
                                            if ($m_n === false) {
                                                $test = true;
                                            } else {
                                                $incpl_m_deux = true;
                                                if (!isset($tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                    $tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                    // $cpt_incpl++;
                                                }
                                            }
                                        }
                                        if ($k == count($tab_m_deux) - 1) {
                                            break;
                                        }
                                    }
                                    if (!isset($tab_cpt_m_deux[$value->id_compt]) && !isset($tab_m_deux_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $tab_cpt_m_deux[$value->id_compt]['id'] = $value->id_compt;
                                        if (!isset($tab_a_jour[$value->id_compt])) {
                                            $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                            $cpt_deux++;
                                        }
                                    }
                                }
                            } elseif ($m_deux >= 0) {
                                $incpl_m_deux = true;
                            }

                            break;
                        }

                        //--calcul for m-3
                        $tab_m_trois = explode(",", $m_moin_trois);
                        for ($i = count($tab_m_trois) - 1; $i <= count($tab_m_trois) - 1; $i--) {
                            $dateLimit = new \DateTime($tab_m_trois[$i] . '-01');
                            $m_trois = strpos($month, $tab_m_trois[$i]);
                            if ($m_trois === false) {
                                if ($sitImage['numcomptbq'] == 1) {
                                    if (!isset($tab_cpt_m_trois[$value->id_compt]) && !isset($tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $sitImage['casAffich'] = "mtrois";
                                        for ($k = 0; $k <= count($tab_m_trois) - 1; $k++) {
                                            $dateHere = new \DateTime($tab_m_trois[$k] . '-01');
                                            if ($dateHere <= $dateLimit) {
                                                $m_n = strpos($month, $tab_m_trois[$k]);
                                                if ($m_n === false) {
                                                    $test = true;
                                                } elseif ($m_n >= 0) {
                                                    //echo('here te');die();
                                                    $incpl_m_trois = true;
                                                    if (!isset($tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                        $tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                        // $cpt_incpl++;
                                                    }
                                                }
                                            }
                                            if ($k == count($tab_m_trois) - 1) {
                                                break;
                                            }
                                        }
                                        if (!isset($tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                            $tab_cpt_m_trois[$value->id_compt]['id'] = $value->id_compt;
                                            if (!isset($tab_a_jour[$value->id_compt])) {
                                                $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                                $cpt_trois++;
                                            }
                                        }
                                    }
                                    break;
                                    // continue;
                                } else {
                                    for ($k = 0; $k <= count($tab_m_trois) - 1; $k++) {
                                        $dateHere = new \DateTime($tab_m_trois[$k] . '-01');
                                        if ($dateHere <= $dateLimit) {
                                            $m_n = strpos($month, $tab_m_trois[$k]);
                                            if ($m_n === false) {
                                                $test = true;
                                            } else {
                                                $incpl_m_trois = true;
                                                if (!isset($tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                    $tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                    // $cpt_incpl++;
                                                }
                                            }
                                        }
                                        if ($k == count($tab_m_trois) - 1) {
                                            break;
                                        }
                                    }
                                    if (!isset($tab_cpt_m_trois[$value->id_compt]) && !isset($tab_m_trois_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $tab_cpt_m_trois[$value->id_compt]['id'] = $value->id_compt;
                                        if (!isset($tab_a_jour[$value->id_compt])) {
                                            $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                            $cpt_trois++;
                                        }
                                    }
                                }
                            } elseif ($m_trois >= 0) {
                                $incpl_m_trois = true;
                            }
                            break;
                        }
                        //--calcul for m-4
                        $tab_m_quatre = explode(",", $m_moin_quatre);
                        for ($i = count($tab_m_quatre) - 1; $i <= count($tab_m_quatre) - 1; $i--) {
                            $dateLimit = new \DateTime($tab_m_quatre[$i] . '-01');
                            $m_quatre = strpos($month, $tab_m_quatre[$i]);
                            if ($m_quatre === false) {
                                if ($sitImage['numcomptbq'] == 1) {
                                    if (!isset($tab_cpt_m_quatre[$value->id_compt]) && !isset($tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $sitImage['casAffich'] = "mquatre";
                                        for ($k = 0; $k <= count($tab_m_quatre) - 1; $k++) {
                                            $dateHere = new \DateTime($tab_m_quatre[$k] . '-01');
                                            if ($dateHere <= $dateLimit) {
                                                $m_n = strpos($month, $tab_m_quatre[$k]);
                                                if ($m_n === false) {
                                                    $test = true;
                                                } else {
                                                    $incpl_m_quatre = true;
                                                    if (!isset($tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                        $tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                        // $cpt_incpl++;
                                                    }
                                                }
                                            }
                                            if ($k == count($tab_m_quatre) - 1) {
                                                break;
                                            }
                                        }
                                        if (!isset($tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                            $tab_cpt_m_quatre[$value->id_compt]['id'] = $value->id_compt;
                                            if (!isset($tab_a_jour[$value->id_compt])) {
                                                $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                                $cpt_quatre++;
                                            }
                                        }
                                    }
                                    break;
                                    // continue;
                                } else {
                                    for ($i = 0; $i <= count($tab_m_quatre) - 1; $i++) {
                                        $dateHere = new \DateTime($tab_m_quatre[$i] . '-01');
                                        if ($dateHere <= $dateLimit) {
                                            $m_n = strpos($month, $tab_m_quatre[$i]);
                                            if ($m_n === false) {
                                                $test = true;
                                            } else {
                                                $incpl_m_quatre = true;
                                                if (!isset($tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                                    $tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt]['id'] = $value->id_compt;
                                                    // $cpt_incpl++;
                                                }
                                            }
                                        }
                                        if ($i == count($tab_m_quatre) - 1) {
                                            break;
                                        }
                                    }
                                    if (!isset($tab_cpt_m_quatre[$value->id_compt]) && !isset($tab_m_quatre_incpl[$param['exercice'] . '-' . $value->id_compt])) {
                                        $tab_cpt_m_quatre[$value->id_compt]['id'] = $value->id_compt;
                                        if (!isset($tab_a_jour[$value->id_compt])) {
                                            $tab_a_jour[$value->id_compt]['id'] = $value->id_compt;
                                            $cpt_quatre++;
                                        }
                                    }
                                }
                            } elseif ($m_quatre >= 0) {
                                $incpl_m_quatre = true;
                            }
                            break;
                        }
                    }
                    if ($affichM) {
                        if ($incpl_m && $incpl_m_un && $incpl_m_deux && $incpl_m_trois && $incpl_m_quatre) {
                            $cpt_incpl++;
                        } elseif ($incpl_m || $incpl_m_un || $incpl_m_deux || $incpl_m_trois || $incpl_m_quatre) {
                            $test = true;
                        }
                    } else {
                        if ($incpl_m_un && $incpl_m_deux && $incpl_m_trois && $incpl_m_quatre) {
                            $cpt_incpl++;
                        } elseif ($incpl_m_un || $incpl_m_deux || $incpl_m_trois || $incpl_m_quatre) {
                            $test = true;
                        }
                    }
                    // echo('out end');die();
                }

                // var_dump($innumcompte);die();
                if ($affichM) {
                    $queryComplet = "SELECT D.id, banque_compte_id AS id_compt, R.mois
                        FROM releve_complet R
                        INNER JOIN dossier D ON (R.dossier_id = D.id)  
                        WHERE banque_compte_id IN (" . $innumcompte . ") AND R.exercice = " . $param['exercice'] . " ";
                    $prepComplet = $pdo->prepare($queryComplet);
                    $prepComplet->execute();
                    $resComplet = $prepComplet->fetchAll();

                    if ($resComplet) {
                        foreach ($resComplet as $index => $val) {
                            if (!isset($tab_cpt_m[$val->id_compt]) && !isset($tab_cpt_m_un[$val->id_compt]) && !isset($tab_cpt_m_deux[$val->id_compt]) && !isset($tab_cpt_m_trois[$val->id_compt]) && !isset($tab_cpt_m_quatre[$val->id_compt])) {
                                $tab_cpt_m[$val->id_compt]['id'] = $val->id_compt;
                                $cpt_m++;
                            }
                        }
                    }
                }

                if ($sitImage['numcomptbq'] > 1) {
                    $sitImage['casAffich'] = "tous";
                }
                $sitImage['affichM'] = $affichM;
                if (!isset($sitImage['casAffich'])) {
                    $sitImage['casAffich'] = "tous";
                }

                $sitImage['m_moin_un'] = $cpt_un;
                $sitImage['m_moin_deux'] = $cpt_deux;
                $sitImage['m_moin_trois'] = $cpt_trois;
                $sitImage['m_moin_quatre'] = $cpt_quatre;
                if ($affichM) {
                    $sitImage['m'] = $cpt_m;
                    if ($sitImage['numcomptbq'] == 1) {
                        if ($sitImage['m'] != 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "m";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] != 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "mun";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] != 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "mdeux";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "mtrois";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mquatre";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mtrois";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] != 0 && $sitImage['m_moin_deux'] != 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mun";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] != 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mdeux";
                        }
                        /*var_dump($sitImage['casAffich']);
                            var_dump($cpt_m);
                            var_dump($cpt_un);
                            var_dump($cpt_deux);
                            var_dump($cpt_trois);
                            var_dump($cpt_quatre);
                            echo('here 1');die();*/
                    } else {
                        if ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mquatre";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "mtrois";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] != 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "mdeux";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] != 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "mun";
                        } elseif ($sitImage['m'] != 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "m";
                        } elseif ($sitImage['m'] != 0 && $sitImage['m_moin_un'] != 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                            $sitImage['casAffich'] = "metun";
                        } elseif ($sitImage['m'] == 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mtrqu";
                        } elseif ($sitImage['m'] != 0 && $sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] != 0) {
                            $sitImage['casAffich'] = "mqu";
                        }
                        // echo('here 2');die();
                    }

                } else {
                    if ($sitImage['m_moin_un'] != 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                        $sitImage['casAffich'] = "mun";
                    } elseif ($sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] != 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] == 0) {
                        $sitImage['casAffich'] = "mdeux";
                    } elseif ($sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] != 0 && $sitImage['m_moin_quatre'] == 0) {
                        $sitImage['casAffich'] = "mtrois";
                    } elseif ($sitImage['m_moin_un'] == 0 && $sitImage['m_moin_deux'] == 0 && $sitImage['m_moin_trois'] == 0 && $sitImage['m_moin_quatre'] != 0) {
                        $sitImage['casAffich'] = "mquatre";
                    }
                }

                $sitImage['incomplet'] = $cpt_incpl;
                if ($param['dossier'] == 0) {
                    $sitImage['incomplet'] = ($sitImage['numcomptbq'] - ($cpt_m + $cpt_un + $cpt_deux + $cpt_trois + $cpt_quatre)) - $cpt_abstot;
                }
                $sitImage['abcencetot'] = $cpt_abstot;
            } else {
                // echo('here in ');die();
                $queryComplet = "SELECT D.id, banque_compte_id AS id_compt, R.mois
                        FROM releve_complet R
                        INNER JOIN dossier D ON (R.dossier_id = D.id)  
                        WHERE banque_compte_id IN (" . $innumcompte . ") AND R.exercice = " . $param['exercice'] . " ";
                $prepComplet = $pdo->prepare($queryComplet);
                $prepComplet->execute();
                $resComplet = $prepComplet->fetchAll();

                if ($resComplet) {
                    foreach ($resComplet as $index => $val) {
                        if (!isset($tab_cpt_m[$val->id_compt]) && !isset($tab_cpt_m_un[$val->id_compt]) && !isset($tab_cpt_m_deux[$val->id_compt]) && !isset($tab_cpt_m_trois[$val->id_compt]) && !isset($tab_cpt_m_quatre[$val->id_compt])) {
                            $tab_cpt_m[$val->id_compt]['id'] = $val->id_compt;
                            $cpt_m++;
                        }
                    }
                }
                $sitImage['affichM'] = true;
                $sitImage['casAffich'] = "m";
                $sitImage['m'] = $cpt_m;
                $sitImage['incomplet'] = $cpt_incpl;
                $sitImage['abcencetot'] = $cpt_abstot;
            }

            //-- test regex pcre
            /*$phone = "+261 34 29.865-04";
                //-- $phone = "0153789999";
                $regex = "/^(0|\+261)[-. ]?34([-. ][0-9]{2}){1}([-. ][0-9]{3}){1}([-. ][0-9]{2}){1}$/";
                //$regex = "/^0[1-68][0-9]{8}$/";
                if(preg_match($regex, $phone)) {
                    echo('true');die();
                }else{
                    echo('false');die();
                }*/
            //-- test entry
            /*$email = "manassSe@scriptur11a.biz";
                $regex = "/^[a-zA-Z0-9-_.]{2,}+@[a-z0-9-_]{2,}\.[a-z]{3,4}/";
                if(preg_match($regex, $email)) {
                    echo('true');die();
                }else{
                    echo('false');die();
                }*/
            //-- test site
            /*$site = "https://www.maNasse.jk";
                $regex = "/^(http|ftp)(s)?:(\/\/)(w{3})\.?([a-zA-Z0-9]{2,})+\.+([a-z]{2,4})$/";
                if (preg_match($regex, $site)) {
                    echo('true');die();
                }else{
                    echo('false');die();
                }

                echo('out'); die();*/

            return $this->render('BanqueBundle:Banque:tab_situat_image.html.twig', array(
                'data' => $sitImage
            ));

            $status = array();
            /** @var OperateurUtilisateur $verifOpera */

            // return new JsonResponse($status);

            /* } catch ( \Exception $ex) {
                $status = array(
                    'erreur' => true,
                    'erreur_text' => $ex->getMessage(),
                );
                return new JsonResponse(json_encode($status));
            }*/
        }
        throw new BadRequestHttpException("Method not allowed.");
    }

    /**
     * Function for month of m
     * @param $day
     * @param $debutBoucl
     * @return string
     */
    public function monthForRb($day, $debutBoucl)
    {
        $r = (int)($day->format('m'));
        $m_now = "";
        $i = (int)substr($debutBoucl, 5, 2);
        $anneeBoucl = substr($debutBoucl, 0, 4);
        $first = true;
        for ($i; $i <= $r; $i++) {
            if ($i == 01) {
                $m_now = $anneeBoucl . '-0' . $i;
            } else {
                if ($i <= 9) {
                    if ($i == $r && $first) {
                        $m_now .= "" . $anneeBoucl . '-0' . (int)$i;
                        break;
                    } elseif ($i == $r && !$first) {
                        $m_now .= "," . $anneeBoucl . '-0' . (int)$i;
                    } else {
                        if ($first) {
                            $m_now .= $anneeBoucl . '-0' . (int)$i;
                        } else {
                            $m_now .= "," . $anneeBoucl . '-0' . (int)$i;
                        }
                    }
                } else {
                    if ($i == $r && $first) {
                        $m_now .= "" . $anneeBoucl . '-' . (int)$i;
                        break;
                    } elseif ($i == $r && !$first) {
                        $m_now .= "," . $anneeBoucl . '-' . (int)$i;
                    } else {
                        if ($first) {
                            $m_now .= $anneeBoucl . '-' . (int)$i;
                        } else {
                            $m_now .= "," . $anneeBoucl . '-' . (int)$i;
                        }
                    }
                    //$m_now .= ",".$anneeBoucl.'-'.(int)$i;
                }
            }
            $first = false;
        }
        return $m_now;
    }

    public function saisieAction()
    {
        /** @var Operateur $user */
        $user = $this->getUser();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
        $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");
        $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
        $clients = $query->getResult();

        $query = "SELECT id,nom  FROM banque2 ORDER BY nom";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $data['banques'] = $datas;

//        $icategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getCategories();
//        $isouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSousCategoriesBanque();

        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find(16);

        $isouscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->findBy(array('categorie' => $categorie, 'actif' => 1), array('libelleNew' => 'ASC'));


//        $isoussouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSoussousCategoriesBanque();

        $isoussouscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->findBy(array(
                'souscategorie' => $this->getDoctrine()->getRepository('AppBundle:Souscategorie')->find(10),
                'actif' => 1), array('libelleNew' => 'ASC'));

        return $this->render('BanqueBundle:Banque/saisie:banque_saisie.html.twig', array(
            'data' => $data,
            'clients' => $clients,
//            'icategorie'=>$icategorie,
            'isouscategorie' => $isouscategorie,
            'isoussouscategorie' => $isoussouscategorie
        ));
    }

    public function controleRbAction()
    {
        /** @var Operateur $user */
        $user = $this->getUser();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
        $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");
        $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
        $clients = $query->getResult();

        $query = "SELECT id,nom  FROM banque2 ORDER BY nom";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $data['banques'] = $datas;

        $icategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getCategories();
        $isouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSousCategoriesBanque();
        $isoussouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSoussousCategoriesBanque();

        return $this->render('BanqueBundle:Banque/saisie:banque_controle_rb.html.twig', array(
            'data' => $data,
            'clients' => $clients,
            'icategorie' => $icategorie,
            'isouscategorie' => $isouscategorie,
            'isoussouscategorie' => $isoussouscategorie
        ));
    }

    public function controleObAction()
    {
        /** @var Operateur $user */
        $user = $this->getUser();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
        $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");
        $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
        $clients = $query->getResult();

        $query = "SELECT id,nom  FROM banque2 ORDER BY nom";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $data['banques'] = $datas;

        $icategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getCategories();
        $isouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSousCategoriesBanque();
        $isoussouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSoussousCategoriesBanque();

        return $this->render('BanqueBundle:Banque/saisie:banque_controle_ob.html.twig', array(
            'data' => $data,
            'clients' => $clients,
            'icategorie' => $icategorie,
            'isouscategorie' => $isouscategorie,
            'isoussouscategorie' => $isoussouscategorie
        ));
    }

    public function exerciceAction(Request $request)
    {
        $dossierid = $request->query->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $exercices = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getExercicesByDossier($dossier);

        return $this->render('@Banque/Banque/exerciceValue.html.twig', array('exercices' => $exercices));

    }

    public function listeBanqueAction(Request $request)
    {
        $dossierid = $request->query->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $banquecomptes = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->findBy(array('dossier' => $dossier));
        $banques = [];

        foreach ($banquecomptes as $banqueCompte) {
            if (!in_array($banqueCompte->getBanque(), $banques))
                $banques [] = $banqueCompte->getBanque();
        }

        return $this->render('@Banque/Banque/banqueList.html.twig', array('banques' => $banques));
    }

    public function listeBanqueCompteAction(Request $request, $saisie, $iban)
    {
        $dossierid = $request->query->get('dossierid');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $banqueComptes = [];

        if ($request->query->get('banqueid')) {
            $banqueid = $request->query->get('banqueid');
            $banque = $this->getDoctrine()
                ->getRepository('AppBundle:Banque')
                ->find($banqueid);

            if ($dossier && $banque) {
                /** @var BanqueCompte[] $banqueComptes */
                $banqueComptes = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->findBy(array('dossier' => $dossier, 'banque' => $banque));
            }
        } else {
            if ($dossier) {
                /** @var BanqueCompte[] $banqueComptes */
                $banqueComptes = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->findBy(['dossier' => $dossier]);
            }
        }


        return $this->render('@Banque/Banque/banqueCompteList.html.twig',
            [
                'banquecomptes' => $banqueComptes,
                'saisie' => $saisie,
                'iban' => $iban
            ]);
    }



    public function getDateCloture($ims, $clot)
    {
        $query = "SELECT *  FROM releve 
                   WHERE date_releve <='" . $clot . "' 
                   AND image_id IN " . $ims . " ORDER BY date_releve DESC";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $date = '';
        foreach ($datas as $data) {
            $date = $data['date_releve'];
            break;
        }
        return $date;
    }

    public function getDateOuverture($ims)
    {
        $query = "SELECT *  FROM releve 
                   WHERE date_releve 
                   AND image_id IN " . $ims . " ORDER BY date_releve DESC";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $date = '';
        foreach ($datas as $data) {
            $date = $data['date_releve'];
            break;
        }
        return $date;
    }

    public function getSoldeCloture($solde, $ims, $clot)
    {
        $query = "SELECT *  FROM releve 
                   WHERE date_releve <='" . $clot . "' 
                   AND image_id IN " . $ims . " ORDER BY date_releve";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($datas as $data) {
            $solde = $solde - $data['debit'] + $data['credit'];
        }
        return $solde;
    }

    public function getTotalReleve($id, $debut)
    {
        $query = "SELECT *  FROM releve WHERE image_id =" . $id;
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $solde = 0;
        foreach ($datas as $data) {
            $s = $data['credit'] - $data['debit'];
            $solde = $solde + $s;
        }
        return $debut + $solde;
    }

    public function getTotalRelevee($id)
    {
        $query = "SELECT *  FROM releve WHERE image_id =" . $id;
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $solde = 0;
        foreach ($datas as $data) {
            $s = $data['credit'] - $data['debit'];
            $solde = $solde + $s;
        }
        return $solde;
    }

    public function getPanierAction(Request $request)
    {

        /** @var Operateur $operateur */
        $operateur = $this->getUser();

        $get = $request->query;

        $souscat = $get->get('souscat');
//        $soussouscat = $get->get('soussouscat');
//        $soussouscataddId = $get->get('soussouscatadd');
        $souscategorie = null;

        $etape = $get->get('etape');

        $soussouscategorie = null;
        $soussouscategorieadd = null;

        if ($souscat !== '') {

            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscat);

//            if($soussouscat !== null) {
//                $soussouscategorie = $this->getDoctrine()
//                    ->getRepository('AppBundle:Soussouscategorie')
//                    ->find($soussouscat);
//            }
        }

//        if($soussouscataddId !== null && $soussouscataddId !== ''){
//            $soussouscategorieadd = $this->getDoctrine()
//                ->getRepository('AppBundle:Soussouscategorie')
//                ->find($soussouscataddId);
//        }

        if($etape !== 'BQ_DETAILS') {
            $panierTemps = $this->getDoctrine()
                ->getRepository('AppBundle:Panier')
                ->getPanierBanque(null,
                    $operateur->getId(),
                    ($souscat === '') ? null : $souscategorie->getId(),
                    ($soussouscategorie === null) ? null : $soussouscategorie->getId(),
                    $etape
                );

            $panierAdds = [];

//            if($soussouscategorieadd !== null){
//                $panierAdds = $this->getDoctrine()
//                    ->getRepository('AppBundle:Panier')
//                    ->getPanierBanque(null,
//                        $operateur->getId(),
//                        $soussouscategorieadd->getSouscategorie()->getId(),
//                        $soussouscategorieadd->getId(),
//                        $etape
//                    );
//            }

            $paniers = array_merge($panierTemps, $panierAdds);
        }
        else{
            $paniers = $this->getDoctrine()
                ->getRepository('AppBundle:Panier')
                ->getPanierSaisie2Banque(2019, $this->getUser()->getId());
        }

        return $this->render('BanqueBundle:Banque/saisie:liste_lot_saisie.html.twig', array('paniers' => $paniers));

    }

    public function getPanierLotAction(Request $request)
    {
        $souscat = $request->request->get('souscat');
        $etape = $request->request->get('etape');
        $user = $this->getUser();
        $query = "SELECT lot_id FROM panier_reception WHERE operateur_id =" . $user->getId();
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $lots = array();
        $lots[] = 0;
        foreach ($datas as $d) {
            $lots[] = $d['lot_id'];
        }
        $lot = implode(",", $lots);
        if ($etape == 1) {
            $where = "AND I.status IN (0,3)";
        } else {
            $where = "AND I.status IN (3,4)";
        }
        //releve bancaire
        if ($souscat == 10 || $souscat == 7) {
            $query = "SELECT I.* FROM  image I, lot L,dossier D,separation S,categorie C, souscategorie SC
                    WHERE  I.lot_id = L.id
                    AND L.dossier_id = D.id
                    AND S.image_id = I.id
                    AND S.categorie_id = C.id
                    AND S.souscategorie_id = SC.id
                    AND I.lot_id IN (" . $lot . ") " . $where . " ORDER BY I.nom DESC";
        }
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $lignes = '';
        $data = array();
        $nombre = sizeof($datas);
        foreach ($datas as $im) {
            $checked = '';
            if ($etape == 1 && $im['status'] == 3) {
                $checked = 'checked';
            }
            if ($im['status'] == 3) {
                $bgc = 'style="background-color:#FFD966" data-id="1"';
                $t = 1;
                $color = "#FFD966";
            } else if ($im['status'] == 4) {
                $bgc = 'style="background-color:#e2efda" data-id="2"';
                $t = 2;
                $color = "#e2efda";
                $checked = 'checked';
            } else {
                $bgc = 'data-id="0"';
                $t = 0;
                $color = "#fff2cc";
            }
            $lignes .= '<tr><td align="right" bgcolor="' . $color . '" style="border:none;"><span id="' . $im['id'] . '" class="js_imgbq_selected"' . $bgc . ' >' . $im['nom'] . '</span></td>
                        <td align="left" bgcolor="' . $color . '" style="border:none;"><input type="checkbox" id="c' . $im['id'] . '" ' . $checked . ' /></td></tr>';
        }
        $data["l"] = $lignes;
        $data["n"] = $nombre;
        return new JsonResponse($data);
    }

    public function getPanierImageAction(Request $request)
    {
        $souscat = $request->request->get('souscat');
        $etape = $request->request->get('etape');
        $lot = $request->request->get('lot');

        $where = "";

//        if ($etape == 'OS_1'){
//            $where = " AND I.saisie1 = 0";
//        } else {
//            $where = " AND I.saisie1 > 1";
//        }

        //releve bancaire
        if ($souscat == 10 || $souscat == 7) {
            $query = " SELECT I.* FROM  image I, lot L,dossier D,separation S,categorie C, souscategorie SC
                    WHERE  I.lot_id = L.id
                    AND L.dossier_id = D.id
                    AND S.image_id = I.id
                    AND S.categorie_id = C.id
                    AND S.souscategorie_id = SC.id AND S.souscategorie_id = 10
                    AND I.lot_id = " . $lot . $where . " ORDER BY I.nom DESC";
        }
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $lignes = '';
        $data = array();
        $nombre = sizeof($datas);
        foreach ($datas as $im) {
            $checked = '';
            if ($etape == 1 && $im['status'] == 3) {
                $checked = 'checked';
            }
            if ($im['status'] == 3) {
                $bgc = 'style="background-color:#FFD966" data-id="1"';
                $t = 1;
                $color = "#FFD966";
            } else if ($im['status'] == 4) {
                $bgc = 'style="background-color:#e2efda" data-id="2"';
                $t = 2;
                $color = "#e2efda";
                $checked = 'checked';
            } else {
                $bgc = 'data-id="0"';
                $t = 0;
                $color = "#fff2cc";
            }
            $lignes .= '<tr><td align="right" bgcolor="' . $color . '" style="border:none;"><span id="' . $im['id'] . '" class="js_imgbq_selected"' . $bgc . ' >' . $im['nom'] . '</span></td>
                        <td align="left" bgcolor="' . $color . '" style="border:none;"><input type="checkbox" id="c' . $im['id'] . '" ' . $checked . ' /></td></tr>';
        }
        $data["l"] = $lignes;
        $data["n"] = $nombre;
        return new JsonResponse($data);
    }

    public function scAction()
    {

        /** @var Operateur $user */
        $user = $this->getUser();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
        $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $query = $query->andWhere('c = :client')->setParameter('client', $user->getClient());
        }
        $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
        $clients = $query->getResult();

        $query = "SELECT id,nom  FROM banque2 ORDER BY nom";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $data['banques'] = $datas;

        $icategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getCategories();
        $isouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSousCategoriesBanque();
        $isoussouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSoussousCategoriesBanque();

        return $this->render('BanqueBundle:Banque/saisie:banque_controle.html.twig', array(
            'data' => $data,
            'clients' => $clients,
            'icategorie' => $icategorie,
            'isouscategorie' => $isouscategorie,
            'isoussouscategorie' => $isoussouscategorie
        ));
    }

    public function showBanqueAction(Request $request)
    {
        $dossier = $request->request->get('dossier');
        $query = "SELECT BC.numcompte  FROM banque B,banque_compte BC 
                  WHERE BC.banque_id =B.id 
                  AND BC.dossier_id=" . $dossier . "
                  ORDER BY BC.numcompte";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $i = 0;

        $data = [];
        foreach ($datas as $banque) {
            $data[$i]['name'] = substr($banque['numcompte'], 0, 21);
            $i++;
        }
        return new JsonResponse($data);
    }

    public function nomBanqueAction(Request $request)
    {
        $data = array();
        $num = $request->request->get('num');
        $query = "SELECT BB.id,BC.numcompte FROM banque BB,  banque_compte BC        
                  WHERE SUBSTRING(BC.numcompte,1,5) = BB.codebanque     
                  AND BC.numcompte like '%" . $num . "%'";
        $prep = $this->pdo->query($query);
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($datas as $b) {
            $data['id'] = $b['id'];
            $data['compte'] = substr($b['numcompte'], 0, 21);
            $data['cle'] = substr($b['numcompte'], -2, 2);
            break;
        }
        return new JsonResponse($data);
    }

    public function detBanqueAction(Request $request)
    {
        $data = array();
        $id = $request->request->get('id');
        $repository = $this->getDoctrine()->getRepository('AppBundle:BanqueCompte');
        $query = $repository->createQueryBuilder('bc');
        $query->leftJoin('bc.banque', 'b');
        $query->addSelect('b');
        $query->where("b.id = :id");
        $query->setParameter('id', $id);
        $query = $query->getQuery();
        $banques = $query->getResult();
        foreach ($banques as $banque) {
            $data['iban'] = $banque->getIban();
            $data['name'] = $banque->getBanque()->getNom();
            $data['code'] = $banque->getBanque()->getCodebanque();
            $data['num'] = substr($banque->getNumcompte(), 0, 21);
            $data['cle'] = substr($banque->getNumcompte(), -2, 2);
        }
        return new JsonResponse($data);
    }

    public function getNomCompte($id)
    {
        if ($id > 0) {
            $query = "SELECT * FROM  tiers WHERE  id =" . $id;
            $prep = $this->pdo->query($query);
            $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
            if (isset($datas[0])) {
                $compte = $datas[0]['compte_str'];
            }
            return $compte;
        } else {
            return "";
        }
    }

    public function getNomPcc($id, $i)
    {
        if ($id > 0) {
            $query = "SELECT * FROM  pcc WHERE  id =" . $id;
            $prep = $this->pdo->query($query);
            $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
            if ($i == 0) {
                if (isset($datas[0])) {
                    $compte = $datas[0]['compte'];
                }
            } else {
                if (isset($datas[0])) {
                    $compte = $datas[0]['intitule'];
                }
            }
            return $compte;
        } else {
            return "";
        }
    }

    public function setCompteAction(Request $request)
    {
        $lid = $request->request->get('id');
        $compte = $request->request->get('compte');
        $compte = explode('#', $compte[0]);
        if ($compte[0] == 0) {
            $query = "SELECT * FROM  pcc WHERE  id =" . $compte[1];
            $prep = $this->pdo->query($query);
            $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
            if (isset($datas[0])) {
                $compte = substr($datas[0]['compte'], 0, 6);
                $query = 'UPDATE banque_sous_categorie_autre SET compte_chg_id="' . $datas[0]['id'] . '" WHERE id=' . $lid;
                $this->pdo->exec($query);
            }
        } else {
            $query = "SELECT * FROM  tiers WHERE  id =" . $compte[1];
            $prep = $this->pdo->query($query);
            $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
            if (isset($datas[0])) {
                $compte = $datas[0]['compte_str'];
                $query = 'UPDATE banque_sous_categorie_autre SET compte_tiers_id="' . $datas[0]['id'] . '" WHERE id=' . $lid;
                $this->pdo->exec($query);
            }
        }
        return new JsonResponse($compte);
    }

    //pcc
    public function pcgPccAction(Request $request)
    {
        $pcgs = array();
        $doss = $request->request->get('dossier');
        $pcg = $request->request->get('pcg');
        $pcgs[] = $this->getDoctrine()->getRepository('AppBundle:Pcg')->find($pcg);
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($doss);
        $pccs = $this->getDoctrine()->getRepository('AppBundle:Pcc')->getPCCByPCG($pcgs, $dossier, [], $pcgs, false);
        $pcgsChilds = [];
        $pcgsParents = [];
        $pcgsObjects = [];
        foreach ($pccs as $pcg) {
            $compte = $pcg->getCompte();
            $pcgsChilds[$compte] = [];
            $pcgsObjects[$compte] = (object)
            [
                'compte' => $compte,
                'intitule' => $pcg->getIntitule(),
                'id' => $pcg->getId(),
                't' => 0
            ];
            $parent = null;

            for ($i = strlen($compte) - 1; $i >= 0; $i--) {
                $key = substr($compte, 0, $i);
                if (array_key_exists($key, $pcgsChilds)) {
                    $pcgsChilds[$key][] = $compte;
                    $parent = $key;
                    break;
                }
            }

            if ($pcg->getCollectifTiers() != -1) {
                $tiers = $this->getDoctrine()->getRepository('AppBundle:Tiers')
                    ->createQueryBuilder('t')
                    ->where('t.pcc = :pcc')
                    ->setParameter('pcc', $pcg)
                    ->orderBy('t.intitule')
                    ->getQuery()
                    ->getResult();

                $existe = false;
                foreach ($tiers as $tier) {
                    //$tier = new Tiers();
                    $compteTiers = $tier->getCompteStr();
                    $pcgsChilds[$compteTiers] = [];
                    $pcgsChilds[$compte][] = $compteTiers;

                    $pcgsObjects[$compteTiers] = (object)
                    [
                        'compte' => $compteTiers,
                        'intitule' => $tier->getIntitule(),
                        'id' => $tier->getId(),
                        't' => 1
                    ];
                    $existe = true;
                }
            }

            if ($parent == null) $pcgsParents[] = $compte;
        }

        $results = [];
        foreach ($pcgsParents as $pcgsParent) {
            $results[] = functions::getTree($pcgsParent, $pcgsChilds, $pcgsObjects, array());
        }
        return new JsonResponse($results);
    }

    public function formatExl($d)
    {
        $d = explode('-', trim($d));
        if (sizeof($d) == 3) {
            if (strlen($d[2]) == 2) {
                $d[2] = "20" . $d[2];
            }
            return $d[2] . '-' . $d[0] . '-' . $d[1];
        } else {
            return '';
        }
    }

    public function formatAff($d)
    {
        $d = explode('-', $d);
        if (sizeof($d) == 3) {
            return $d[2] . '/' . $d[1] . '/' . $d[0];
        } else {
            return '';
        }
    }

    public function showImgAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $post = $request->request;
            $imgId = $post->get('imgid');
            $donneesSaisie = null;

            /** @var Image $image */
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imgId);

            $imageService = new ImageService($this->getDoctrine()->getManager());
            $url = $imageService->getUrl($image->getId());

            //Infoperdos
            $dossier = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getDossierImage($image->getId());
            $exercice = $image->getExercice();

            $g = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getGenerale($dossier);
            $m = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getMandataire($dossier);

            $data['generale'] = "<tr><td>R.S SociÃ©tÃ© :</td><td>" . $g['rs_ste'] . "</td></tr>";
            $data['generale'] .= "<tr><td>Siren|Siret :</td><td>" . $g['siren_ste'] . "</td></tr>";
            $data['generale'] .= "<tr><td>Num et rue :</td><td>" . $g['num_rue'] . "</td></tr>";
            $data['generale'] .= "<tr><td>Ville :</td><td>" . $g['ville'] . "</td></tr>";
            $data['generale'] .= "<tr><td>Code Postale :</td><td>" . $g['code_postal'] . "</td></tr>";
            $data['generale'] .= "<tr><td>Pays :</td><td>" . $g['pays'] . "</td></tr>";
            $data['generale'] .= "<tr><td>TÃ©l. SociÃ©tÃ© :</td><td>" . $g['rs_ste'] . "</td></tr>";

            $data['mandataire'] = "<tr><td>Nom Mandataire :</td><td>" . $m['nom'] . " " . $m['prenom'] . "</td></tr>";
            $data['mandataire'] .= "<tr><td>TÃ©l Mandataire :</td><td>" . $m['tel_portable'] . "</td></tr>";
            $data['mandataire'] .= "<tr><td>Mail Mandataire :</td><td>" . $m['email'] . "</td></tr>";


            $fisc = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getRegimeFiscal($dossier);
            $impo = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getRegimeImposition($dossier);
            $rtva = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getRegimeTva($dossier);
            $ttva = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getTypeTva($dossier);

            $ecriture = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getDateEcriture($dossier, $exercice);
            $dateCloture = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getRevDateCloture($dossier, $exercice);
            $dateCloture = $dateCloture->format('d-m-Y');
            $datePremier = new \DateTime($g['debut_activite']);
            $datePremier = $datePremier->format('d-m-Y');
            $te = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getTenue($dossier);
            $convention = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getConvention($dossier);

            $t = array();
            $t[0] = "";
            $t[1] = "Mensuelle";
            $t[2] = "Trimestrielle";
            $t[3] = "Semestrielle";
            $t[4] = "Annuelle";
            $t[5] = "Ponctuelle";
            $tenue = "";
            if (intval($te) > 0) {
                $tenue = $t[$te];
            }
            $instruction = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getInstructions($dossier);
            $data['isaisie'] = "";
            $data['idossier'] = "";
            if (isset($instruction[0])) {
                $data['isaisie'] = $instruction[0]['isaisie'];
                $data['idossier'] = $instruction[0]['idossier'];
            }

            $data['comptable'] = "<tr><td>Dates Ã©critures :</td><td>" . $ecriture . "</td></tr>";
            $data['comptable'] .= "<tr><td>Note de frais :</td><td>Saisie NdF sans justif</td></tr>";
            $data['comptable'] .= "<tr><td>PÃ©riodicitÃ© tenue :</td><td>" . $tenue . "</td></tr>";
            $data['comptable'] .= "<tr><td>Date dÃ©marrage 1er exercice :</td><td>" . $datePremier . "</td></tr>";
            $data['comptable'] .= "<tr><td>Date clÃ´ture :</td><td>" . $dateCloture . "</td></tr>";
            $data['comptable'] .= "<tr><td>Convention comptable :</td><td>" . $convention . "</td></tr>";
            $data['comptable'] .= "<tr><td>Compte Ã  globaliser :</td><td>607, XXX</td></tr>";

            $data['fiscale'] = "<tr><td>RÃ©gime fiscal :</td><td>" . $fisc . "</td></tr>";
            $data['fiscale'] .= "<tr><td>RÃ©gime imposition :</td><td>" . $impo . "</td></tr>";
            $data['fiscale'] .= "<tr><td>TVA :</td><td>" . $rtva . "</td></tr>";
            $data['fiscale'] .= "<tr><td>Date echÃ©ance :</td><td>" . $g['tva_date'] . "</td></tr>";
            $data['fiscale'] .= "<tr><td>Type TVA :</td><td>" . $ttva . "</td></tr>";
            $data['fiscale'] .= "<tr><td>Num Intracom :</td><td>...</td></tr>";
            $data['dossier'] = $dossier;
            //FIn info

            $filename = $this->get('kernel')->getRootDir().'/../web/ocr/'.$image->getNom().'.pdf';
            if (file_exists($filename)) {
                $data['pdf'] = '/ocr/' .$image->getNom().'.pdf';
            } else {
                $data['pdf'] = $url;
            }

            //recuperation entete remise
            $query = "SELECT * FROM banque_remise WHERE image_id=" . $imgId;
            $prep = $this->pdo->query($query);
            $datar = $prep->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($datar as $man) {
                $data['numc'] = $man['num_compte'];
                $data['num_remise'] = $man['num_remise'];
                $data['nombre_cheque'] = $man['nombre_cheque'];
                $data['total_cheque'] = $man['total_cheque'];
            }
            //recuperation entete releve
            $data['ddebit'] = 0;
            $data['dcredit'] = 0;
            $data['fdebit'] = 0;
            $data['fcredit'] = 0;
            $query = "SELECT B.*,BC.*,S.* FROM saisie_controle S, banque_compte BC,banque B
                WHERE S.banque_compte_id = BC.id
                AND BC.banque_id = B.id
                AND S.image_id=" . $imgId;
            $prep = $this->pdo->query($query);
            $datar = $prep->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($datar as $man) {
                //$data['montant_ttc']=$man['montant_ttc'];
                //recuperation entete lcr
                $data['date_reglement'] = $this->formatAff($man['date_reglement']);
                $data['date_echeance'] = $this->formatAff($man['date_echeance']);
                $data['totallcr'] = $man['montant_ttc'];
                $data['date_facture'] = $this->formatAff($man['date_facture']);
                $data['num_facture'] = $man['num_facture'];
                $data['nombreligne'] = $man['nbre_couvert'];
                $data['numcompte'] = $man['numcompte'];
                $data['num_releve'] = $man['num_releve'];
                if ($man['solde_debut'] > 0) {
                    $data['dcredit'] = $man['solde_debut'];
                } else {
                    $data['ddebit'] = $man['solde_debut'] * (-1);
                }
                if ($man['solde_fin'] > 0) {
                    $data['fcredit'] = $man['solde_fin'];
                } else {
                    $data['fdebit'] = $man['solde_fin'] * (-1);
                }
                $data['page_solde_debut'] = $man['page_solde_debut'];
                $data['page_solde_fin'] = $man['page_solde_fin'];
                if (strlen($man['periode_d1']) == 10) {
                    $data['debut_periode'] = $this->formatAff($man['periode_d1']);
                }
                if (strlen($man['periode_f1']) == 10) {
                    $data['fin_periode'] = $this->formatAff($man['periode_f1']);
                }
                $data['numc'] = substr($man['numcompte'], 0, 21);

                $data['totalccreleve'] = $man['montant_ttc'];
            }

            //$demo='<li><img data-original="/ocr/ES2F0J017.jpg" src="/ocr/ES2F0J017.jpg"></li>';
            //$txt =file_get_contents("D:\BOULOT_MAHARO\www\Intranet\web\ocr\ES2F0J017.txt");
            $data['ocr'] = $data['numc'];
            $data['demo'] = '';
            $data['imid'] = $imgId;
            //cat souscat soussouscat
            $data['infocat'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getInfosImage($imgId);
            return new JsonResponse($data);
        }
        throw new BadRequestHttpException("Method not allowed.");
    }

    public function showImgSaisieAction(Request $request)
    {


        if ($request->isXmlHttpRequest()) {
            $post = $request->request;
            $imgId = $post->get('imgid');
//            $souscat = $post->get('souscat');

            /** @var Image $image */
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imgId);

            $separation = $this->getDoctrine()
                ->getRepository('AppBundle:Separation')
                ->getSeparationByImage($image);

            $souscat = null;


            $donneesSaisie = null;

            /** @var Image $image */

            $imageService = new ImageService($this->getDoctrine()->getManager());
            $url = $imageService->getUrl($imgId);

            //Infoperdos
            $dossier = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getDossierImage($image->getId());

            /** @var Dossier $dossierEntity */
            $dossierEntity = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossier);

            /** @var ResponsableCsd $mandataire */
            $mandataire = $this->getDoctrine()
                ->getRepository('AppBundle:ResponsableCsd')
                ->getMandataire($dossierEntity);

            /** @var MethodeComptable $methodeComptable */
            $methodeComptable = $this->getDoctrine()
                ->getRepository('AppBundle:MethodeComptable')
                ->getMethodeComptableByDossier($dossierEntity);

            $exercice = $image->getExercice();

            $data['generale'] = "<tr><td>R.S SociÃ©tÃ© :</td><td>" . $dossierEntity->getRsSte() . "</td></tr>";
            $data['generale'] .= "<tr><td>Siren|Siret :</td><td>" . $dossierEntity->getSirenSte() . "</td></tr>";
            $data['generale'] .= "<tr><td>Num et rue :</td><td>" . $dossierEntity->getNumRue() . "</td></tr>";
            $data['generale'] .= "<tr><td>Ville :</td><td>" . $dossierEntity->getVille() . "</td></tr>";
            $data['generale'] .= "<tr><td>Code Postale :</td><td>" . $dossierEntity->getCodePostal() . "</td></tr>";
            $data['generale'] .= "<tr><td>Pays :</td><td>" . $dossierEntity->getPays() . "</td></tr>";
            $data['generale'] .= "<tr><td>TÃ©l. SociÃ©tÃ© :</td><td>" . $dossierEntity->getTelSte() . "</td></tr>";

            $data['mandataire'] = "<tr><td>Nom Mandataire :</td><td>" . ($mandataire === null) ? '' : $mandataire->getNom() . " " . $mandataire->getPrenom() . "</td></tr>";
            $data['mandataire'] .= "<tr><td>TÃ©l Mandataire :</td><td></td></tr>";
            $data['mandataire'] .= "<tr><td>Mail Mandataire :</td><td>" . ($mandataire === null) ? '' : $mandataire->getEmail() . "</td></tr>";

            $fisc = ($dossierEntity->getRegimeFiscal() === null) ? '' :
                $dossierEntity->getRegimeFiscal()->getLibelle();

            $impo = ($dossierEntity->getRegimeTva() === null) ? '' :
                $dossierEntity->getRegimeTva()->getLibelle();

            $rtva = ($dossierEntity->getRegimeTva() === null) ? '' :
                $dossierEntity->getRegimeTva()->getLibelle();

            $ttva = ($dossierEntity->getTvaType() === null) ? '' :
                $dossierEntity->getTvaType()->getLibelle();

            $historiqueUpload = $this->getDoctrine()
                ->getRepository('AppBundle:HistoriqueUpload')
                ->getLastUploadDossier($dossierEntity);

            $ecriture = '';

            if ($historiqueUpload !== null) {
                $ecriture = ($historiqueUpload->getDateUpload() === null) ? '' :
                    $historiqueUpload->getDateUpload()->format('d-m-Y');
            }

            $dateCloture = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossierEntity, $exercice);

            $dateCloture = $dateCloture->format('d-m-Y');

            $datePremier = ($dossierEntity->getDateCloture() === null) ? '' :
                $dossierEntity->getDateCloture()->format('d-m-Y');

            $te = 0;
            $convention = null;
            if ($methodeComptable) {
                $te = $methodeComptable->getTenueComptablilite();
                $convention = $methodeComptable->getConventionComptable();
            }

            $t = array();
            $t[0] = "";
            $t[1] = "Mensuelle";
            $t[2] = "Trimestrielle";
            $t[3] = "Semestrielle";
            $t[4] = "Annuelle";
            $t[5] = "Ponctuelle";
            $tenue = "";
            if (intval($te) > 0) {
                $tenue = $t[$te];
            }
            $instruction = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                ->getInstructions($dossier);
            $data['isaisie'] = "";
            $data['idossier'] = "";
            if (isset($instruction[0])) {
                $data['isaisie'] = $instruction[0]['isaisie'];
                $data['idossier'] = $instruction[0]['idossier'];
            }

            $data['comptable'] = "<tr><td>Dates Ã©critures :</td><td>" . $ecriture . "</td></tr>";
            $data['comptable'] .= "<tr><td>Note de frais :</td><td>Saisie NdF sans justif</td></tr>";
            $data['comptable'] .= "<tr><td>PÃ©riodicitÃ© tenue :</td><td>" . $tenue . "</td></tr>";
            $data['comptable'] .= "<tr><td>Date dÃ©marrage 1er exercice :</td><td>" . $datePremier . "</td></tr>";
            $data['comptable'] .= "<tr><td>Date clÃ´ture :</td><td>" . $dateCloture . "</td></tr>";
            $data['comptable'] .= "<tr><td>Convention comptable :</td><td>" . ($convention !== null) ? '' : $convention->getLibelle() . "</td></tr>";
            $data['comptable'] .= "<tr><td>Compte Ã  globaliser :</td><td>607, XXX</td></tr>";

            $data['fiscale'] = "<tr><td>RÃ©gime fiscal :</td><td>" . $fisc . "</td></tr>";
            $data['fiscale'] .= "<tr><td>RÃ©gime imposition :</td><td>" . $impo . "</td></tr>";
            $data['fiscale'] .= "<tr><td>TVA :</td><td>" . $rtva . "</td></tr>";
//            $data['fiscale'] .= "<tr><td>Date echÃ©ance :</td><td>" . $g['tva_date'] . "</td></tr>";
            $data['fiscale'] .= "<tr><td>Type TVA :</td><td>" . $ttva . "</td></tr>";
            $data['fiscale'] .= "<tr><td>Num Intracom :</td><td>...</td></tr>";
            $data['dossier'] = $dossier;

            //recuperation entete releve
            $data['ddebit'] = 0;
            $data['dcredit'] = 0;
            $data['fdebit'] = 0;
            $data['fcredit'] = 0;

            $query = "SELECT B.*,BC.*,S.* FROM saisie_controle S, banque_compte BC,banque B
                WHERE S.banque_compte_id = BC.id
                AND BC.banque_id = B.id
                AND S.image_id=" . $imgId;
            $prep = $this->pdo->query($query);
            $datar = $prep->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($datar as $man) {
                $data['montant_ttc'] = $man['montant_ttc'];
                //recuperation entete lcr
                $data['date_reglement'] = $this->formatAff($man['date_reglement']);
                $data['date_echeance'] = $this->formatAff($man['date_echeance']);
                $data['totallcr'] = $man['montant_ttc'];
                $data['nombreligne'] = $man['nbre_couvert'];
                $data['numcompte'] = $man['numcompte'];
                $data['num_releve'] = $man['num_releve'];
                $data['date_facture'] = $this->formatAff($man['date_facture']);
                $data['num_facture'] = $man['num_facture'];
                if ($man['solde_debut'] > 0) {
                    $data['dcredit'] = $man['solde_debut'];
                } else {
                    $data['ddebit'] = $man['solde_debut'] * (-1);
                }
                if ($man['solde_fin'] > 0) {
                    $data['fcredit'] = $man['solde_fin'];
                } else {
                    $data['fdebit'] = $man['solde_fin'] * (-1);
                }
                $data['page_solde_debut'] = $man['page_solde_debut'];
                $data['page_solde_fin'] = $man['page_solde_fin'];
                if (strlen($man['periode_d1']) == 10) {
                    $data['debut_periode'] = $this->formatAff($man['periode_d1']);
                }
                if (strlen($man['periode_f1']) == 10) {
                    $data['fin_periode'] = $this->formatAff($man['periode_f1']);
                }
                $data['numc'] = substr($man['numcompte'], 0, 21);
                $data['banque_compte_id'] = $man['banque_compte_id'];
                $data['iban'] = $man['iban'];
                $data['banque_id'] = $man['banque_id'];

                $data['nombre_cheque'] = $man['nbre_couvert'];
                $data['total_cheque'] = $man['montant_ttc'];
                if (strlen($man['date_reglement']) == 10) {
                    $data['date_remise'] = $this->formatAff($man['date_reglement']);
                }

                $data['totalccreleve'] = $man['montant_ttc'];

                $dateccreleve = $man['date_reglement'];

                if($dateccreleve !== null){
                    $dateccreleve = \DateTime::createFromFormat('Y-m-d', $dateccreleve);
                    if($dateccreleve !== false)
                        $dateccreleve = $dateccreleve->format('d/m/Y');
                }

                $data['dateccreleve'] = $dateccreleve;
                $data['totalccreleve'] = $man['montant_ttc'];

                $data['numcb'] = $man['carte_bleu_banque_compte_id'];
            }


            $data['pdf'] = $url;

            $txt = "";

            /* OCR Tsy ampiasaina intsony

            if (file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".txt")) {
                $txt = file_get_contents($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".txt");
            }
            if (file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . "-0.txt")) {
                $txt = file_get_contents($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . "-0.txt");
            }
            $txt = str_replace(chr(10) . chr(10), '', $txt);
            $txto = $txt;
            if (strpos($txt, 'R.I.B') > 0) {
                $txt = substr($txt, strpos($txt, 'R.I.B'), 35);
                $txt = preg_replace("/[^0-9]/", "", $txt);
            } else if (strpos($txt, 'Nom du compte') > 0) {
                $txt = substr($txt, strpos($txt, 'Nom du compte'), 50);
                $txt = preg_replace("/[^0-9]/", "", $txt);
                $txt = substr($txt, 12, 11);
            } else if (strpos($txt, 'Compte Courant') > 0) {
                $txt = substr($txt, strpos($txt, 'Compte Courant'), 30);
                $txt = preg_replace("/[^0-9]/", "", $txt);
            } else if (strpos($txt, 'COMPTE N') > 0) {
                $txt = substr($txt, strpos($txt, 'COMPTE N'), 30);
                $txt = preg_replace("/[^0-9]/", "", $txt);
            } else if (strpos($txt, 'Confort N') > 0) {
                $txt = substr($txt, strpos($txt, 'Confort N'), 30);
                $txt = preg_replace("/[^0-9]/", "", $txt);
            } else if (strpos($txt, 'Global N') > 0) {
                $txt = substr($txt, strpos($txt, 'Global N'), 30);
                $txt = preg_replace("/[^0-9]/", "", $txt);
            } else {
                if (strpos($txt, 'IBAN') > 0) {
                    $txt = substr($txt, strpos($txt, 'IBAN'), 70);
                    $txt = str_replace(':', '', $txt);
                    $txt = str_replace(';', '', $txt);
                    $txt = str_replace(' ', '', $txt);
                    $txt = substr($txt, 8, 21);
                } else if (strpos($txt, 'I.B.A.N.') > 0) {
                    $txt = substr($txt, strpos($txt, 'I.B.A.N.'), 60);
                    $txt = str_replace(':', '', $txt);
                    $txt = str_replace(';', '', $txt);
                    $txt = str_replace(' ', '', $txt);
                    $txt = substr($txt, 8, 21);
                } else {
                    if (strpos($txt, 'GENERALE CTC INDEXE') > 0 && strpos($txt, 'nÂ°') > 0) {
                        $txt = substr($txt, strpos($txt, 'nÂ°'), 30);
                        $txt = preg_replace("/[^0-9]/", "", $txt);
                        if (strlen($txt) == 23) {
                            $txt = substr($txt, 10, 11);
                        } else {
                            $txt = '';
                        }
                    } else {
                        $txt = '';
                    }
                }
            }
            if ($txt == '') {
                //recuperation 11 chiffres
                $regex = "/(\d+)/";
                preg_match_all($regex, $txto, $matches);
                foreach ($matches[0] as $k => $v) {
                    if (strlen($v) == 11) {
                        $txt = $v;
                        //echo $txt."|";
                        break;
                    }
                }
            }

            if (isset($data['numc'])) {
                $data['ocr'] = $data['numc'];
            } else {
                $data['ocr'] = $txt;
            }

            if (!file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".pdf")) {
                $ocr = $this->get('kernel')->getRootDir() . '/../web/ocr/';
                exec('curl -o ' . $ocr . $image->getNom() . '.pdf ' . $data["pdf"] . ' --insecure');
            }

            $target_pdf = $this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".pdf";
            $cmd = sprintf("identify %s", $target_pdf);
            exec($cmd, $output);
            $nbpage = count($output);
            $data['nbpage'] = $nbpage;

            */

            //$data['demo'] = $demo;

            //cat souscat soussouscat
            $data['infocat'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getInfosImage($imgId);

            $data['convention'] = ($convention !== null) ? $convention->getId() : 0;

            $data['engagement'] = $image->getFlaguer();

            header("Access-Control-Allow-Origin: *");

            header("Content-type: application/pdf");


            return new JsonResponse($data);
        }
        throw new BadRequestHttpException("Method not allowed.");
    }

    public function ocrCompteAction(Request $request)
    {
        $post = $request->request;

        $pdf = $post->get('pdf');
        $imag = $post->get('imag');
        foreach ($imag as $imgId) {

            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imgId);

            $txt = "";

            if (file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".txt")) {
                $txt = file_get_contents($this->get('kernel')->getRootDir() .'/../web/ocr/'.$image->getNom().".txt");
            }

            if (file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . "-0.txt")) {
                $txt = file_get_contents($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . "-0.txt");
            }
            if (!(strlen($txt) > 10)) {


                $ocrpath = $this->get('kernel')->getRootDir() . '/../web/ocr/';
                //*traitement pdf -> jpg + ocr

                if (!file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".pdf")) {
                    exec("curl -o " . $ocrpath . $image->getNom() . ".pdf " . $pdf . " --insecure");
                }

                if (!file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . ".jpg") &&
                    !file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $image->getNom() . "-0.jpg")) {
                    exec("convert -density 300 " . $ocrpath . $image->getNom() . ".pdf" . " -depth 8 " . $ocrpath . $image->getNom() . ".jpg");
                }


                $dir = $this->get('kernel')->getRootDir() . '/../web/ocr';
                $leni = strlen($image->getNom());
                $nbpage = 0;
                if (is_dir($dir)) {
                    if ($dh = opendir($dir)) {
                        while (($file = readdir($dh)) !== false) {
                            if ($file != "." && $file != "..") {
                                $nomi = substr($file, 0, $leni);
                                $noma = substr($file, 0, $leni + 2);
                                $exti = substr($file, -3, 3);
                                if ($nomi == $image->getNom() && $exti == 'jpg') {
                                    if (strpos($file, "-0") > 0) {
                                        if (!file_exists($this->get('kernel')->getRootDir() . '/../web/ocr/' . $noma)) {
                                            exec("tesseract " . $this->get('kernel')->getRootDir() . "/../web/ocr/" . " " . $file . $this->get('kernel')->getRootDir() . "/../web/ocr/" . $noma);
                                        }
                                    } else {
                                        if (!file_exists($this->get('kernel')->getRootDir() . "/../web/ocr/" . $nomi) &&
                                            !file_exists($this->get('kernel')->getRootDir() . "/../web/ocr/" . $noma)) {
                                            exec("tesseract " . $this->get('kernel')->getRootDir() . "/../web/ocr/" . $file . " " . $this->get('kernel')->getRootDir() . '/../web/ocr/' . $nomi);
                                        }
                                    }
                                    $nbpage++;
                                }
                            }
                        }
                        closedir($dh);
                    }
                }
            }
        }
        return new JsonResponse($txt);
    }

    public function loadSousouscategorieAction(Request $request)
    {

        $souscategorie = $request->query->get('souscategorie');

        $soussouscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->findBy(array('souscategorie' => $souscategorie, 'actif' => 1), array('libelleNew' => 'ASC'));

        return $this->render('@Banque/Banque/saisie/soussouscategorie.html.twig',
            array('soussouscategories' => $soussouscategories));

    }

    public function changeCategorieAction(Request $request)
    {
        $imagid = json_decode($request->request->get('imagid'), true);
        $c = json_decode($request->request->get('c'), true);
        $sc = json_decode($request->request->get('sc'), true);
        $ssc = json_decode($request->request->get('ssc'), true);

        if ($imagid > 0) {
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imagid);
            if ($image !== null) {

                $separation = $this->getDoctrine()
                    ->getRepository('AppBundle:Separation')
                    ->getSeparationByImage($image);

                if ($separation) {

                    $oldcategorie = $separation->getCategorie();
                    $oldSouscategorie = $separation->getSouscategorie();
                    $oldSoussouscategorie = $separation->getSoussouscategorie();


                    $categorie = $this->getDoctrine()
                        ->getRepository('AppBundle:Categorie')
                        ->find($c);

                    $souscategorie = $this->getDoctrine()
                        ->getRepository('AppBundle:Souscategorie')
                        ->find($sc);

                    $soussouscategorie = $this->getDoctrine()
                        ->getRepository('AppBundle:Soussouscategorie')
                        ->find($ssc);

                    $em = $this->getDoctrine()
                        ->getManager();

                    $separation->setCategorie($categorie);

                    $separation->setSouscategorie($souscategorie);

                    $separation->setSoussouscategorie($soussouscategorie);

                    $same = true;

                    if($oldcategorie !== $categorie ||
                        $oldSouscategorie !== $souscategorie ||
                        $oldSoussouscategorie !== $soussouscategorie){

                        if($oldSoussouscategorie !== $soussouscategorie) {
                            $same = false;
                        }

                        $historique = new HistoriqueCategorie();
                        $historique->setOperateur($this->getUser());
                        $historique->setImage($image);
                        $historique->setCategorie($oldcategorie);
                        $historique->setSouscategorie($oldSouscategorie);
                        $historique->setSoussouscategorie($oldSoussouscategorie);
                        $historique->setMotif('SAISIE BANQUE');
                        $historique->setDateModification(new \DateTime('now'));

                        //Doublons => atao fini ny panier
                        if($souscategorie->getId() === 3){
                            /** @var Panier[] $paniers */
                            $paniers = $this->getDoctrine()
                                ->getRepository('AppBundle:Panier')
                                ->getPanierSaisieBanqueByImage($image);

                            foreach ($paniers as $panier){
                                $panier->setFini(1);
                            }
                        }

                        $em->persist($historique);
                        $em->flush();

                    }


                    $em->flush();

                    return new JsonResponse([
                        'type' => 'success',
                        'message' => 'Modification effectuÃ©e',
                        'same' => $same
                    ]);
                }

            }
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Image introuvable']
        );

    }

    public function stateImageTableauBordAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() == 'POST') {
                $param = array();
                $data = array();
                $dataTaf = array();
                $dataPieceManquant = array();
                $nb_cli_Doss_Cmpt = array();
                $client = $request->request->get('client');
                $groupe = $request->request->get('groupe');
                $param['dossier'] = $request->request->get('dossier');
                $param['exercice'] = $request->request->get('exercice');
                $param['periode'] = $request->request->get('periode');
                $param['site'] = $request->request->get('site');
                $dateScanSearch = false;

                switch ($param['periode']) {
                    case 1:
                        // Aujourd'hui
                        $periodNow = new \DateTime();
                        $param['cas'] = 1;
                        $param['dateDeb'] = $periodNow->format('Y-m-d');
                        $param['dateFin'] = $periodNow->format('Y-m-d');
                        $data['date_debut'] = 'ArrivÃ©e Aujourd\'hui';
                        $dateScanSearch = true;
                        break;
                    case 2:
                        // Depuis une semaine
                        $periodNow = new \DateTime();
                        $param['cas'] = 2;
                        $now = clone $periodNow;
                        $oneWeek = date_modify($periodNow, "-7 days");
                        $param['dateDeb'] = $oneWeek->format('Y-m-d');
                        $param['dateFin'] = $now->format('Y-m-d');
                        $data['date_debut'] = 'ArrivÃ©e depuis ' . $oneWeek->format('d/m/Y');
                        $dateScanSearch = true;
                        break;
                    case 3:
                        // Depuis un mois
                        $periodNow = new \DateTime();
                        $param['cas'] = 3;
                        $now = clone $periodNow;
                        $oneMonth = date_modify($periodNow, "-1 months");
                        $param['dateDeb'] = $oneMonth->format('Y-m-d');
                        $param['dateFin'] = $now->format('Y-m-d');
                        $data['date_debut'] = 'ArrivÃ©e depuis ' . $oneMonth->format('d/m/Y');
                        $dateScanSearch = true;
                        break;
                    case 4:
                        // Tous les exercixe
                        $param['dateDeb'] = null;
                        $param['dateFin'] = null;
                        $data['date_debut'] = 'Tout l\'exercice';
                        $dateScanSearch = false;
                        break;
                    case 5:
                        // Fourchette date debut et date fin
                        $param['cas'] = 5;
                        $debPeriode = $request->request->get('perioddeb');
                        $finPeriode = $request->request->get('periodfin');
                        if ((isset($debPeriode) && !is_null($debPeriode)) && (isset($finPeriode) && !is_null($finPeriode))) {
                            $param['dateDeb'] = $debPeriode;
                            $param['dateFin'] = $finPeriode;
                            $dateDeb_param = explode('-', $debPeriode);
                            $dateFin_param = explode('-', $finPeriode);
                            $data['date_debut'] = 'ArrivÃ©e depuis ' . $dateDeb_param[2] . '/' . $dateDeb_param[1] . '/' . $dateDeb_param[0] . ' au ' . $dateFin_param[2] . '/' . $dateFin_param[1] . '/' . $dateFin_param[0];
                        }
                        $dateScanSearch = true;
                        break;
                }

                $dossier = $this->getDoctrine()
                                ->getRepository('AppBundle:Dossier')
                                ->find($param['dossier']);
                $status = '';

                if(!empty($dossier)){
                    $status = $this->getDoctrine()
                                   ->getRepository('AppBundle:Dossier')
                                   ->getStatusDossier($dossier,$param['exercice']);
                }

                /*if ( $client == 0 ) {

                    $clients = array();*/

                    /*Selection responsable*/
                   /* if ( $groupe != 0 ) {
                        $clients = $this->getDoctrine()
                                        ->getRepository('AppBundle:Client')
                                        ->getAllClientByResponsable($groupe);
                    } *//*Tous les responsables*/
                    /*else {
                        $responsables = $this->getDoctrine()
                                             ->getRepository('AppBundle:ResponsableScriptura')
                                             ->getSubDirections();

                        foreach ( $responsables as $responsable ) {

                            $clientsResp = $this->getDoctrine()
                                                ->getRepository('AppBundle:Client')
                                                ->getAllClientByResponsable($responsable['operateur_id']);

                            $clients = array_merge($clients, $clientsResp);

                        }
                    }
                    $param['client'] = [];
                    foreach ( $clients as $client ) {
                        array_push($param['client'], $client->id);
                    }
                }else {
                    $selectedClient = $this->getDoctrine()
                                           ->getRepository('AppBundle:Client')
                                           ->find($client);
                    $param['client'][] = $selectedClient->getId();
                }*/

                $images = $this->getDoctrine()
                               ->getRepository('AppBundle:Image')
                               ->getListeImageByDossierCategorie($dossier, intval($param['exercice']), 16, $dateScanSearch, $param['dateDeb'], $param['dateFin']);

                $imageSeparations = $this->getDoctrine()
                                         ->getRepository('AppBundle:Image')
                                         ->getListeImageSeparationByDossierCategorie($dossier->getId(), intval($param['exercice']), 16, $dateScanSearch, $param['dateDeb'], $param['dateFin']);
                $traitementImageBanque = $this->traitementImageBanque($images, $imageSeparations, 16);
            }
        }

                /*$sitImages = $this->getDoctrine()
                                  ->getRepository('AppBundle:Image')
                                  ->getSituationsImagesBySeparation($param);

                $situationImagesBanque = $this->getDoctrine()
                                              ->getRepository('AppBundle:Image')
                                              ->getSituationsImagesByCategorie($param);

                $nbImagesEncours = $this->getDoctrine()
                                        ->getRepository('AppBundle:Image')
                                        ->getNbImageEncours($param);

                $listSousCatExist = array();
                foreach ($sitImages['situation_image'] as $val){
                    if(array_key_exists($val->libelle_new, $situationImagesBanque)){
                        $val->nb_image = $val->nb_image + $situationImagesBanque[$val->libelle_new];
                        $listSousCatExist[] = $val->libelle_new;
                    }
                }

                foreach ($situationImagesBanque as $k=>$val1){
                    if(!in_array($k, $listSousCatExist)){
                        array_push($sitImages['situation_image'], [
                            'nb_image' => $val1,
                            'libelle_new' => $k,
                            'nb_stock' => 0
                        ]);
                    }
                }*/

                /*$total =  $this->getDoctrine()
                               ->getRepository('AppBundle:Image')
                               ->getTravauxAfaire($param, 'total');

                $lettre =  $this->getDoctrine()
                               ->getRepository('AppBundle:Image')
                               ->getTravauxAfaire($param, 'lettre');

                $clef =  $this->getDoctrine()
                               ->getRepository('AppBundle:Image')
                               ->getTravauxAfaire($param, 'clef');

                $pc_manquant =  $this->getDoctrine()
                                     ->getRepository('AppBundle:Image')
                                     ->getTravauxAfaire($param, 'pc_manquant');

                $cheque_inconnu =  $this->getDoctrine()
                                        ->getRepository('AppBundle:Image')
                                        ->getTravauxAfaire($param, 'cheque_inconnu');

                $arapprocher = $total - ($lettre + $clef);
                $rapprocher = ($total != 0) ? (($lettre + $clef) * 100) / $total : 0;
                $dataTaf['total'] = $total;
                $dataTaf['lettre'] = $lettre;
                $dataTaf['clef'] = $clef;
                $dataTaf['pc_manquant'] = $pc_manquant;
                $dataTaf['cheque_inconnu'] = $cheque_inconnu;
                $dataTaf['arapprocher'] = $arapprocher;
                $dataTaf['rapprocher'] = $rapprocher;

                $getMois = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getMoisManquantByParam($param);

                $dataPieceManquant = $this->getImputees($getMois, $param);
            }
        }else{
            $operateur = $this->getUser();
            $responsables = $this->getDoctrine()
                                 ->getRepository('AppBundle:ResponsableScriptura')
                                 ->getSubDirections();

            $clients = array();

            foreach ($responsables as $responsable) {

                $clientsResp = $this->getDoctrine()
                                    ->getRepository('AppBundle:Client')
                                    ->getAllClientByResponsable($responsable['operateur_id']);
                $clients = array_merge($clients,$clientsResp);
            }
            sort($clients);
            $exercices = $this->getExercices(7, 2);
        }*/

        $operateur = $this->getUser();
            $responsables = $this->getDoctrine()
                                 ->getRepository('AppBundle:ResponsableScriptura')
                                 ->getSubDirections();

            $clients = array();

            foreach ($responsables as $responsable) {

                $clientsResp = $this->getDoctrine()
                                    ->getRepository('AppBundle:Client')
                                    ->getAllClientByResponsable($responsable['operateur_id']);
                $clients = array_merge($clients,$clientsResp);
            }
            sort($clients);
            $exercices = $this->getExercices(7, 2);

        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() == 'POST') {
                $data['banque_stock'] = $sitImages['banque_stock'];
                $data['situations_images'] = $sitImages['situation_image'];
                $data['nb_img_encours'] = $nbImagesEncours;
                $data['status'] = $status;
                $data['dataTaf'] = $dataTaf;
                $data['dataPieceManquant'] = $dataPieceManquant;
                return new JsonResponse($data);
            }
        }
        return $this->render('BanqueBundle:Banque:tableau_bord_situation_image.html.twig', [
            'clients' => $clients,
            'responsables' => $responsables,
            'exercices' => $exercices,
            'isGestion' => false
        ]);
    }

    public function getBetweenDate($start, $end)
    {
        $time1 = strtotime($start);
        $time2 = strtotime($end);
        $my = date('mY', $time2);
        $months = array(date('Y-m', $time1));
        while ($time1 < $time2) {
            $time1 = strtotime(date('Y-m', $time1) . ' +1 month');
            if (date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('Y-m', $time1);
        }
        $months[] = date('Y-m', $time2);
        return $months;
    }

    public function getNbPieceManquantByM($m_moins_un, $m_moins_deux, $m_moins_trois, $data)
    {
        $pc_m = 0;
        $pc_moins_un = 0;
        $pc_moins_deux = 0;
        $pc_moins_trois = 0;
        $pc_autr = 0;
        $nbPieceManquantByM = array();
        foreach ($data as $key => $value) {
            switch ($value->date_r) {
                case $m_moins_un:
                    $pc_m += $value->nb_r;
                    break;
                case $m_moins_deux:
                    $pc_moins_un += $value->nb_r;
                    break;
                case $m_moins_trois:
                    $pc_moins_deux += $value->nb_r;
                    break;
                default:
                    $pc_autr += $value->nb_r;
                    break;
            }
        }
        $nbPieceManquantByM[] = $pc_m;
        $nbPieceManquantByM[] = $pc_moins_un;
        $nbPieceManquantByM[] = $pc_moins_deux;
        $nbPieceManquantByM[] = $pc_autr;
        $nbPieceManquantByM[] = $pc_m + $pc_moins_un + $pc_moins_deux + $pc_autr;
        return $nbPieceManquantByM;
    }

    public function getDetailImputees($data_imputees, $param)
    {
        $tab_imputees = array();
        $tab_key_mois = array();
        $tab_exist_comptes = array();
        $last_key = 0;
        $exercice = $param['exercice'];
        $dossier = $param['dossier'];
        $client = $param['client'];
        $param['periode'] = 4; // tout l'exercice
        $betweens = array();
        $clientEntity = $this->getDoctrine()
                       ->getRepository('AppBundle:Client')
                       ->find($client[0]);

        $prioriteParam =  $this->getDoctrine()
                               ->getRepository('AppBundle:PrioriteParam')
                               ->findAll();
        $taches = null;
        $periodeTache = new \DateTime();
        if($dossier == 0){
            $taches = $this->getDoctrine()->getRepository('AppBundle:Tache')
                                          ->getTachesPourGestionTaches($param['dossier'], $periodeTache, true, true, $param['isScriptura'],
                                            $param['isEc'], $param['isCf'], $clientEntity);
        }

        foreach ($data_imputees as $key => $value) {
            if($taches == null || (!array_key_exists($value->dossier_id, $taches['taches'])))
                $taches = $this->getDoctrine()->getRepository('AppBundle:Tache')
                                          ->getTachesPourGestionTaches($value->dossier_id, $periodeTache, true, true, $param['isScriptura'],
                                            $param['isEc'], $param['isCf'], null);
            if(array_key_exists($value->dossier_id, $taches['taches']) || $param['responsable'] == ''){
                if (!empty($value->mois)) {
                    $tab_mois_manquant = explode(',', $value->mois);
                    $mois_manquant = str_replace(' ', '', $tab_mois_manquant);
                    //fin mois cloture
                    if ($value->cloture < 9) {
                        $debut_mois = ($exercice - 1) . '-0' . ($value->cloture + 1) . '-01';
                    } else if ($value->cloture >= 9 and $value->cloture < 12) {
                        $debut_mois = ($exercice - 1) . '-' . ($value->cloture + 1) . '-01';
                    } else {
                        $debut_mois = ($exercice) . '-01-01';
                    }
                    //debut mois cloture
                    if ($value->cloture < 10) {
                        $fin_mois = ($exercice) . '-0' . ($value->cloture) . '-01';
                    } else {
                        $fin_mois = ($exercice) . '-' . ($value->cloture) . '-01';
                    }

                    /*$tab_mois_cloture = $this->getBetweenDate($debut_mois, $fin_mois);*/

                    $k = array_key_exists($debut_mois . '-' . $fin_mois, $betweens);
                    if ($k) {
                        $tab_mois_cloture = $betweens[$debut_mois . '-' . $fin_mois];
                    } else{
                        $tab_mois_cloture = $this->getBetweenDate($debut_mois, $fin_mois);

                        $betweens[$debut_mois . '-' . $fin_mois] = $tab_mois_cloture;

                    }

                    $nb_m_mois_exist = false;
                    switch (count($mois_manquant)) {
                        case 0:
                            $nb_m_mois_exist = true;
                            $tab_imputees[$key]['m'] = 'M-1';
                            break;
                        case 1:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 2:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 3:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 12:
                            $nb_m_mois_exist = true;
                            //jerena aloha raha mis relevÃ© ihany le banque amin'ny alalan'ny dossier
                            $resReleves = $this->getDoctrine()
                                               ->getRepository('AppBundle:Image')
                                               ->getInfoReleveByDossier($value->banque_compte_id, $exercice);
                            $tab_imputees[$key]['m'] = (count($resReleves) > 0) ? 'Inc.' : 'Auc.';
                            break;
                        default:
                            $nb_m_mois_exist = true;
                            $tab_imputees[$key]['m'] = 'Inc.';
                            break;
                    }

                    if (!$nb_m_mois_exist) {
                        $min = 13;
                        $now = new \DateTime();
                        foreach ($tab_key_mois[$key] as $key_m => $key_mois_m) {
                            if ($key_m < $min) {
                                $min = $key_m;
                            }
                        }
                        //Jerena aloha raha misy tsy ampy eo ampovoany
                        $continue = true;
                        $lastIndex = -1;
                        foreach ($tab_key_mois[$key] as $k => $v){
                            if($lastIndex === -1){
                                $lastIndex = $k;
                                continue;
                            }
                            if($lastIndex+1 !== $k){
                                $continue = false;
                                break;
                            }
                            else{
                                $lastIndex = $k;
                            }
                        }

                        if($continue) {
                            if (intval($exercice) < $now->format('Y')) {
                                switch ($min) {
                                    case 11:
                                        $tab_imputees[$key]['m'] = 'M-1';
                                        break;
                                    case 10:
                                        $tab_imputees[$key]['m'] = 'M-1';
                                        break;
                                    case 9:
                                        $tab_imputees[$key]['m'] = 'M-2';
                                        break;
                                    default:
                                        $tab_imputees[$key]['m'] = 'Inc.';
                                        break;
                                }
                            } else {
                                if (array_key_exists($min, $tab_key_mois[$key])){
                                    $now = new \DateTime();
                                    $yearNow = $now->format('Y');
                                    $monthNow = $now->format('m');
                                    $dateNow = intval($now->format('d'));
                                    $datetime = \DateTime::createFromFormat('Y-m-d', $tab_key_mois[$key][$min] . "-01");
                                    $interval = $now->diff($datetime);
                                    $diff = $interval->m + 1;
                                    if($dateNow <= 6 ){
                                        $diff = $interval->m;
                                    }

                                    if ($diff === 0) {
                                        $tab_imputees[$key]['m'] = 'M-1';
                                    } else if ($diff > 0) {
                                        $tab_imputees[$key]['m'] = 'M-' . $diff;
                                    } else {
                                        $tab_imputees[$key]['m'] = 'Inc.';
                                    }
                                }else{
                                    $tab_imputees[$key]['m'] = 'Inc.';
                                }
                            }
                        }
                        else{
                            $tab_imputees[$key]['m'] = 'Inc.';
                        }
                    }
                }
                else {
                    $tab_imputees[$key]['m'] = 'M-1';
                }

                $regimeTva = trim($value->regime_tva);
                $abrevRegimeTva = '';
                if($regimeTva !== ''){
                    $abrevs = explode(' ',str_replace(['-', '_'],' ',$regimeTva));
                    foreach ($abrevs as $abrev){
                        $abrevRegimeTva .= strtoupper($abrev[0]);
                    }

                }

                $dossierStatus = '';
                if($value->status === 1){
                    $dossierStatus = 'Actif';
                } else if($value->status === 2) {
                    $dossierStatus = 'Suspendu';
                }else if($value->status === 3){
                    $dossierStatus = 'Radié';
                }

                $tab_imputees[$key]['clients'] = $value->clients;
                $tab_imputees[$key]['dossier'] = $value->dossier;
                $tab_imputees[$key]['banque'] = $value->banque;
                $tab_imputees[$key]['nb_r'] = $value->nb_r;
                $nbr_rapproche = ($value->nb_r != 0) ? (($value->nb_lettre + $value->nb_clef + $value->nb_ecriture_change) * 100) / $value->nb_r : 0;
                $nbr_pc_manquant = ($value->nb_r != 0) ? ($value->nb_r - ($value->nb_lettre + $value->nb_clef)) : 0;
                $tab_imputees[$key]['nbr_rapproche'] = $this->ifNull($nbr_rapproche);
                $tab_imputees[$key]['nb_pc_manquant'] = $this->ifNull($nbr_pc_manquant);
                $tab_imputees[$key]['nb_clef'] = $this->ifNull($value->nb_clef);
                $tab_imputees[$key]['nb_lettre'] = $this->ifNull($value->nb_lettre);
                $tab_imputees[$key]['chq_inconnu'] = $this->ifNull($value->chq_inconnu);
                $tab_imputees[$key]['dossier_status'] = $dossierStatus;
                $tab_imputees[$key]['valider'] = $value->valider;
                $tab_imputees[$key]['banque_compte_id'] = $value->banque_compte_id;
                $tab_imputees[$key]['importe'] = 'Imp.';
                $tab_imputees[$key]['etat'] = $value->etat;

                //date echeance tva
                $now = new \DateTime();
                $miniReel = ['01','04','07','10'];
                $reelSimplifie = ['07','12'];
                $month = $now->format('m');

                $remise = 0;
                $frbanc = 0;
                $lcr = 0;
                $vrmt = 0;
                $cartCredRel = 0;
                $cartDebRel = 0;

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 8, -1, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $frbanc += 1;
                    }
                }
                $dataObMq[0]['nb'] = $frbanc;
                $dataObMq[0]['libelle'] = 'Frais Bancaire';

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 5, -1, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $lcr += 1;
                    }
                }
                $dataObMq[1]['nb'] = $lcr;
                $dataObMq[1]['libelle'] = 'RelevÃ©  LCR';

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 7, -1, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $remise += 1;
                    }
                }
                $dataObMq[2]['nb'] = $remise;
                $dataObMq[2]['libelle'] = 'Remise en banque';

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 153, 1905, 1, -1);

                $imageObChq = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 6, -1, 1, -1);
                $imageOb = array_merge($imageOb, $imageObChq);
                foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $vrmt += 1;
                    }
                }

                $dataObMq[3]['nb'] = $vrmt;
                $dataObMq[3]['libelle'] = 'VRT/CHQ EMIS';

               /* $souscategoriecarte = $this->getDoctrine()
                                           ->getRepository('AppBundle:Soussouscategorie')
                                           ->findBy(['souscategorie' => $this->getDoctrine()
                                                ->getRepository('AppBundle:Souscategorie')
                                                ->find(1)]
                                           );

                $carteKey = 4;
                foreach ($souscategoriecarte as $sscarte) {
                    $carteIndex = 0;
                    $imageOb = $this->getDoctrine()
                                    ->getRepository('AppBundle:Image')
                                    ->getListImageBanque($value->dossier_id, $exercice, 0, 1, $sscarte->getId(), 1, -1);
                    foreach ($imageOb as $im) {
                        if($im->ctrl_saisie > 2 && $im->valider != 100){
                            $carteIndex += 1;
                        }
                    }
                    $dataObMq[$carteKey]['nb'] = $carteIndex;
                    $dataObMq[$carteKey]['libelle'] = $sscarte->getLibelleNew();
                    $carteKey++;
                }*/

                $imageOb = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 1, 1901, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $cartCredRel += 1;
                    }
                }
                $dataObMq[4]['nb'] = $cartCredRel;
                $dataObMq[4]['libelle'] = 'Cartes de crÃ©dit relevÃ©';

                $imageOb = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 1, 2791, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $cartDebRel += 1;
                    }
                }
                $dataObMq[5]['nb'] = $cartDebRel;
                $dataObMq[5]['libelle'] = 'Cartes DÃ©bits tickets';

                $isOb = false;
                foreach ($dataObMq as $dataOb) {
                    if($dataOb['nb'] > 0){
                        $isOb = true;
                    }
                }
                $tab_imputees[$key]['ob'] = ($isOb) ? 'PB' : 'OK';
                $tab_imputees[$key]['data_ob_m'] = json_encode($dataObMq, true);

                /*$releve = $this->getDoctrine()
                               ->getRepository('AppBundle:Releve')
                               ->getReleveWithImageFlague($value->client_id, $value->dossier_id, $exercice);
                foreach ($releve as $r){
                    $alettrer = $this->getDoctrine()
                                     ->getRepository('AppBundle:TvaImputationControle')
                                     ->getNbAlettrer($value->dossier_id, $exercice, $r->montant);
                    if(count($alettrer) > 0) $nbAlettrer++;
                }
                */
                $tab_imputees[$key]['alettrer'] = ($value->a_lettrer) ? $value->a_lettrer : 0;

                $param['dossier'] = $value->dossier_id;
                $param['cas'] = 4;
                $param['exercice'] = $exercice;
                $nbImagesEncours = $this->getDoctrine()
                                        ->getRepository('AppBundle:Image')
                                        ->getNbImageEncours($param);

                $color = 0;

                if($tab_imputees[$key]['alettrer'] > 0)
                    $color = 1;

                if($nbImagesEncours > 0)
                    $color = 2;

                $tab_imputees[$key]['color'] = $color;

                //rb1 ok
                $param['dossier'] = $value->dossier_id;
                $dataRb1AC = $this->getDoctrine()
                                  ->getRepository('AppBundle:Image')
                                  ->getRb1AControler($param);
                $acontroler = $dataRb1AC['acontroler'];
                $tab_imputees[$key]['acontroler'] = $acontroler.'-'.$dataRb1AC['imgSaisieKo'];

                //ecart
                $banqueCompte = $this->getDoctrine()->getRepository('AppBundle:BanqueCompte')->find($value->banque_compte_id);
                $soldeDebut = $this->getDoctrine()->getRepository('AppBundle:Image')->getSoldes($banqueCompte,$exercice);
                $soldeFin = $this->getDoctrine()->getRepository('AppBundle:Image')->getSoldes($banqueCompte,$exercice,false);

                $mouvements = $this->getDoctrine()
                                    ->getRepository('AppBundle:Image')
                                    ->getMouvement($exercice, $value->banquecompte_id);

                $ecart = (float)($soldeFin - $soldeDebut - $mouvements);

                $tab_imputees[$key]['ecart'] = $ecart;

                $code = '';
                if($banqueCompte->getSourceImage() !== null){
                    if($banqueCompte->getSourceImage()->getSource() === 'SOBANK'){
                        $code = 'BI';
                    }
                }
                $tab_imputees[$key]['comptes'] = $value->comptes;
                $tab_imputees[$key]['sb'] = $code;
                $tab_imputees[$key]['tache'] = '';
                $datetimeTache = '';
                $tab_imputees[$key]['ech'] = '';
                $abrevTache = '';
                $tab_imputees[$key]['respons_tache'] = '';

                //tache
                if(array_key_exists($value->dossier_id, $taches['taches'])){
                    usort($taches['taches'][$value->dossier_id], function($a, $b) {
                      return ($a['datetime'] < $b['datetime']) ? -1 : 1;
                    });
                    foreach ($taches['taches'][$value->dossier_id] as $k => $t) {
                      $abrevTache = explode('*', $t['titre2']);
                      if(!$t['expirer']) {
                        $dateTache = $t['datetime']->format('d-m');
                        $datetimeTache = $t['datetime'];
                        $titre2Tache = $t['titre2'];
                        $abrevTache = $abrevTache[0];
                        $statusTvaTache = $t['status'];
                        $expirerTache = $t['expirer'];
                        if($t['responsable'] === 0){
                            $reponsableTache = "Scriptura";
                        }else if($t['responsable'] == 1){
                            $reponsableTache = "Cabinet";
                        }else{
                            $reponsableTache = "Client";
                        }
                        break;
                      }else{
                        $expirerTache = $t['expirer'];
                        $dateTache = $t['datetime']->format('d-m');
                        $datetimeTache = $t['datetime'];
                        $titre2Tache = $t['titre2'];
                        $abrevTache = $abrevTache[0];
                        $statusTvaTache = $t['status'];
                        if($t['responsable'] === 0){
                            $reponsableTache = "Scriptura";
                        }else if($t['responsable'] == 1){
                            $reponsableTache = "Cabinet";
                        }else{
                            $reponsableTache = "Client";
                        }
                      }
                    }

                    $tab_imputees[$key]['tache'] = json_encode($taches['taches'][$value->dossier_id], true);
                    if(count($taches['taches'][$value->dossier_id]) > 1){
                        $libelleTache = (count($taches['taches'][$value->dossier_id] == 1)) ? 'Tâche' : 'Tâches';
                        $valueInCellTache = $expirerTache.'='.$abrevTache.'='.count($taches['taches'][$value->dossier_id]).$libelleTache;
                    }else{
                        $valueInCellTache = $expirerTache.'='.$abrevTache.'='.false;
                    }
                    $tab_imputees[$key]['respons_tache'] = $reponsableTache;
                    $dateTacheTva = new \DateTime("now");
                    $dateTacheTvaYear = $dateTacheTva->format('y');
                    $dateTacheTvaMonth = $dateTacheTva->format('m');
                    $tachesDate = explode('-', $dateTache);
                    $isTvaNewYear = false;
                    if(count($tachesDate) > 1){
                        if((intval($dateTacheTvaMonth) > intval($tachesDate[1])) && ($statusTvaTache == 1 || $statusTvaTache == 2)){
                            $isTvaNewYear = true;
                            $dateTacheTvaYear++;
                        }
                    }
                    if($dateTache != '' && count($tachesDate) > 1){
                        if($tab_imputees[$key]['m'] != 'Auc.')
                            $tab_imputees[$key]['ech'] = $dateTacheTvaYear.'-'.$tachesDate[1].'-'.$tachesDate[0];
                    }
                }
                if($abrevTache == '') $valueInCellTache = false.'='.$abrevRegimeTva.'='.false;
                $tab_imputees[$key]['regime_tva'] = $valueInCellTache;

                //priorité
                $coulPriorte = "";
                $heure = 0;
                $month = $now->format('m');
                $tab_imputees[$key]['prio'] = '';
                if($tab_imputees[$key]['ech'] != '' && $datetimeTache != ''){
                    $dtime1 = $datetimeTache->format('Y').'-'.$datetimeTache->format('m-d');
                    if($isTvaNewYear)
                        $dtime1 = ($datetimeTache->format('Y') + 1).'-'.$datetimeTache->format('m-d');
                    $datetime1 = new \DateTime($dtime1); 
                    $datetime1->setTime(0,0); 
                    $datetime2 = new \DateTime("now"); 
                    $datetime2->setTime(0, 0);
                    $interval = 9000;
                    $datetime1 = $this->checkWeekend(new \DateTime($datetime1->format('Y-m-d')));
                    if ($datetime1 < $datetime2) {
                        $interval = 0;
                    } else {
                        /**  Calculer NB Heure entre délai et date du jour */
                        $interval = $this->nbHeureTravail(clone $datetime2, clone $datetime1);
                    }
                    $dateDiff = date_diff($datetime1, $datetime2);
                    $nbday = $dateDiff->format("%a");
                    if ($interval > 0) {
                        foreach ($prioriteParam[1]->getParamValue() as $val) {
                            if ($interval >= $val['min'] && $interval <= $val['max']) {
                                $coulPriorte = $val['max'] .' '.$val['color'].' '.$nbday;
                            }
                        }
                    }else{
                        $coulPriorte = '10000000000000000 Expiré '.$nbday;
                    }
                    $tab_imputees[$key]['prio'] = $coulPriorte;
                }

                $tab_imputees[$key]['t_indic'] = 100;
                if(!($tab_imputees[$key]['etat'])){
                    $tab_imputees[$key]['t_indic'] = 1;
                }else{
                    if($tab_imputees[$key]['m'] == 'Inc.' || $tab_imputees[$key]['ob'] == 'PB' || $tab_imputees[$key]['color'] == 2 || $tab_imputees[$key]['m'] == 'Auc.' || $acontroler > 0){
                        $tab_imputees[$key]['t_indic'] = 3;
                    }else if($tab_imputees[$key]['alettrer'] == 0 && ($tab_imputees[$key]['m'] == 'M-1' || $tab_imputees[$key]['m'] == 'A jour') && ($tab_imputees[$key]['importe'] == 'Imp.' && ($tab_imputees[$key]['ecart'] == '0' || $tab_imputees[$key]['ecart'] == '-0' || round($tab_imputees[$key]['ecart']) == 0))){
                         $tab_imputees[$key]['t_indic'] = 1;
                    }else{
                        $tab_imputees[$key]['t_indic'] = 2;
                    }
                }

                if ($tab_imputees[$key]['importe'] == 'Imp.') {
                    if ($tab_imputees[$key]['ecart'] == '0' || $tab_imputees[$key]['ecart'] == '-0' || round($tab_imputees[$key]['ecart']) == 0) {
                        $tab_imputees[$key]['importe'] = 1;
                    }else{
                        $tab_imputees[$key]['importe'] = 2;
                    }
                    if ($acontroler > 0 || $tab_imputees[$key]['m'] === 'Inc.') {
                        $tab_imputees[$key]['importe'] = 3;
                    }
                }else{
                    $tab_imputees[$key]['importe'] = 4;
                }

                $email = $this->getDoctrine()
                               ->getRepository('AppBundle:Emails')
                               ->findBy(array(
                                    'dossier' => $value->dossier_id,
                                    'typeEmail' => 'BANQUE_MANQUANTE'
                                ),array('id'=>'desc'));
                $dernierEnvoi = null;
                $dataIntruct = null;
                $htmlIntruct = '<i class="fa fa-circle" style="color:#15c115; padding-top: 3px;"></i>';
                if(count($email) > 0){
                    $dernierEnvoi = $email[0]->getDateEnvoi();
                    $dernierEnvoi = ($dernierEnvoi) ? $dernierEnvoi->format('Y-m-d') : null;
                }

                $tab_imputees[$key]['t_relbq'] = $dernierEnvoi;

                $instDossier = $this->getDoctrine()
                                   ->getRepository('AppBundle:ReleveManquant')
                                   ->getInstructions($value->dossier_id);

                if(count($instDossier) > 0){
                    $htmlIntruct = '<i class="fa fa-circle qtip_instruction pointer" style="color:#008000; padding-top: 3px;"></i>';
                    $dataIntruct = $instDossier[0]['isaisie'];
                } 

                $tab_imputees[$key]['t-inst'] = $htmlIntruct;
                $tab_imputees[$key]['t-data-inst'] = $dataIntruct;

                switch ($tab_imputees[$key]['m']) {
                    case 'M-1':
                        $tab_imputees[$key]['m'] = 1;
                        break;
                    case 'M-2':
                        $tab_imputees[$key]['m'] = 2;
                        break;
                    case 'M-3':
                        $tab_imputees[$key]['m'] = 3;
                        break;
                    case 'M-4':
                        $tab_imputees[$key]['m'] = 4;
                        break;
                    case 'M-5':
                        $tab_imputees[$key]['m'] = 5;
                        break;
                    case 'M-6':
                        $tab_imputees[$key]['m'] = 6;
                        break;
                    case 'M-7':
                        $tab_imputees[$key]['m'] = 7;
                        break;
                    case 'M-8':
                        $tab_imputees[$key]['m'] = 8;
                        break;
                    case 'Auc.':
                        $tab_imputees[$key]['m'] = 11;
                        break;
                    
                    default:
                        $tab_imputees[$key]['m'] = 10;
                        break;
                }
            }
            $tab_exist_comptes[] = $value->numcompte;
            $last_key = $key;
        }

        if($param['responsable'] == ''){
            $last_key++;
            $listSansImage = $this->getDoctrine()
                              ->getRepository('AppBundle:Image')
                              ->getListeImputeSansImage($client, $dossier, $tab_exist_comptes);
        
            foreach ($listSansImage as $key=>$value){
                $taches = $this->getDoctrine()->getRepository('AppBundle:Tache')
                                          ->getTachesPourGestionTaches($value->dossier_id, $periodeTache, true, true, $param['isScriptura'],
                                            $param['isEc'], $param['isCf'], null);

                $regimeTva = trim($value->regime_tva);
                $abrevRegimeTva = '';
                if($regimeTva !== ''){
                    $abrevs = explode(' ',str_replace(['-', '_'],' ',$regimeTva));
                    foreach ($abrevs as $abrev){
                        $abrevRegimeTva .= strtoupper($abrev[0]);
                    }

                }

                $dossierStatus = '';
                if($value->status === 1){
                    $dossierStatus = 'Actif';
                } else if($value->status === 2) {
                    $dossierStatus = 'Suspendu';
                }else if($value->status === 3){
                    $dossierStatus = 'Radié';
                }


                $tab_imputees[$last_key]['clients'] = $value->clients;
                $tab_imputees[$last_key]['dossier'] = $value->dossier;
                $tab_imputees[$last_key]['comptes'] = $value->comptes;
                $tab_imputees[$last_key]['banque'] = $value->banque;
                $tab_imputees[$last_key]['regime_tva'] = ' -'.$abrevRegimeTva.'- ';
                $tab_imputees[$last_key]['dossier_status'] = $dossierStatus;
                $tab_imputees[$last_key]['nb_r'] = 0;
                $tab_imputees[$last_key]['nbr_rapproche'] = 0;
                $tab_imputees[$last_key]['nb_pc_manquant'] = 0;
                $tab_imputees[$last_key]['nb_clef'] = 0;
                $tab_imputees[$last_key]['nb_lettre'] = 0;
                $tab_imputees[$last_key]['chq_inconnu'] = 0;
                $tab_imputees[$last_key]['valider'] = 0;
                $tab_imputees[$last_key]['banque_compte_id'] = $value->banque_compte_id;
                $tab_imputees[$last_key]['ecart'] = 0;
                $tab_imputees[$last_key]['prio'] = '';
                $tab_imputees[$last_key]['etat'] = '';
                $tab_imputees[$last_key]['tache'] = '';
                $tab_imputees[$last_key]['respons_tache'] = '';
                $tab_imputees[$last_key]['color'] = 0;
                $tab_imputees[$last_key]['t_indic'] = 100;
                $tab_imputees[$last_key]['t_relbq'] = '';
                $tab_imputees[$last_key]['ech'] = '';
                if(!empty($value->comptes)) {
                    $tab_imputees[$last_key]['m'] = 'Auc.';
                }else{
                    $tab_imputees[$last_key]['m'] = '';
                }
                $now = new \DateTime();

                 //tache
                if(array_key_exists($value->dossier_id, $taches['taches'])){
                    usort($taches['taches'][$value->dossier_id], function($a, $b) {
                      return ($a['datetime'] < $b['datetime']) ? -1 : 1;
                    });
                    foreach ($taches['taches'][$value->dossier_id] as $k => $t) {
                      $abrevTache = explode('*', $t['titre2']);
                      if(!$t['expirer']) {
                        $dateTache = $t['datetime']->format('d-m');
                        $datetimeTache = $t['datetime'];
                        $titre2Tache = $t['titre2'];
                        $abrevTache = $abrevTache[0];
                        $statusTvaTache = $t['status'];
                        $expirerTache = $t['expirer'];
                        if($t['responsable'] === 0){
                            $reponsableTache = "Scriptura";
                        }else if($t['responsable'] == 1){
                            $reponsableTache = "Cabinet";
                        }else{
                            $reponsableTache = "Client";
                        }
                        break;
                      }else{
                        $expirerTache = $t['expirer'];
                        $dateTache = $t['datetime']->format('d-m');
                        $datetimeTache = $t['datetime'];
                        $titre2Tache = $t['titre2'];
                        $abrevTache = $abrevTache[0];
                        $statusTvaTache = $t['status'];
                        if($t['responsable'] === 0){
                            $reponsableTache = "Scriptura";
                        }else if($t['responsable'] == 1){
                            $reponsableTache = "Cabinet";
                        }else{
                            $reponsableTache = "Client";
                        }
                      }
                    }

                    $tab_imputees[$last_key]['tache'] = json_encode($taches['taches'][$value->dossier_id], true);
                    if(count($taches['taches'][$value->dossier_id]) > 1){
                        $libelleTache = (count($taches['taches'][$value->dossier_id] == 1)) ? 'Tâche' : 'Tâches';
                        $valueInCellTache = $expirerTache.'='.$abrevTache.'='.count($taches['taches'][$value->dossier_id]).$libelleTache;
                    }else{
                        $valueInCellTache = $expirerTache.'='.$abrevTache.'='.false;
                    }
                    $tab_imputees[$last_key]['respons_tache'] = $reponsableTache;
                    $dateTacheTva = new \DateTime("now");
                    $dateTacheTvaYear = $dateTacheTva->format('y');
                    $dateTacheTvaMonth = $dateTacheTva->format('m');
                    $tachesDate = explode('-', $dateTache);
                    $isTvaNewYear = false;
                    if(count($tachesDate) > 1){
                        if((intval($dateTacheTvaMonth) > intval($tachesDate[1])) && ($statusTvaTache == 1 || $statusTvaTache == 2)){
                            $isTvaNewYear = true;
                            $dateTacheTvaYear++;
                        }
                    }
                    if($dateTache != '' && count($tachesDate) > 1)
                        $tab_imputees[$last_key]['ech'] = $dateTacheTvaYear.'-'.$tachesDate[1].'-'.$tachesDate[0];
                }

                if(!isset($abrevTache))
                    $abrevTache = '';

                if($abrevTache == '') $valueInCellTache = false.'='.$abrevRegimeTva.'='.false;
                $tab_imputees[$last_key]['regime_tva'] = $valueInCellTache;

                //priorité
                $coulPriorte = "";
                $heure = 0;
                $month = $now->format('m');
                $tab_imputees[$last_key]['prio'] = '';
                if($tab_imputees[$last_key]['ech'] != '' && $datetimeTache != ''){
                    $dtime1 = $datetimeTache->format('Y').'-'.$datetimeTache->format('m-d');
                    if($isTvaNewYear)
                        $dtime1 = ($datetimeTache->format('Y') + 1).'-'.$datetimeTache->format('m-d');
                    $datetime1 = new \DateTime($dtime1); 
                    $datetime1->setTime(0,0); 
                    $datetime2 = new \DateTime("now"); 
                    $datetime2->setTime(0, 0);
                    $interval = 9000;
                    $datetime1 = $this->checkWeekend(new \DateTime($datetime1->format('Y-m-d')));
                    if ($datetime1 < $datetime2) {
                        $interval = 0;
                    } else {
                        /**  Calculer NB Heure entre délai et date du jour */
                        $interval = $this->nbHeureTravail(clone $datetime2, clone $datetime1);
                    }
                    $dateDiff = date_diff($datetime1, $datetime2);
                    $nbday = $dateDiff->format("%a");
                    if ($interval > 0) {
                        foreach ($prioriteParam[1]->getParamValue() as $val) {
                            if ($interval >= $val['min'] && $interval <= $val['max']) {
                                $coulPriorte = $val['max'] .' '.$val['color'].' '.$nbday;
                            }
                        }
                    }else{
                        $coulPriorte = '10000000000000000 Expiré '.$nbday;
                    }
                    $tab_imputees[$last_key]['prio'] = $coulPriorte;
                }

                $instDossier = $this->getDoctrine()
                                   ->getRepository('AppBundle:ReleveManquant')
                                   ->getInstructions($value->dossier_id);

                if(count($instDossier) > 0){
                    $htmlIntruct = '<i class="fa fa-circle qtip_instruction pointer" style="color:#008000; padding-top: 3px;"></i>';
                    $dataIntruct = $instDossier[0]['isaisie'];
                } 

                if(!isset($htmlIntruct))
                    $htmlIntruct = '';

                $tab_imputees[$last_key]['t-inst'] = $htmlIntruct;

                if(!isset($dataIntruct))
                    $dataIntruct = '';

                $tab_imputees[$last_key]['t-data-inst'] = $dataIntruct;

                //alettrer
                /*foreach ($listeNonLettre as $v) $alettrer += $v->nb_non_lettre;
                foreach ($listeNonLettreBanque as $v) $alettrer += $v->nb;*/
                $tab_imputees[$last_key]['alettrer'] = 0;

                $code = '';
                if(!empty($value->banque_compte_id)){
                    $tab_imputees[$last_key]['etat'] = ($value->etat) ? $value->etat : '';
                    $banqueCompte = $this->getDoctrine()->getRepository('AppBundle:BanqueCompte')->find($value->banque_compte_id);
                    if($banqueCompte->getSourceImage() !== null){
                        if($banqueCompte->getSourceImage()->getSource() === 'SOBANK'){
                            $code = 'BI';
                        }
                    }
                }
                $tab_imputees[$last_key]['sb'] = $code;
                if(!empty($value->comptes)) {
                    $tab_imputees[$last_key]['m'] = 11;
                    $tab_imputees[$last_key]['importe'] = 4;
                    //rb1 ok
                    $param['dossier'] = $value->dossier_id;
                    $dataRb1AC = $this->getDoctrine()
                                      ->getRepository('AppBundle:Image')
                                      ->getRb1AControler($param);
                    $acontroler = $dataRb1AC['acontroler'];
                    $tab_imputees[$last_key]['acontroler'] = $acontroler.'-'.$dataRb1AC['imgSaisieKo'];
                }else{
                    $tab_imputees[$last_key]['m'] = '';
                    $tab_imputees[$last_key]['importe'] = '';
                    $tab_imputees[$last_key]['acontroler'] = '';
                }

                $tab_imputees[$last_key]['ob'] = '';
                $tab_imputees[$last_key]['data_ob_m'] = '';
                $last_key++;
            }
        }
        return $tab_imputees;
    }


    public function ifNull($value,$null = 0)
    {
        $value = ($value) ? $value :  $null;

        return $value;
    }


    public function stateImageGestionBilanAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() == 'POST') {
                $param = array();
                $client = $request->request->get('client');
                $param['dossier'] = $request->request->get('dossier');
                $param['exercice'] = $request->request->get('exercice');
                $param['responsable'] = $request->request->get('responsable');
                $param['isScriptura'] = true;
                $param['isEc'] = true;
                $param['isCf'] = true;
                if($param['responsable'] != ''){
                    switch ($param['responsable']) {
                        case 0:
                            $param['isScriptura'] = true;
                            $param['isEc'] = false;
                            $param['isCf'] = false;
                            break;
                        case 1:
                            $param['isScriptura'] = false;
                            $param['isEc'] = true;
                            $param['isCf'] = false;
                            break;
                        
                        default:
                            $param['isScriptura'] = false;
                            $param['isEc'] = false;
                            $param['isCf'] = true;
                            break;
                    }
                }
                $groupe = $request->request->get('groupe');


                if ( $client == 0 ) {

                    $clients = array();

                    /*Selection responsable*/
                    if ( $groupe != 0 ) {
                        $clients = $this->getDoctrine()
                                        ->getRepository('AppBundle:Client')
                                        ->getAllClientByResponsable($groupe);
                    } /*Tous les responsables*/
                    else {
                        $clients = $this->getDoctrine()
                                        ->getRepository('AppBundle:Client')
                                        ->getAllClient();
                    }
                    $param['client'] = [];
                    foreach ( $clients as $client ) {
                        array_push($param['client'], $client->id);
                    }
                } else {
                    $selectedClient = $this->getDoctrine()
                                           ->getRepository('AppBundle:Client')
                                           ->find($client);
                    $param['client'][] = $selectedClient->getId();
                }

                $sitImages = $this->getDoctrine()
                                  ->getRepository('AppBundle:Image')
                                  ->getListeImpute($param);

                $imputees = $this->getDetailImputees($sitImages['imputees'], $param);
                $rows = [];
                $liste = [];
                foreach ( $imputees as $key => $imputee ) {
                    $rows[] = [
                        'id' => $imputee['banque_compte_id'],
                        'cell' => [
                            't-client' => $imputee['clients'],
                            't-dossier' => $imputee['dossier'],
                            't_statut' => $imputee['dossier_status'],
                            't-tva' => $imputee['regime_tva'],
                            't-banque' => $imputee['banque'],
                            't-compte' => $imputee['comptes'],
                            't_image' => $imputee['color'],
                            't_ecart' => $imputee['ecart'],
                            't_rb' => $imputee['m'],
                            't_ob' => $imputee['ob'],
                            't-total' => $imputee['nb_r'],
                            't-lettre' => $imputee['nb_lettre'],
                            't-clef' => $imputee['nb_clef'],
                            't_alettre' => $imputee['alettrer'],
                            't-piece' => $imputee['nb_pc_manquant'],
                            't-cheque' => $imputee['chq_inconnu'],
                            't-rapproche' => $imputee['nbr_rapproche'],
                            't_rb2' => $imputee['importe'],
                            't-priorite' => $imputee['prio'],
                            't-acontroler' => $imputee['acontroler'],
                            't-ech' => $imputee['ech'],
                            't-data-ob-m' => $imputee['data_ob_m'],
                            't-sb' => $imputee['sb'],
                            't-aucun-image' => ($imputee['m'] == "") ? 11 : $imputee['m'],
                            't_etat' => $imputee['etat'],
                            't-data-tache' => $imputee['tache'],
                            't-respons' => $imputee['respons_tache'],
                            't_indicateur' => $imputee['t_indic'],
                            't_relbq' => $imputee['t_relbq'],
                            't-inst' => $imputee['t-inst'],
                            't-data-inst' => $imputee['t-data-inst']
                        ],
                    ];
                }
                $liste = [
                    'rows' => $rows,
                ];
                return new JsonResponse($liste);
            }
        }else{
            $responsables = $this->getDoctrine()
                                 ->getRepository('AppBundle:ResponsableScriptura')
                                 ->getSubDirections();

            $clients = $this->getDoctrine()
                            ->getRepository('AppBundle:Client')
                            ->getAllClient();

            sort($clients);
            $exercices = $this->getExercices(7, 2);

            return $this->render('BanqueBundle:Banque:gestion_bilan_situation_image.html.twig', [
                'clients' => $clients,
                'responsables' => $responsables,
                'exercices' => $exercices,
                'isGestion' => true
            ]);
        }
    }

    public function compteAttenteAction(Request $request)
    {
        $dossierid = $request->query->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $pccAttentes = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->getPccByDossierLike($dossier, '472');

        $select = '<select> <option></option>';

        /** @var Pcc $pccAttente */
        foreach ($pccAttentes as $pccAttente) {
            $select .= '<option value="' . $pccAttente->getId() . '">' . $pccAttente->getCompte() . '</option>';
        }

        $select .= '</select>';

        return new Response($select);

    }

    /**
     * TRAVAUX A FAIRE
     *
     * @method POST
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function tafAction(Request $request)
    {
        $result = array();

        $param = array();

        $client   = $request->request->get('client');
        $groupe   = $request->request->get('groupe');
        $param['dossier']  = $request->request->get('dossier');
        $param['exercice'] = $request->request->get('exercice');
        $param['periode']  = $request->request->get('periode');
        $param['perioddeb']= $request->request->get('perioddeb');
        $param['periodfin']= $request->request->get('periodfin');

        if ( $client == 0 ) {

            $clients = array();

            /*Selection responsable*/
            if ( $groupe != 0 ) {
                $clients = $this->getDoctrine()
                                ->getRepository('AppBundle:Client')
                                ->getAllClientByResponsable($groupe);
            } /*Tous les responsables*/
            else {
                $responsables = $this->getDoctrine()
                                     ->getRepository('AppBundle:ResponsableScriptura')
                                     ->getSubDirections();

                foreach ( $responsables as $responsable ) {

                    $clientsResp = $this->getDoctrine()
                                        ->getRepository('AppBundle:Client')
                                        ->getAllClientByResponsable($responsable['operateur_id']);

                    $clients = array_merge($clients, $clientsResp);

                }
            }
            $param['client'] = [];
            foreach ( $clients as $client ) {
                array_push($param['client'], $client->id);
            }
        }else {
            $selectedClient = $this->getDoctrine()
                                   ->getRepository('AppBundle:Client')
                                   ->find($client);
            $param['client'][] = $selectedClient->getId();
        }
        
        // RB1
        $dataRb1AC = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getRb1AControler($param);

        $dataRb1 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafData($param);

        $result[] = [
            'cell' => [
                'RB1',
                $dataRb1->nb,
                '',
                $dataRb1AC['trou'],
                $dataRb1AC['doublon'],
            ]
        ];

        // RB2
        $dataRb2 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafRb2Data($param);

        $dataRb2AC = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getRb2AControler($param);

        $result[] = [
            'cell' => [
                'RB2',
                $dataRb2,
                $dataRb2AC,
                '',
                ''
            ]
        ];

        // Carte crÃ©dit
        $data1 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafData($param,1);

        $result[] = [
            'cell' => [
                $data1->libelle_new,
                $data1->nb,
                '',
                '',
                ''
            ]
        ];

        // Virements
        $data6 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafData($param,6);

        $result[] = [
            'cell' => [
                $data6->libelle_new,
                $data6->nb,
                '',
                '',
                ''
            ]
        ];

        // Remise en banque
        $data7 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafData($param,7);

        $result[] = [
            'cell' => [
                $data7->libelle_new,
                $data7->nb,
                '',
                '',
                ''
            ]
        ];

        // Frais bancaire
        $data8 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafData($param,8);

        $result[] = [
            'cell' => [
                $data8->libelle_new,
                $data8->nb,
                '',
                '',
                ''
            ]
        ];

        // ChÃ¨ques
        $data153 = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getTafData($param,153);

        $result[] = [
            'cell' => [
                $data153->libelle_new,
                $data153->nb,
                '',
                '',
                ''
            ]
        ];

        return new JsonResponse($result);
        
    }

    public function tableauImageAction(Request $request){
        if ($request->isXmlHttpRequest()) {
            if ( $request->getMethod() == 'POST' ) {
                $client_id = $request->request->get('client');
                $banqueCompteId = $request->request->get('banqueCompteId');
                $exercice = $request->request->get('exercice');
                $dateScan = $request->request->get('dateScan');
                $param = array();
                $data = array();

                if ( $banqueCompteId != null ) {
                    $banqueCompte = $this->getDoctrine()
                                        ->getRepository('AppBundle:BanqueCompte')
                                        ->find($banqueCompteId);
                    $dossier = $banqueCompte->getDossier();
                    $listes = $this->getDoctrine()
                                   ->getRepository('AppBundle:Image')
                                   ->getListImageByCategorie($client_id, $dossier->getId(), $exercice);

                    $listesBanque = $this->getDoctrine()
                                   ->getRepository('AppBundle:Image')
                                   ->getListeImagesByCategorie($dossier->getId(), $exercice, 16, 'CODE_BANQUE');

                    $listesByBanqueSeparation = $this->getDoctrine()
                                                     ->getRepository('AppBundle:Image')
                                                     ->getListImageBanqueBySeparation($dossier->getId(), $exercice);

                    $listesSousCatBanq = $listesBanque['souscat'];
                    $listSousCatExist = array();
                    $nb_bank = 0;

                    foreach ($listesByBanqueSeparation as $val){
                        if(array_key_exists($val->libelle_new, $listesSousCatBanq)){
                            $val->nb_image = $val->nb_image + $listesSousCatBanq[$val->libelle_new];
                            $listSousCatExist[] = $val->libelle_new;
                        }
                        $nb_bank += $val->nb_image;
                    }

                    foreach ($listesSousCatBanq as $k=>$val1){
                        if(!in_array($k, $listSousCatExist)){
                            array_push($listesByBanqueSeparation, [
                                'nb_image' => $val1,
                                'libelle_new' => $k,
                            ]);
                            $nb_bank += $val1;
                        }
                    }
                    if(count($listesBanque['images'] > 0)) {
                        $data[0]['nb_image'] = $nb_bank;
                        $data[0]['libelle_new'] = 'BANQUES';
                        $data[0]['cat_id'] = 16;
                        $listes = array_merge($listes, $data);
                    }

                    /*$releveImageNonValide =  $this ->getDoctrine()
                                                   ->getRepository('AppBundle:Releve')
                                                   ->getReleveImageNonValideTous($dossier->getId(), $exercice);

                    $listeNonValider = [];

                    $listeNonValider = json_decode(json_encode($releveImageNonValide), true);
                    $countLibelleNonValider= array_count_values(array_column($listeNonValider, 'libelle_new'));
                    foreach ($listeNonValider as $key1 => &$value1) {
                        foreach ($countLibelleNonValider as $key2 => &$value2) {
                            if($value1['libelle_new'] == $key2){
                                $value1['nb_non_lettre'] = $value2;
                            }
                        }
                    }*/
                    $releveImageNonValide = $this ->getDoctrine()
                                                   ->getRepository('AppBundle:Releve')
                                                   ->getReleveImageNonValideBanque($dossier->getId(), $exercice);

                    $tableauLettre = [];

                    foreach ($releveImageNonValide as $key => $relImgNonVal) {
                        $montant = $relImgNonVal->montant;
                        $exerciceRel = $relImgNonVal->exercice;
                        $dossierRel = $relImgNonVal->dossier_id;
                        $tvaImputationControles = $this->getDoctrine()
                                                       ->getRepository('AppBundle:Image')
                                                       ->getListImageNonValiderImputaion($montant, $dossierRel, $exerciceRel, null, null, true, -1);
                        $banquesSousCategorieAutres = $this->getDoctrine()
                                                           ->getRepository('AppBundle:Image')
                                                           ->getListImageNonValiderSousBanque($montant, $dossierRel, $exerciceRel, null, null, true, -1);
                        $mergeNonlettre = array_merge($tvaImputationControles, $banquesSousCategorieAutres);
                        if(count($mergeNonlettre) > 0){
                            foreach ($mergeNonlettre as $key => $value) {
                                array_push($tableauLettre, $value);
                            }
                        }
                    }
                    //var_dump($tableauLettre);die;

                    $listeNonValider = [];
                    $listeNonValiderBanque = [];
                    $listeNonValider = json_decode(json_encode($tableauLettre), true);
                    $countLibelleNonValider = array_count_values(array_column($listeNonValider, 'libelle_new'));
                    foreach ($listeNonValider as $key1 => &$value1) {
                        foreach ($countLibelleNonValider as $key2 => &$value2) {
                            if($value1['libelle_new'] == $key2){
                                $value1['nb_non_lettre'] = $value2;
                            }
                        }
                    }
                    
                    $listeDateScan = $this->getDoctrine()
                                          ->getRepository('AppBundle:Image')
                                          ->getDateScan($client_id, $dossier->getId(), $exercice);

                    $param['dossier'] = $dossier->getId();
                    $param['cas'] = 4;
                    $param['exercice'] = $exercice;
                    $nbImagesEncours = $this->getDoctrine()
                                            ->getRepository('AppBundle:Image')
                                            ->getNbImageEncours($param);
                    if($dateScan == 'true'){
                        $debPeriode = $request->request->get('perioddeb');
                        $finPeriode = $request->request->get('periodfin');
                        $imageDateScan = $this->getDoctrine()
                                              ->getRepository('AppBundle:Image')
                                              ->getImageByDateScan($client_id, $dossier->getId(), $exercice, $debPeriode, $finPeriode);

                        //$listeNonValiderPeriode = [];
                        /*$releveImageNonValidePeriode =  $this ->getDoctrine()
                                                   ->getRepository('AppBundle:Releve')
                                                   ->getReleveImageNonValideTous($dossier->getId(), $exercice, $debPeriode, $finPeriode);

                        $listeNonValiderPeriode = json_decode(json_encode($releveImageNonValidePeriode), true);
                        $countLibelleNonValiderPeriode = array_count_values(array_column($listeNonValiderPeriode, 'libelle_new'));
                        foreach ($listeNonValiderPeriode as $key1 => &$value1) {
                            foreach ($countLibelleNonValiderPeriode as $key2 => &$value2) {
                                if($value1['libelle_new'] == $key2){
                                    $value1['nb_non_lettre'] = $value2;
                                }
                            }
                        }*/
                        $releveImageNonValidePeriode = $this ->getDoctrine()
                                                       ->getRepository('AppBundle:Releve')
                                                       ->getReleveImageNonValideBanque($dossier->getId(), $exercice, $debPeriode, $finPeriode);

                        /*$listeNonValiderPeriodeBanque = [];
                        $listeNonValiderPeriodeBanque = json_decode(json_encode($releveImageNonValidePeriodeBanque), true);
                        $countLibelleNonValiderPeriodeBanque = array_count_values(array_column($listeNonValiderPeriodeBanque, 'libelle_new'));
                        foreach ($listeNonValiderPeriodeBanque as $key1 => &$value1) {
                            foreach ($countLibelleNonValiderPeriodeBanque as $key2 => &$value2) {
                                if($value1['libelle_new'] == $key2){
                                    $value1['nb_non_lettre'] = $value2;
                                }
                            }
                        }*/
                        $tableauLettrePeriode = [];

                        foreach ($releveImageNonValidePeriode as $key => $relImgNonValPer) {
                            $montant = $relImgNonValPer->montant;
                            $exerciceRel = $relImgNonValPer->exercice;
                            $dossierRel = $relImgNonValPer->dossier_id;
                            $tvaImputationControles = $this->getDoctrine()
                                                           ->getRepository('AppBundle:Image')
                                                           ->getListImageNonValiderImputaion($montant, $dossierRel, $exerciceRel, null, null, true, -1);
                            $banquesSousCategorieAutres = $this->getDoctrine()
                                                               ->getRepository('AppBundle:Image')
                                                               ->getListImageNonValiderSousBanque($montant, $dossierRel, $exerciceRel, null, null, true, -1);
                            $mergeNonlettre = array_merge($tvaImputationControles, $banquesSousCategorieAutres);
                            if(count($mergeNonlettre) > 0){
                                foreach ($mergeNonlettre as $key => $value) {
                                    array_push($tableauLettrePeriode, $value);
                                }
                            }
                        }

                        $listeNonValiderPeriodeBanque = [];
                        $listeNonValiderPeriode = [];
                        $listeNonValiderPeriode = json_decode(json_encode($tableauLettrePeriode), true);
                        $countLibelleNonValiderPeriode = array_count_values(array_column($listeNonValiderPeriode, 'libelle_new'));
                        foreach ($listeNonValiderPeriode as $key1 => &$value1) {
                            foreach ($countLibelleNonValiderPeriode as $key2 => &$value2) {
                                if($value1['libelle_new'] == $key2){
                                    $value1['nb_non_lettre'] = $value2;
                                }
                            }
                        }

                        $listesBanqueByDate = $this->getDoctrine()
                                                     ->getRepository('AppBundle:Image')
                                                     ->getListeImagesByCategorie($dossier->getId(), $exercice, 16, 'CODE_BANQUE', $debPeriode, $finPeriode);

                        $listesByBanqueSeparationDate = $this->getDoctrine()
                                                             ->getRepository('AppBundle:Image')
                                                             ->getListImageBanqueBySeparation($dossier->getId(), $exercice, $debPeriode, $finPeriode);

                        $listesSousCatBanqByDate = $listesBanqueByDate['souscat'];
                        $listSousCatByDateExist = array();
                        $nb_bank_date = 0;
                        foreach ($listesByBanqueSeparationDate as $val){
                            if(array_key_exists($val->libelle_new, $listesSousCatBanqByDate)){
                                $val->nb_image = $val->nb_image + $listesSousCatBanqByDate[$val->libelle_new];
                                $listSousCatByDateExist[] = $val->libelle_new;
                            }
                            $nb_bank_date += $val->nb_image;
                        }

                        foreach ($listesSousCatBanqByDate as $k=>$val1){
                            if(!in_array($k, $listSousCatByDateExist)){
                                array_push($listesByBanqueSeparationDate, [
                                    'nb_image' => $val1,
                                    'libelle_new' => $k,
                                ]);
                                $nb_bank_date += $val1;
                            }
                        }

                        if(count($listesBanqueByDate['images'] > 0)) {
                            $data[0]['nb_image'] = $nb_bank_date;
                            $data[0]['libelle_new'] = 'BANQUES';
                            $data[0]['cat_id'] = 16;
                            $imageDateScan = array_merge($imageDateScan, $data);
                        }

                        $param['cas'] = 3;
                        $param['dateDeb'] = $debPeriode;
                        $param['dateFin'] = $finPeriode;
                        $nbImagesByDateScanEncours = $this->getDoctrine()
                                                            ->getRepository('AppBundle:Image')
                                                            ->getNbImageEncours($param);
                        return $this->render('BanqueBundle:Banque:tableau-image.html.twig', array(
                            'listes' => $listes,
                            'listesBanqueSousCat' => $listesByBanqueSeparation,
                            'listesBanqueSousCatParDate' => $listesByBanqueSeparationDate,
                            'listesNonLetrre' => $listeNonValider,
                            'listesNonLettreBanque' => $listeNonValiderBanque,
                            'listesParDateScanNonLettre' => $listeNonValiderPeriode,
                            'listesParDateScanNonLettreBanque' => $listeNonValiderPeriodeBanque,
                            'listeDateScan' => $listeDateScan,
                            'imageDateScan' => $imageDateScan,
                            'banqueCompteId' => $banqueCompteId,
                            'nbImagesEncours' => $nbImagesEncours,
                            'nbImagesByDateScanEncours' => $nbImagesByDateScanEncours,
                            'dateScan'      => true
                        ));
                    }else{
                        return $this->render('BanqueBundle:Banque:tableau-image.html.twig', array(
                            'listes' => $listes,
                            'listesBanqueSousCat' => $listesByBanqueSeparation,
                            'listesNonLetrre' => $listeNonValider,
                            'listesNonLettreBanque' => $listeNonValiderBanque,
                            'listeDateScan' => $listeDateScan,
                            'banqueCompteId'=> $banqueCompteId,
                            'nbImagesEncours' => $nbImagesEncours,
                            'nbImagesByDateScanEncours' => 0,
                            'dateScan'      => false
                        ));
                    }
                }
            }
        }
        return false;
    }

    public function detailTableauImageAction(Request $request){
        if ($request->isXmlHttpRequest()) {
            if ( $request->getMethod() == 'POST' ) {
                $client_id = $request->request->get('client');
                $banqueCompteId = $request->request->get('banqueCompteId');
                $exercice = $request->request->get('exercice');
                $categories = $request->request->get('categorieId');
                $indexTab = $request->request->get('index_tab');
                $isScan = $request->request->get('isScan');
                $param = array();
                $isBanque = false;
                $isEncours = false;

                $banqueCompte = $this->getDoctrine()
                                     ->getRepository('AppBundle:BanqueCompte')
                                     ->find($banqueCompteId);
                $dossier = $banqueCompte->getDossier();
                $categorieId = explode('-', $categories)[0];
                if($categorieId != ''){
                    $categorieLib = explode('-', $categories)[1];
                    if($isScan == 'scan'){
                        $debPeriode = $request->request->get('perioddeb');
                        $finPeriode = $request->request->get('periodfin');
                        if($categorieLib != '*'){
                            $listesBanque = $this->getDoctrine()
                                                 ->getRepository('AppBundle:Image')
                                                 ->getListeImagesByCategorie($dossier->getId(), $exercice, 16, 'CODE_BANQUE', $debPeriode, $finPeriode, $categorieLib);

                            $listesByBanqueSeparation = $this->getDoctrine()
                                                             ->getRepository('AppBundle:Image')
                                                             ->getListImageBanqueBySeparation($dossier->getId(), $exercice,$debPeriode, $finPeriode, $categorieLib);


                            $listes = $listesBanque['images'];
                            $isALettre = false;
                            if(count($listesBanque['images'] > 0)) {
                                $listes = array_merge($listes, $listesByBanqueSeparation);
                            }
                        }else{
                            $listes = $this->getDoctrine()
                                           ->getRepository('AppBundle:Image')
                                           ->getListImageDetailByDateScan($dossier->getId(), $exercice, $categorieId, $debPeriode, $finPeriode);
                            $isALettre = false;
                        }
                    }else if($isScan == 'scan-non-lettre'){
                        $debPeriode = $request->request->get('perioddeb');
                        $finPeriode = $request->request->get('periodfin');
                        if($categorieLib != '*'){
                            $listes = [];
                            $listesB = $this ->getDoctrine()
                                            ->getRepository('AppBundle:Releve')
                                            ->getReleveImageNonValideBanque($dossier->getId(), $exercice, $debPeriode, $finPeriode, true, -1);

                            $tableauLettrePeriode = [];

                            foreach ($listesB as $key => $relImgNonValPer) {
                                $montant = $relImgNonValPer->montant;
                                $exerciceRel = $relImgNonValPer->exercice;
                                $dossierRel = $relImgNonValPer->dossier_id;
                                $tvaImputationControles = $this->getDoctrine()
                                                               ->getRepository('AppBundle:Image')
                                                               ->getListImageNonValiderImputaion($montant, $dossierRel, $exerciceRel, $debPeriode, $finPeriode, true, $categorieLib);
                                $banquesSousCategorieAutres = $this->getDoctrine()
                                                                   ->getRepository('AppBundle:Image')
                                                                   ->getListImageNonValiderSousBanque($montant, $dossierRel, $exerciceRel, $debPeriode, $finPeriode, true, $categorieLib);
                                $mergeNonlettre = array_merge($tvaImputationControles, $banquesSousCategorieAutres);
                                if(count($mergeNonlettre) > 0){
                                    foreach ($mergeNonlettre as $key => $value) {
                                        array_push($tableauLettrePeriode, $value);
                                    }
                                }
                            }

                            if(count($tableauLettrePeriode) > 0){
                                foreach ($tableauLettrePeriode as $key => $value) {
                                    array_push($listes, $value);
                                }
                            }
                            $isALettre = true;
                        }else{
                            $listes = [];
                            $categorieLib = explode('-', $categories)[0];
                            $listesI = $this ->getDoctrine()
                                            ->getRepository('AppBundle:Releve')
                                            ->getReleveImageNonValideBanque($dossier->getId(), $exercice, $debPeriode, $finPeriode, true, -1);

                            $tableauLettrePeriode = [];

                            foreach ($listesI as $key => $relImgNonValPer) {
                                $montant = $relImgNonValPer->montant;
                                $exerciceRel = $relImgNonValPer->exercice;
                                $dossierRel = $relImgNonValPer->dossier_id;
                                $tvaImputationControles = $this->getDoctrine()
                                                               ->getRepository('AppBundle:Image')
                                                               ->getListImageNonValiderImputaion($montant, $dossierRel, $exerciceRel, $debPeriode, $finPeriode, true, $categorieLib);
                                $banquesSousCategorieAutres = $this->getDoctrine()
                                                                   ->getRepository('AppBundle:Image')
                                                                   ->getListImageNonValiderSousBanque($montant, $dossierRel, $exerciceRel, $debPeriode, $finPeriode, true, $categorieLib);
                                $mergeNonlettre = array_merge($tvaImputationControles, $banquesSousCategorieAutres);
                                if(count($mergeNonlettre) > 0){
                                    foreach ($mergeNonlettre as $key => $value) {
                                        array_push($tableauLettrePeriode, $value);
                                    }
                                }
                            }

                            if(count($tableauLettrePeriode) > 0){
                                foreach ($tableauLettrePeriode as $key => $value) {
                                    array_push($listes, $value);
                                }
                            }
                            $isALettre = true;
                        }
                    }else if($isScan == 'tous'){
                        if($categorieLib != '*'){
                            $listesBanque = $this->getDoctrine()
                                                 ->getRepository('AppBundle:Image')
                                                 ->getListeImagesByCategorie($dossier->getId(), $exercice, 16, 'CODE_BANQUE', null, null, $categorieLib);

                            $listesByBanqueSeparation = $this->getDoctrine()
                                                             ->getRepository('AppBundle:Image')
                                                             ->getListImageBanqueBySeparation($dossier->getId(), $exercice,null, null, $categorieLib);

                            $listes = $listesBanque['images'];
                            $isALettre = false;
                            if(count($listesBanque['images'] > 0)) {
                                $listes = array_merge($listes, $listesByBanqueSeparation);
                            }
                        }else{
                            $listes = $this->getDoctrine()
                                           ->getRepository('AppBundle:Image')
                                           ->getListImageDetailByCategorie($client_id, $dossier->getId(), $exercice, $categorieId);
                            $isALettre = false;
                        }
                    }else{
                        if($categorieLib != '*') {
                            $listes = [];
                            $listesB =  $this->getDoctrine()
                                            ->getRepository('AppBundle:Releve')
                                            ->getReleveImageNonValideBanque($dossier->getId(), $exercice, null, null, true, $categorieLib);
                            if(count($listesB) > 0){
                                foreach ($listesB as $key => $value) {
                                    array_push($listes, $value);
                                }
                            }
                            $isALettre = true;
                        }else{
                            $listes = [];
                            $categorieLib = explode('-', $categories)[0];
                            $listesI =  $this->getDoctrine()
                                            ->getRepository('AppBundle:Releve')
                                            ->getReleveImageNonValideBanque($dossier->getId(), $exercice, null, null, true, -1);

                            $tableauLettrePeriode = [];

                            foreach ($listesI as $key => $relImgNonValPer) {
                                $montant = $relImgNonValPer->montant;
                                $exerciceRel = $relImgNonValPer->exercice;
                                $dossierRel = $relImgNonValPer->dossier_id;
                                $tvaImputationControles = $this->getDoctrine()
                                                               ->getRepository('AppBundle:Image')
                                                               ->getListImageNonValiderImputaion($montant, $dossierRel, $exerciceRel, null, null, true, $categorieLib);
                                $banquesSousCategorieAutres = $this->getDoctrine()
                                                                   ->getRepository('AppBundle:Image')
                                                                   ->getListImageNonValiderSousBanque($montant, $dossierRel, $exerciceRel, null, null, true, $categorieLib);
                                $mergeNonlettre = array_merge($tvaImputationControles, $banquesSousCategorieAutres);
                                if(count($mergeNonlettre) > 0){
                                    foreach ($mergeNonlettre as $key => $value) {
                                        array_push($tableauLettrePeriode, $value);
                                    }
                                }
                            }

                            if(count($tableauLettrePeriode) > 0){
                                foreach ($tableauLettrePeriode as $key => $value) {
                                    array_push($listes, $value);
                                }
                            }
                            $isALettre = true;
                        }
                    }
                }else{
                    $param['dossier'] = $dossier->getId();
                    $param['cas'] = 4;
                    $param['exercice'] = $exercice;
                    if($isScan == 'scan'){
                        $debPeriode = $request->request->get('perioddeb');
                        $finPeriode = $request->request->get('periodfin');
                        $param['cas'] = 3;
                        $param['dateDeb'] = $debPeriode;
                        $param['dateFin'] = $finPeriode;
                    }
                    $listes = $this->getDoctrine()
                                   ->getRepository('AppBundle:Image')
                                   ->getNbImageEncours($param, true);
                    $categorieId = 0;
                    $isALettre = false;
                    $isEncours = true;
                }

                $rows = [];
                $tableauImage = [];
                $tableauLettre = [];
                foreach ( $listes as $liste ) {
                    //rapprochement
                    //flaguer piece
                    $isRapprochement = false;
                    if($isALettre){
                        $releveImageNonValide =  $this ->getDoctrine()
                                                       ->getRepository('AppBundle:Releve')
                                                       ->getReleveImageNonValide($dossier->getId(), $exercice);
                        foreach ($releveImageNonValide as $key => $rel) {
                            if($rel->montant == $liste->montant){
                                $lettre = $rel->libelle;
                                $imageIdRap = $rel->relId.'-alettre';
                            }
                        }
                        /*$listeNonLettreImputaion =$this->getDoctrine()
                                                       ->getRepository('AppBundle:Image')
                                                       ->getListImageNonValiderImputaion($liste->montant, $dossier->getId(), $exercice, null, null, true, -1);
                        $listeNonLettreSousBanque =$this->getDoctrine()
                                                        ->getRepository('AppBundle:Image')
                                                        ->getListImageNonValiderSousBanque($liste->montant, $dossier->getId(), $exercice, null, null, true, -1);
                        $mergeNonlettre = array_merge($listeNonLettreImputaion, $listeNonLettreSousBanque);
                        if(count($mergeNonlettre) > 0){
                            foreach ($mergeNonlettre as $key => $value) {
                                array_push($tableauLettre, $value);
                            }
                        }*/
                        $isRapprochement = true;
                        $icon = '';
                    }else{
                        $flaguePiece = $this->getDoctrine()
                                            ->getRepository('AppBundle:TvaImputationControle')
                                            ->getImageFlaguesByImageid($liste->imageId);
                        if(count($flaguePiece) > 0){
                            $lettre = ($flaguePiece[0]->num_facture) ? $flaguePiece[0]->libelle.'-'.$flaguePiece[0]->num_facture : $flaguePiece[0]->libelle;
                            $isRapprochement = true;
                            $icon = '<i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;';
                            $imageIdRap = $flaguePiece[0]->image_id.'-lettre';
                        } 
                    }


                    $rapprochement = "Non";
                    if($isRapprochement){
                        $class = 'pointer show_lettrage_rapprochement';
                        $rapprochement = '<span class="text-success '.$class.'" data-id="'.$imageIdRap.'">'.$icon.$lettre.'</span>';
                    }/*else if(count($tableauLettre) > 2){
                        $rapprochement = '<span class="text-success pointer show_multiple_lettre" data-id="'.$liste->imageId.'"><i class="fa fa-files-o"></i>&nbsp;&nbsp;Multiple</span>';
                    }*/

                    $avancement = 'Reçu';
                    if ($liste->imputation > 1) {
                        $avancement = 'Imputée';
                    }
                    $rows[] = [
                        'tb_detail_image' =>  $liste->image,
                        'tb_detail_datescan' => $liste->date_scan,
                        'tb_detail_datepiece' => $liste->date_piece,
                        'tb_detail_rs' => $liste->rs,
                        'tb_detail_avancement' => $avancement,
                        'tb_detail_rapprochement' => $rapprochement,
                        'tb_image_id' => $liste->imageId,
                        'tb_categorie_id' => $categorieId,
                        'tb_checkbox' => ($liste->imageId == $liste->prioriteImageId) ? 1 : 0
                    ];
                    //image pdf
                    /** @var Image $image */
                    $image = $this->getDoctrine()
                                  ->getRepository('AppBundle:Image')->createQueryBuilder('im')
                                  ->where('im.id = :id')
                                  ->setParameter('id', $liste->imageId)
                                  ->getQuery()
                                  ->getOneOrNullResult();

                    /*$extension = 'jpg';
                    $image_exts = [
                        'jpg',
                        'png',
                        'jpeg',
                        'pdf',
                    ];*/
                    $dateScanFomated = $image->getLot()->getDateScan()->format('Ymd');
                    $chemin = 'https://www.lesexperts.biz/IMAGES/' . $dateScanFomated . '/' . $image->getNom() .'.'. $image->getExtImage();
                    /*$file_headers = get_headers($chemin);
                    $isExist = false;
                    if ($file_headers[7] == 'HTTP/1.1 200 OK' || $file_headers[0] == 'HTTP/1.1 200 OK') {
                        $isExist = true;
                    }*/
                    //$isExist = $this->remote_file_exists($chemin);
                    /*if(!$isExist){
                        $match = false;
                        foreach ($image_exts as $ext) {
                            if($ext != $image->getExtImage()){
                                $chemin = 'https://www.lesexperts.biz/IMAGES/' . $dateScanFomated . '/' . $image->getNom() .'.'. $ext;
                                $file_headers = get_headers($chemin);
                                if ($file_headers[7] == 'HTTP/1.1 200 OK' || $file_headers[0] == 'HTTP/1.1 200 OK') {
                                    $isExist = true;
                                    $match = true;
                                    break;
                                }
                            }
                        }*/
                        //mamadika image ho pdf=>msmapiakatra anazy any am OVH (mandeha fa mavesatra lotra)
                        /*if ($match) {
                            $new_filename = 'https://www.lesexperts.biz/IMAGES/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
                            $directory = "IMAGES";
                            $fs = new Filesystem();
                            try { $fs->mkdir($directory,0777); } catch (IOExceptionInterface $e) { }
                            $directory .= '/'.$dateScanFomated;
                            try { $fs->mkdir($directory,0777); } catch (IOExceptionInterface $e) { }
                            $dir = $this->get('kernel')->getRootDir() . '/../web/IMAGES/'.$dateScanFomated.'/';
                            $pdf = new TCPDF();
                            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                            $pdf->SetMargins(0, 0, 0, 0);
                            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                            $pdf->setPrintHeader(false);
                            $pdf->setPrintFooter(false);
                            $pdf->SetAutoPageBreak(false);
                            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                            $pdf->AddPage();
                            $pdf->Image($chemin, 0, 0, 215, 355, '', 'http://www.tcpdf.org', '', true, 150);
                            $name = $image->getNom().'.pdf';
                            $pdf->Output($dir.$name, 'F');


                            $ftp_server = "ns315229.ip-37-59-25.eu";
                            $ftp_user_name = "images";
                            $ftp_user_pass = "wAgz37^8";
                            $destination_file = $dateScanFomated. '/' . $image->getNom() . '.pdf';

                            $conn_id = ftp_connect($ftp_server);
                            ftp_pasv($conn_id, true);
                            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
                            if ((!$conn_id) || (!$login_result)) {
                                return new JsonResponse(array('type' => 'error', 'message' => 'FTP connection failed'));
                            }

                            $source_file = $dir.$name;

                            ftp_pasv($conn_id, true);
                            $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
                            $chemin = $source_file;
                        }
                    }*/
                    $imgLists = ['jpg', 'jpeg', 'tiff', 'gif', 'tif', 'png'];
                    $ch_ext = array_reverse(explode('.', $chemin))[0];
                    if(in_array(strtolower($ch_ext), $imgLists)){
                        $tableauImage[] = [
                            'imageId' => $liste->imageId,
                            'embed' => '<img src="'.$chemin.'" style="width:100%;">'
                        ];
                    }else if(strtolower($ch_ext) === "pdf"){
                        $tableauImage[] = [
                            'imageId' => $liste->imageId,
                            'embed' => '<object id="js_embed" 
                                            width="100%" 
                                            height="100%" 
                                            type="application/pdf" 
                                            trusted="yes" 
                                            application="yes" 
                                            title="IMAGE" 
                                            data="' . $chemin .
                                       '?#scrollbar=1&toolbar=0&navpanes=1">
                                           <p>Votre  navigateur ne peut pas affichier le fichier PDF. Vous pouvez le télÃécharger en cliquant <a target="_blank" href="' . $chemin . '" style="text-decoration: underline;">ICI</a></p>
                                        </object>'
                        ];
                    }
                    else{
                        $tableauImage[] = [
                            'imageId' => $liste->imageId,
                            'embed' => '<p>Votre  navigateur ne peut pas affichier ce type de fichier. Vous pouvez le télécharger en cliquant <a target="_blank" href="' . $chemin . '" style="text-decoration: underline;">ICI</a></p>'
                        ];
                    }

                }
               $data = json_encode($rows);
                return $this->render('BanqueBundle:Banque:tableau-detail-image.html.twig', array(
                    'tableauImage' => $tableauImage,
                    'data' => $data,
                    'indexTab'  => $indexTab,
                    'categorieId'  => $categorieId,
                    'isEncours'  => $isEncours
                ));
            }
        }
    }

    public function changeStatutTableauImageAction(Request $request){
        if ($request->isXmlHttpRequest()) {
            if ( $request->getMethod() == 'POST' ) {
                $imageId = $request->request->get('imageId');
                $statutSelect = $request->request->get('statut_select');

                $image = $this->getDoctrine()
                              ->getRepository('AppBundle:Image')
                              ->find($imageId);

                $em = $this->getDoctrine()
                           ->getManager();

                if($statutSelect == 1){
                    $image->setANePasTraiter(1);
                    $em->flush();
                }else{
                    $souscategorie = $this->getDoctrine()
                                          ->getRepository('AppBundle:Souscategorie')
                                          ->find($statutSelect);

                    $separation = $this->getDoctrine()
                                       ->getRepository('AppBundle:Separation')
                                       ->findBy(array(
                                           'image' => $imageId,
                                           'souscategorie' => $statutSelect
                                       ));
                    $categorie = $souscategorie->getCategorie();
                    if(count($separation) == 0){
                        $separation = new Separation();
                        $separation->setSouscategorie($souscategorie)
                                   ->setCategorie($categorie)
                                   ->setOperateur($this->getUser())
                                   ->setImage($image);
                        $em->persist($separation);
                        $em->flush();
                    }
                }
                return new JsonResponse('SUCCESS');
            }
        }
    }

    function remote_file_exists($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch,CURLOPT_HEADER,false);
        $response = curl_exec($ch);
        $target = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($target)
            return true;
        return false;
        //return (@fopen($file, "r")) ? true : false;
    }

    public function getImputees($data_imputees, $param){
        $tab_imputees = array();
        $tab_key_mois = array();
        $tab_exist_comptes = array();
        $tab_exist_client = array();
        $tab_exist_dossier = array();
        $last_key = 0;
        $exercice = $param['exercice'];
        $dossier = $param['dossier'];
        $client = $param['client'];
        $param['periode'] = 4; // tout l'exercice
        $betweens = array();
        $ajour = 0;
        $m_1 = 0;
        $m_2 = 0;
        $incompl = 0;
        $aucun = 0;

        foreach ($data_imputees as $key => $value) {
            if ( !empty($value->mois)) {
                if($value->status == 1 || $param['dossier'] != 0){
                    $tab_mois_manquant = explode(',', $value->mois);
                    $mois_manquant = str_replace(' ', '', $tab_mois_manquant);
                    //fin mois cloture
                    if ( $value->cloture < 9 ) {
                        $debut_mois = ($exercice - 1) . '-0' . ($value->cloture + 1) . '-01';
                    } else if ( $value->cloture >= 9 and $value->cloture < 12 ) {
                        $debut_mois = ($exercice - 1) . '-' . ($value->cloture + 1) . '-01';
                    } else {
                        $debut_mois = ($exercice) . '-01-01';
                    }
                    //debut mois cloture
                    if ( $value->cloture < 10 ) {
                        $fin_mois = ($exercice) . '-0' . ($value->cloture) . '-01';
                    } else {
                        $fin_mois = ($exercice) . '-' . ($value->cloture) . '-01';
                    }

                    /*$tab_mois_cloture = $this->getBetweenDate($debut_mois, $fin_mois);*/

                    $k = array_key_exists($debut_mois . '-' . $fin_mois, $betweens);
                    if ( $k ) {
                        $tab_mois_cloture = $betweens[$debut_mois . '-' . $fin_mois];
                    } else {
                        $tab_mois_cloture = $this->getBetweenDate($debut_mois, $fin_mois);

                        $betweens[$debut_mois . '-' . $fin_mois] = $tab_mois_cloture;

                    }

                    $nb_m_mois_exist = false;
                    switch ( count($mois_manquant) ) {
                        case 0:
                            $nb_m_mois_exist = true;
                            $ajour += 1;
                            break;
                        case 1:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 2:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 3:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 12:
                            $nb_m_mois_exist = true;
                            //jerena aloha raha mis relevÃ© ihany le banque amin'ny alalan'ny dossier
                            $resReleves = $this->getDoctrine()
                                               ->getRepository('AppBundle:Image')
                                               ->getInfoReleveByDossier($value->banque_compte_id, $exercice);
                            if ( count($resReleves) > 0 )
                                $incompl += 1;
                            else
                                $aucun += 1;
                            break;
                        default:
                            $nb_m_mois_exist = true;
                            $incompl += 1;
                            break;
                    }

                    if ( !$nb_m_mois_exist ) {
                        $min = 13;
                        $now = new \DateTime();
                        foreach ( $tab_key_mois[$key] as $key_m => $key_mois_m ) {
                            if ( $key_m < $min ) {
                                $min = $key_m;
                            }
                        }
                        //Jerena aloha raha misy tsy ampy eo ampovoany
                        $continue = true;
                        $lastIndex = -1;
                        foreach ( $tab_key_mois[$key] as $k => $v ) {
                            if ( $lastIndex === -1 ) {
                                $lastIndex = $k;
                                continue;
                            }
                            if ( $lastIndex + 1 !== $k ) {
                                $continue = false;
                                break;
                            } else {
                                $lastIndex = $k;
                            }
                        }

                        if ( $continue ) {
                            if ( intval($exercice) < $now->format('Y') ) {
                                switch ( $min ) {
                                    case 11:
                                        $ajour += 1;
                                        break;
                                    case 10:
                                        $m_1 += 1;
                                        break;
                                    case 9:
                                        $m_2 += 1;
                                        break;
                                    default:
                                        $incompl += 1;
                                        break;
                                }
                            } else {
                                if ( array_key_exists($min, $tab_key_mois[$key]) ) {
                                    $now = new \DateTime();
                                    $datemin = \DateTime::createFromFormat('Y-m-d', $tab_key_mois[$key][$min] . "-06");

                                    $interval = $now->diff($datemin);
                                    $diff = $interval->m + 1;

                                    if ( $diff === 0 ) {
                                        $ajour += 1;
                                    } elseif ( $diff === 1 ) {
                                        $m_1 += 1;
                                    }elseif ( $diff > 1 ) {
                                        $m_2 += 1;
                                    } else {
                                        $incompl += 1;
                                    }
                                } else {
                                    $incompl += 1;
                                }
                            }
                        } else {
                            $incompl += 1;
                        }
                    }
                }
            } else {
                $ajour += 1;
            }

            if(!in_array($value->numcompte, $tab_exist_comptes)){
                $tab_exist_comptes[] = $value->numcompte;
            }

            if(!in_array($value->client_nom, $tab_exist_client))
                $tab_exist_client[] = $value->client_nom;

            if(!in_array($value->dossier_nom, $tab_exist_dossier))
                $tab_exist_dossier[] = $value->dossier_nom;
        }

        $listSansImage = $this->getDoctrine()
                              ->getRepository('AppBundle:Image')
                              ->getListeImputeSansImage($client, $dossier, $tab_exist_comptes);

        foreach ($listSansImage as $key=>$v){
            if(!empty($v->comptes)) {
                if($v->status == 1){
                    $aucun += 1;
                    if(!empty($v->dossier)){
                        if(!in_array($v->dossier, $tab_exist_dossier))
                            $tab_exist_dossier[] = $v->dossier;
                    }
                    if(!empty($v->clients)){
                        if(!in_array($v->clients, $tab_exist_client))
                            $tab_exist_client[] = $v->clients;
                    }
                }
            }
        }
        $tab_imputees['ajour'] = $ajour;
        $tab_imputees['m_1'] = $m_1;
        $tab_imputees['m_2'] = $m_2;
        $tab_imputees['incompl'] = $incompl;
        $tab_imputees['aucun'] = $aucun;
        $tab_imputees['totalCompte'] = $ajour + $m_1 + $m_2 + $incompl + $aucun;
        $tab_imputees['nbDossier'] = count($tab_exist_dossier);
        $tab_imputees['nbClient'] = count($tab_exist_client);
        return $tab_imputees;
    }

    public function stateImageShowRapprochementAction(Request $request){
        $releveId = $request->request->get('imageId');
        $tableauLettre = [];
        $releve = $this->getDoctrine()
                       ->getRepository('AppBundle:Releve')
                       ->find($releveId);
        $montant = $releve->getCredit() - $releve->getDebit();
        $exercice = $releve->getImage()->getExercice();
        $dossier = $releve->getBanqueCompte()->getDossier()->getId();
        $tvaImputationControles = $this->getDoctrine()
                                       ->getRepository('AppBundle:Image')
                                       ->getListImageNonValiderImputaion($montant, $dossier, $exercice, null, null, true, -1);
        $banquesSousCategorieAutres = $this->getDoctrine()
                                           ->getRepository('AppBundle:Image')
                                           ->getListImageNonValiderSousBanque($montant, $dossier, $exercice, null, null, true, -1);
        $mergeNonlettre = array_merge($tvaImputationControles, $banquesSousCategorieAutres);
        if(count($mergeNonlettre) > 0){
            foreach ($mergeNonlettre as $key => $value) {
                array_push($tableauLettre, $value);
            }
        }

        $datas = [];
        foreach ($tableauLettre as $value){
            $datas[] = (object)
            [
                'date' => ($value->date_piece != null) ? $value->date_piece : new \DateTime(),
                'image' => $value->image,
                'libelle' => $value->libelle,
                'montant' => number_format($value->montant, 2, ',', ' '),
                'categorie' => $value->libelle_new
            ];
        }
        $datas[] = (object)
        [
            'date' => $releve->getDateReleve(),
            'image' => $releve->getImage()->getNom(),
            'libelle' => $releve->getLibelle(),
            'montant' => number_format($montant, 2, ',', ' '),
            'categorie' => 'RelevÃ©s bancaires'
        ];
        return $this->render('BanqueBundle:Banque:tableau-rapprochement.html.twig', ['datas' => $datas]);
    }

    public function listClientsSituationImageByResponsableAction($responsable)
    {

        if ($responsable != 0) {
            
            $clients = $this->getDoctrine()
                    ->getRepository('AppBundle:Client')
                    ->getAllClientByResponsable($responsable);
            
            return new JsonResponse($clients);
        }

        else{

            $clientsObjet = $this->getDoctrine()
                                 ->getRepository('AppBundle:Client')
                                 ->getAllClient();
            $clients = array();

            foreach ($clientsObjet as $client) {
                array_push($clients, [
                    'id' => $client->getId(),
                    'nom' => $client->getNom()
                ]);
            }

            return new JsonResponse($clients);
        }


    }

    public function etatBanqueCompteAction(Request $request)
    {
        $banqueCompteId = $request->request->get('bcId');
        $etat = $request->request->get('etat');
        $etat = ($etat == 'true') ? 1 : 0;
        $q = 'UPDATE banque_compte SET etat="' . $etat . '" WHERE id=' . $banqueCompteId;
        $this->pdo->exec($q);
        return new JsonResponse($etat);
    }

    public function prioriteImageAction(Request $request)
    {
        $data = $request->request->get('data');
        $em = $this->getDoctrine()->getManager();
        foreach ($data as $k => $v) {
            if($v['checked'] == 'true'){
                $prioriteImage = $this->getDoctrine()
                                      ->getRepository('AppBundle:PrioriteImage')
                                      ->findBy(array(
                                           'imageId' => $v['imageId']
                                       ));
                if(count($prioriteImage) == 0){
                    $priorite_image = new PrioriteImage();
                    $priorite_image->setImageId($v['imageId']);
                    $em->persist($priorite_image);
                    $em->flush();
                }
            }else{
                $prioriteImage = $this->getDoctrine()
                                      ->getRepository('AppBundle:PrioriteImage')
                                      ->findBy(array(
                                           'imageId' => $v['imageId']
                                       ));
                if(count($prioriteImage) > 0){
                    $em->remove($prioriteImage[0]);
                    $em->flush();
                }
            }
        }
        return new JsonResponse('SUCCESS');
    }

    public function traitementImageBanque($images, $imageSeparations, $categorieId)
    {
        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find($categorieId);
        if(null !== $categorie){
            $categorieCode = $categorie->getCode();
        }else{
            $categorieCode = $categorieId;
        }
        $rows = [];
        if ($images != null) {
            foreach ($images as $image) {
                $results = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getInfosImageByImageId($image->getId());
                if ($results !== null) {
                    $res0 = $results['saisie'][0];
                    $tableSaisie = $results['tableSaisie'];
                    $categorieLib = '';
                    $sousCategorieLib = '';
                    $ssCategorieLib = '';

                    $trouveCategorie = false;
                    $resSeparation = $results['separation'];

                    if($resSeparation !== null) {
                        if($resSeparation->getCategorie() !== null){
                            $categorieLib = $resSeparation->getCategorie()
                                ->getLibelleNew();
                        }

                        if($resSeparation->getSouscategorie() !== null) {
                            $sousCategorieLib = $resSeparation->getSouscategorie()
                                ->getLibelleNew();
                            $trouveCategorie = true;
                        }

                        if($resSeparation->getSoussouscategorie() !== null){
                            $ssCategorieLib = $resSeparation->getSoussouscategorie()
                                ->getLibelleNew();
                        }
                    }

                    if(!$trouveCategorie) {
                        if ($tableSaisie === 'ImputÃ©e') {
                            if ($listeSousCategorie != null) {

                               $trouveCategorie = true;

                                foreach ($listeSousCategorie as $sousCategorie) {
                                    if ($sousCategorieLib == '') {
                                       $sousCategorieLib = $sousCategorie;
                                    } else {
                                        $sousCategorieLib = $sousCategorieLib . ', ' . $sousCategorie;
                                    }
                               }
                            }
                            if ($listeSsCategorie != null) {

                               $trouveCategorie = true;

                                foreach ($listeSsCategorie as $ssCategorie) {

                                    if ($ssCategorieLib == '') {
                                       $ssCategorieLib = $ssCategorie;
                                    } else {
                                       $ssCategorieLib = $ssCategorieLib . ', ' . $ssCategorie;
                                    }
                                }
                            }
                        }

                        if ($res0->getSoussouscategorie() !== null) {
                            if ($tableSaisie !== 'ImputÃ©e') {
                               $sousCategorieLib = $res0->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                               $ssCategorieLib = $res0->getSoussouscategorie()->getLibelleNew();
                            } else if ($trouveCategorie === false) {
                               $sousCategorieLib = $res0->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                               $ssCategorieLib = $res0->getSoussouscategorie()->getLibelleNew();
                            }
                            $categorieLib = $res0->getSoussouscategorie()->getSouscategorie()->getCategorie()->getLibelleNew();
                        } else {

                           $trouveCategorieLib = false;

                            if ($tableSaisie === 'ImputÃ©e') {
                                if ($res0->getSouscategorie() != null) {
                                   $categorieLib = $res0->getSouscategorie()->getCategorie()->getLibelleNew();
                                   $trouveCategorieLib = true;
                                }
                            }

                           //AFFICHER-NA FOTSINY ILAY CATEGORIE SELECTIONNE
                            if (null !== $categorie && $trouveCategorieLib == false) {
                               $categorieLib = $categorie->getLibelleNew();
                            } else {
                               $trouveCategorieImputation = false;
                               //JERENA NY SOUSCATEGORIE RAHA IMPUTATION
                               if ($tableSaisie === 'ImputÃ©e') {
                                   if ($res0->getSouscategorie() !== null) {
//                                    $categorieLib = $res0->getSouscategorie()->getCategorie()->getLibelle();
                                       $trouveCategorieImputation = true;
                                    }

                                }
                               //JERENA NY ANY @ SEPARATION
                               if (!$trouveCategorieImputation) {
                                   if ($resSeparation !== null) {
                                       /** @var $resSeparation Separation */
                                       if ($resSeparation->getCategorie() != null) {
                                           $categorieLib = $resSeparation->getCategorie()->getLibelleNew();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $posScat = strpos(strtolower($sousCategorieLib), 'doublon');
                    $posMalScat = strpos(strtolower($sousCategorieLib), 'mal aff');
                    $posSscat = strpos(strtolower($ssCategorieLib), 'doublon');
                    $posMalSscat = strpos(strtolower($ssCategorieLib), 'mal aff');
                    if ($posScat !== false || $posSscat !== false || $posMalScat !== false || $posMalSscat !== false) {
                        continue;
                    }

                    //Tsy afficher-na izay catÃ©gorie tsy izy
                    if(null !== $categorie){
                        if($categorie->getLibelleNew() != $categorieLib){
                            continue;
                        }
                    }


                    //Tsy affiche-na raha tsy misy catÃ©gorie
                    if($res0->getSoussouscategorie() == null){
                        if($tableSaisie === 'ImputÃ©e'){
                            if($res0->getSouscategorie() == null && $categorieLib == ''){
                                continue;
                            }
                        }
                    }


                    $rows[] = $sousCategorieLib ? $sousCategorieLib : $ssCategorieLib;
                }
            }
        }

        if($imageSeparations != null) {
            foreach ($imageSeparations as $imageSeparation) {
                /** @var $imageSeparation Separation */

                $soussouscategorieValide = true;

                //Raha misy soussouscategorie dia alaina avy @ soussouscategorie ny categorie
                if ($imageSeparation->getSoussouscategorie() != null) {

                    $cat = $imageSeparation->getSoussouscategorie()->getSouscategorie()->getCategorie();

                    //Raha tsy mitovy ny categorie an'ilay soussouscategorie & categorie selectionnÃ©
                    if ($categorieCode != -1 && $cat->getCode() != $categorieCode) {
                        $soussouscategorieValide = false;
                    }

                    $categorieLib = $imageSeparation->getSoussouscategorie()->getSouscategorie()->getCategorie()->getLibelleNew();
                    $sousCategorieLib = $imageSeparation->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                    $ssCategorieLib = $imageSeparation->getSoussouscategorie()->getLibelleNew();
                    $rows[] = $sousCategorieLib ? $sousCategorieLib : $ssCategorieLib;
                }
            }
        }

        $sousCatExist = [];
        $listes = [];
        foreach ( $rows as $value ) {
            if ( !in_array($value, $sousCatExist) ) {
                $listes[$value] = 1;
                $sousCatExist[] = $value;
            } else
                $listes[$value]++;
        }
        return $listes;
    }

    public function nbHeureTravail(\DateTime $start, \DateTime $end)
    {
        $debut = new \DateTime($start->format('Y-m-d'));
        $fin = new \DateTime($end->format('Y-m-d'));
        $nbHeure = 0;
        $hours = [];
        $param = $this->getDoctrine()
                      ->getRepository('AppBundle:PrioriteParam')
                      ->findOneBy(array(
                        'paramName' => 'priorite_jour'
                      ));
//        {"weekday":1,"checked":true,"heure":8}
        if ($param) {
            foreach ($param->getParamValue() as $value) {
                $hours[$value['weekday']] = $value;
            }
        }

        while ($debut < $fin) {
            $weekday = $debut->format('N');
            if (isset($hours[$weekday]) && $hours[$weekday]['checked'] === true) {
                $nbHeure += intval($hours[$weekday]['heure']);
            }
            $debut->add(new \DateInterval('P1D'));
        }

        return $nbHeure;
    }

    public function checkWeekend(\DateTime $date)
    {
        $tmp = clone $date;
        if ($tmp->format('w') == 6) {
            $tmp->add(new \DateInterval('P2D'));
        }
        if ($tmp->format('w') == 0) {
            $tmp->add(new \DateInterval('P1D'));
        }
        return $tmp;
    }

    public function showListeColonneAction()
    {
        return $this->render('BanqueBundle:Banque:list-title-name-column.html.twig');
    }

    public function showLogBanqueManquanteAction(Request $request)
    {
        $dossier = $request->request->get('dossier');
        $index = $request->request->get('index');
        $dossier = $this->getDoctrine()
                        ->getRepository('AppBundle:Dossier')
                        ->findBy(array('nom' => $dossier));
        $emails = $this->getDoctrine()
                        ->getRepository('AppBundle:Emails')
                        ->findBy(array(
                            'dossier' => $dossier[0],
                            'typeEmail' => 'BANQUE_MANQUANTE'
                        ));

        return $this->render('BanqueBundle:Banque:liste-log-email.html.twig', [
            'emails' => $emails,
            'index' => $index 
        ]);
    }

    public function showLogContenuBanqueManquanteAction(Request $request)
    {
        $id = $request->request->get('id');
        $email = $this->getDoctrine()
                    ->getRepository('AppBundle:Emails')
                    ->find($id);
        return $this->render('BanqueBundle:Banque:liste-log-contenu-email.html.twig', [
            'email' => $email
        ]);
    }
}
