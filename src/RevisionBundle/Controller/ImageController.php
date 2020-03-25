<?php

namespace RevisionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Entity\Image;
use AppBundle\Entity\Client;
use AppBundle\Functions\CustomPdoConnection;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class ImageController extends Controller
{
    public function indexAction()
    {

        $operateur = $this->getUser();

        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableClient')
            ->getClientsByResponsable($operateur->getId());

        $isPicdataUser = $this->isPicdataUser($operateur);

        /*Listes des responsables*/
        $responsables = $this->getDoctrine()
                ->getRepository('AppBundle:ResponsableScriptura')
                ->getSubDirections();

        return $this->render('RevisionBundle:Image:index.html.twig', array(
            'clients' => $clients,
            'responsables' => $responsables,
            'isPicdataUser' => $isPicdataUser
        ));
    }

    /**
     * Changer la couleur de fond d'une cellule dans l'exportation excel du tableau Details
     *
     * @param $phpExcelObject
     * @param integer $index index numérique des cellules
     * @param string $color code couleur en hexa
     * @param integer $typedate
     * @param  boolean $clientIsSelected
     */
    public function cellColor($phpExcelObject,$index,$color,$typedate, $clientIsSelected = false){

        $alphaNum = array();

        if (!$clientIsSelected) {
            if ($typedate == 1) {
                $alphaNum = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');
            }

            if ($typedate == 2) {
                $alphaNum = array('P','Q','R','S','T','U','V','W','X','Y','Z','AA');
            }
        }
        else{
            if ($typedate == 1) {
                $alphaNum = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');
            }

            if ($typedate == 2) {
                $alphaNum = array('Q','R','S','T','U','V','W','X','Y','Z','AA','AB');
            }
        }


        foreach ($alphaNum as $value) {
            
            $cells = $value . $index; 
            
            $phpExcelObject->setActiveSheetIndex(0)->getStyle($cells)->applyFromArray(
                array(
                    'fill'     => array(
                        'type' => 'solid',
                        'color'=> array('rgb'=>$color),
                     )
                )
            );
        }

    }

    /**
     * Total cellue (Excel)
     *
     * @param array $total
     * @param array $data
     * @param integer $typedate
     *
     * @return array
     */
    public function setCellTotal($total,$data,$typedate)
    {

        if ($typedate == 1) {
            $begin = 0;
            $end = 12;
        }
        else{
            $begin = 12;
            $end = 24;
        }

        for($i = $begin; $i < $end; $i++){

            $index = 'm ' . $i;
            
            if ($i === 0) {
                $index = 'm';
            }

            if (array_key_exists($index, $data)) {
                $total[$index] += $data[$index];
            }
        }

        return $total;

    }

    
    /**
     * Initialisation des variables pour les lignes totals (Excel)
     *
     * @param integer $typedate
     * @param boolean $clientIsSelected
     *
     * @return array
     */
    public function initializeTotal($typedate,$clientIsSelected = false)
    {
        $_TOTAL = array();

        if ($clientIsSelected) {
            if ($typedate == 1) {
                $total = array(
                        'dossier' => 'Total N' ,
                        'exercice' => '' ,
                        'client' => '',
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                     );
                    $totaln1 = array(
                        'dossier' => 'Total N - 1' ,
                        'exercice' => '' ,
                        'client' => '',
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                     );

                    $_TOTAL['total'] = $total;
                    $_TOTAL['totaln1'] = $totaln1;
            }
            else{
                $total = array(
                        'dossier' => 'Total N' ,
                        'exercice' => '' ,
                        'client' => '',
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                        'm 12' => 0,
                        'm 13' => 0,
                        'm 14' => 0,
                        'm 15' => 0,
                        'm 16' => 0,
                        'm 17' => 0,
                        'm 18' => 0,
                        'm 19' => 0,
                        'm 20' => 0,
                        'm 21' => 0,
                        'm 22' => 0,
                        'm 23' => 0,
                     );
                    $totaln1 = array(
                        'dossier' => 'Total N - 1' ,
                        'exercice' => '' ,
                        'client' => '',
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                        'm 12' => 0,
                        'm 13' => 0,
                        'm 14' => 0,
                        'm 15' => 0,
                        'm 16' => 0,
                        'm 17' => 0,
                        'm 18' => 0,
                        'm 19' => 0,
                        'm 20' => 0,
                        'm 21' => 0,
                        'm 22' => 0,
                        'm 23' => 0,
                     );

                    $_TOTAL['total'] = $total;
                    $_TOTAL['totaln1'] = $totaln1;
            }
        }
        else{
            if ($typedate == 1) {
                $total = array(
                        'dossier' => 'Total N' ,
                        'exercice' => '' ,
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                     );
                    $totaln1 = array(
                        'dossier' => 'Total N - 1' ,
                        'exercice' => '' ,
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                     );

                    $_TOTAL['total'] = $total;
                    $_TOTAL['totaln1'] = $totaln1;
            }
            else{
                $total = array(
                        'dossier' => 'Total N' ,
                        'exercice' => '' ,
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                        'm 12' => 0,
                        'm 13' => 0,
                        'm 14' => 0,
                        'm 15' => 0,
                        'm 16' => 0,
                        'm 17' => 0,
                        'm 18' => 0,
                        'm 19' => 0,
                        'm 20' => 0,
                        'm 21' => 0,
                        'm 22' => 0,
                        'm 23' => 0,
                     );
                    $totaln1 = array(
                        'dossier' => 'Total N - 1' ,
                        'exercice' => '' ,
                        'total' => 0,
                        'm' => 0,
                        'm 1' => 0,
                        'm 2' => 0,
                        'm 3' => 0,
                        'm 4' => 0,
                        'm 5' => 0,
                        'm 6' => 0,
                        'm 7' => 0,
                        'm 8' => 0,
                        'm 9' => 0,
                        'm 10' => 0,
                        'm 11' => 0,
                        'm 12' => 0,
                        'm 13' => 0,
                        'm 14' => 0,
                        'm 15' => 0,
                        'm 16' => 0,
                        'm 17' => 0,
                        'm 18' => 0,
                        'm 19' => 0,
                        'm 20' => 0,
                        'm 21' => 0,
                        'm 22' => 0,
                        'm 23' => 0,
                     );

                    $_TOTAL['total'] = $total;
                    $_TOTAL['totaln1'] = $totaln1;
            }
        }

        return $_TOTAL;
    }

    /**
     * Initialiation des cellules
     *
     * @param $sheet
     * @param integer $typedate
     * @param boolean $clientIsSelected
     */
    public function initializeCellValue($sheet,$typedate, $clientIsSelected = false)
    {

        if ($clientIsSelected) {
            if ($typedate == 1) {
                $sheet->setCellValue('A6', 'Dossier');
                $sheet->setCellValue('B6', 'Exercice');
                $sheet->setCellValue('C6', 'Client');
                $sheet->setCellValue('D6', 'Total images');
                $sheet->setCellValue('E6', 'm1');
                $sheet->setCellValue('F6', 'm2');
                $sheet->setCellValue('G6', 'm3');
                $sheet->setCellValue('H6', 'm4');
                $sheet->setCellValue('I6', 'm5');
                $sheet->setCellValue('J6', 'm6');
                $sheet->setCellValue('K6', 'm7');
                $sheet->setCellValue('L6', 'm8');
                $sheet->setCellValue('M6', 'm9');
                $sheet->setCellValue('N6', 'm10');
                $sheet->setCellValue('O6', 'm11');
                $sheet->setCellValue('P6', 'm12');
            }
            else{
                $sheet->setCellValue('Q6', 'm13');
                $sheet->setCellValue('R6', 'm14');
                $sheet->setCellValue('S6', 'm15');
                $sheet->setCellValue('T6', 'm16');
                $sheet->setCellValue('U6', 'm17');
                $sheet->setCellValue('V6', 'm18');
                $sheet->setCellValue('W6', 'm19');
                $sheet->setCellValue('X6', 'm20');
                $sheet->setCellValue('Y6', 'm21');
                $sheet->setCellValue('Z6', 'm22');
                $sheet->setCellValue('AA6', 'm23');
                $sheet->setCellValue('AB6', 'm24');
            }
        }
        else{

            if ($typedate == 1) {
                $sheet->setCellValue('A6', 'Dossier');
                $sheet->setCellValue('B6', 'Exercice');
                $sheet->setCellValue('C6', 'Total images');
                $sheet->setCellValue('D6', 'm1');
                $sheet->setCellValue('E6', 'm2');
                $sheet->setCellValue('F6', 'm3');
                $sheet->setCellValue('G6', 'm4');
                $sheet->setCellValue('H6', 'm5');
                $sheet->setCellValue('I6', 'm6');
                $sheet->setCellValue('J6', 'm7');
                $sheet->setCellValue('K6', 'm8');
                $sheet->setCellValue('L6', 'm9');
                $sheet->setCellValue('M6', 'm10');
                $sheet->setCellValue('N6', 'm11');
                $sheet->setCellValue('O6', 'm12');
            }
            else{
                $sheet->setCellValue('P6', 'm13');
                $sheet->setCellValue('Q6', 'm14');
                $sheet->setCellValue('R6', 'm15');
                $sheet->setCellValue('S6', 'm16');
                $sheet->setCellValue('T6', 'm17');
                $sheet->setCellValue('U6', 'm18');
                $sheet->setCellValue('V6', 'm19');
                $sheet->setCellValue('W6', 'm20');
                $sheet->setCellValue('X6', 'm21');
                $sheet->setCellValue('Y6', 'm22');
                $sheet->setCellValue('Z6', 'm23');
                $sheet->setCellValue('AA6', 'm24');
            }
            
        }

    }

    /**
     * Export excel - Tableau Details 
     *
     * @method POST
     * @param Request $request
     *
     * @return $response
     */
    public function exportDetailsAction(Request $request)
    {

        $exp_datas = json_decode(urldecode($request->request->get('exp_datas')),true);
        $dossier_selector = $request->request->get('exp_dossier');
        $typedate = $request->request->get('exp_typedate');
        $exercice = $request->request->get('exp_exercice');
        $client = $request->request->get('exp_client');

        if ($client != 0) {
            $clientValue = $this->getDoctrine()
                ->getRepository('AppBundle:Client')
                ->find($client)
                ->getNom();
        }
        else{
            $clientValue = 'Tous';
        }

        $datas = $exp_datas;

        $dossier = $dossier_selector;

        $ext = '.xls';

        $title = 'Details_' . $clientValue . '_' . $exercice ;

        $name = $title . $ext ;

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $backgroundTitle = '808080';

        $phpExcelObject->getProperties()->setCreator("Intranet")
            ->setLastModifiedBy("Intranet")
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription("Tableau de detail des images pour le client " . $clientValue . " pour l'exercice " . $exercice)
            ->setKeywords("intranet tableau details " . $clientValue)
            ->setCategory("exportation excel Intranet");
        
        $sheet = $phpExcelObject->setActiveSheetIndex(0);

        /*Titre*/
        $sheet->setCellValue('A1', '')
              ->setCellValue('A2', 'Client')
              ->setCellValue('B2', $clientValue )
              ->setCellValue('A3', 'Exercice')
              ->setCellValue('B3', $exercice);

        $key = 6;

        /*entetes*/
        if ($client == 0) {
            
           $this->initializeCellValue($sheet,1,true);

           // if ($typedate == 2) {
                $this->initializeCellValue($sheet,2,true);
            // }

            if (count($datas) > 3) {
                $index = 10;
            }
            else{
                $index = 7;
            }

            // if ($typedate == 1) {
                $_TOTAL = $this->initializeTotal(1,true);
                $total = $_TOTAL['total'];
                $totaln1 = $_TOTAL['totaln1'];
            // }
            // else{

                $_TOTAL = $this->initializeTotal(2,true);
                $total = $_TOTAL['total'];
                $totaln1 = $_TOTAL['totaln1'];
                
            // }

            foreach ($datas as $data) {

                if ($data['dossier'] == '') {
                    $this->cellColor($phpExcelObject,$index,'cecece',1,true);

                }
                else{

                    if ($data['exercice'] == 'N') {

                        $total['total'] += $data['total'];

                        $total = $this->setCellTotal($total,$data,1);

                        // if ($typedate == 2) {

                            $total = $this->setCellTotal($total,$data,2);

                        // }
                    }

                    if ($data['exercice'] == 'N - 1') {
                        $totaln1['total'] += $data['total'];

                        $totaln1 = $this->setCellTotal($totaln1,$data,1);

                        // if ($typedate == 2) {
                            $totaln1 = $this->setCellTotal($totaln1,$data,2);
                        // }
                    }

                }

                $sheet->setCellValue('A'.$index, $data['dossier']);
                $sheet->setCellValue('B'.$index, $data['exercice']);
                $sheet->setCellValue('C'. $index, $data['client']);
                $sheet->setCellValue('D'. $index, $data['total']);

                $this->setCellValue($sheet,$data, $index,1,true);

                // if ($typedate == 2) {

                    $this->setCellValue($sheet,$data, $index,2,true);

                    if ($data['dossier'] == '') {
                        $this->cellColor($phpExcelObject,$index , 'cecece',2,true);
                    }
                // }

                $index++;
            }

            if (count($datas) > 3) {

                $sheet->setCellValue('A8', $total['dossier']);
                $sheet->setCellValue('B8', $total['exercice']);
                $sheet->setCellValue('C8', $total['client']);
                $sheet->setCellValue('D8', $total['total']);

                $this->setCellValue($sheet,$total,8,1,true);

                $this->cellColor($phpExcelObject,8 , 'e9fbfc',1,true);

                $sheet->setCellValue('A9', $totaln1['dossier']);
                $sheet->setCellValue('B9', $totaln1['exercice']);
                $sheet->setCellValue('C9', $totaln1['client']);
                $sheet->setCellValue('D9', $totaln1['total']);

                $this->setCellValue($sheet,$totaln1,9,1,true);

                $this->cellColor($phpExcelObject,9 , 'e9fbfc',1,true);
                
                // if ($typedate == 2) {
                     $this->setCellValue($sheet,$total,8,2,true);

                     $this->cellColor($phpExcelObject,8 , 'e9fbfc',2,true);

                     $this->setCellValue($sheet,$totaln1,9,2,true);

                    $this->cellColor($phpExcelObject,9 , 'e9fbfc',2,true);
                // }

            }


            $phpExcelObject
            ->getActiveSheet()
            ->getColumnDimension('A')
            ->setAutoSize(true);
            $phpExcelObject
            ->getActiveSheet()
            ->getColumnDimension('C')
            ->setAutoSize(true);

            // END IF
        }
        else{

           $this->initializeCellValue($sheet,1);

            // if ($typedate == 2) {
                $this->initializeCellValue($sheet,2);
            // }

            if (count($datas) > 3) {
                $index = 10;
            }
            else{
                $index = 7;
            }

            // if ($typedate == 1) {
            //     $_TOTAL = $this->initializeTotal(1);
            //     $total = $_TOTAL['total'];
            //     $totaln1 = $_TOTAL['totaln1'];
            // }
            // else{

                $_TOTAL = $this->initializeTotal(2);
                $total = $_TOTAL['total'];
                $totaln1 = $_TOTAL['totaln1'];
                
            // }

            $i = 0;

            foreach ($datas as $data) {

                if ($data['exercice'] == '') {
                    $this->cellColor($phpExcelObject,$index,'cecece',1);
                    $data['dossier'] = '';

                }
                else{

                    if ($data['exercice'] == 'N') {

                        $total['total'] += $data['total'];

                        $total = $this->setCellTotal($total,$data,1);

                        // if ($typedate == 2) {

                            $total = $this->setCellTotal($total,$data,2);

                        // }
                    }

                    if ($data['exercice'] == 'N - 1') {
                        $totaln1['total'] += $data['total'];

                        $totaln1 = $this->setCellTotal($totaln1,$data,1);

                        // if ($typedate == 2) {
                            $totaln1 = $this->setCellTotal($totaln1,$data,2);
                        // }
                    }

                }

                $sheet->setCellValue('A'.$index, $data['dossier']);
                $sheet->setCellValue('B'.$index, $data['exercice']);
                $sheet->setCellValue('C'. $index, $data['total']);

                $this->setCellValue($sheet,$data, $index,1);

                // if ($typedate == 2) {

                    $this->setCellValue($sheet,$data, $index,2);

                    if ($data['dossier'] == '') {
                        $this->cellColor($phpExcelObject,$index , 'cecece',2);
                    }
                // }

                $index++;
            }

            // die();

            if (count($datas) > 3) {

                $sheet->setCellValue('A8', $total['dossier']);
                $sheet->setCellValue('B8', $total['exercice']);
                $sheet->setCellValue('C8', $total['total']);

                $this->setCellValue($sheet,$total,8,1);

                $this->cellColor($phpExcelObject,8 , 'e9fbfc',1);

                $sheet->setCellValue('A9', $totaln1['dossier']);
                $sheet->setCellValue('B9', $totaln1['exercice']);
                $sheet->setCellValue('C9', $totaln1['total']);

                $this->setCellValue($sheet,$totaln1,9,1);

                $this->cellColor($phpExcelObject,9 , 'e9fbfc',1);
                
                // if ($typedate == 2) {
                    $this->setCellValue($sheet,$total,8,2);

                    $this->cellColor($phpExcelObject,8 , 'e9fbfc',2);

                    $this->setCellValue($sheet,$totaln1,9,2);

                    $this->cellColor($phpExcelObject,9 , 'e9fbfc',2);

                // }

            }


            $phpExcelObject
            ->getActiveSheet()
            ->getColumnDimension('A')
            ->setAutoSize(true);
            $phpExcelObject
            ->getActiveSheet()
            ->getColumnDimension('C')
            ->setAutoSize(true);
        }

        


        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        $phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;



    }

    /**
     * Valeur cellule
     *
     * @param $sheet
     * @param array $data
     * @param integer $index
     * @param integer $typedate
     * @param boolean $clientIsSelected
     */
    public function setCellValue($sheet,$data, $index, $typedate, $clientIsSelected = false)
    {

        $alphaNum = array();

        if (!$clientIsSelected) {
            if ($typedate == 1) {
                $alphaNum['0'] = 'D';
                $alphaNum['1'] = 'E';
                $alphaNum['2'] = 'F';
                $alphaNum['3'] = 'G';
                $alphaNum['4'] = 'H';
                $alphaNum['5'] = 'I';
                $alphaNum['6'] = 'J';
                $alphaNum['7'] = 'K';
                $alphaNum['8'] = 'L';
                $alphaNum['9'] = 'M';
                $alphaNum['10'] = 'N';
                $alphaNum['11'] = 'O';
                $begin = 0;
                $end = 12;
            }
            else{
                $alphaNum['12'] = 'P';
                $alphaNum['13'] = 'Q';
                $alphaNum['14'] = 'R';
                $alphaNum['15'] = 'S';
                $alphaNum['16'] = 'T';
                $alphaNum['17'] = 'U';
                $alphaNum['18'] = 'V';
                $alphaNum['19'] = 'W';
                $alphaNum['20'] = 'X';
                $alphaNum['21'] = 'Y';
                $alphaNum['22'] = 'Z';
                $alphaNum['23'] = 'AA';
                $begin = 12;
                $end = 24;
            }
        }
        else{
            if ($typedate == 1) {
                $alphaNum['0'] = 'E';
                $alphaNum['1'] = 'F';
                $alphaNum['2'] = 'G';
                $alphaNum['3'] = 'H';
                $alphaNum['4'] = 'I';
                $alphaNum['5'] = 'J';
                $alphaNum['6'] = 'K';
                $alphaNum['7'] = 'L';
                $alphaNum['8'] = 'M';
                $alphaNum['9'] = 'N';
                $alphaNum['10'] = 'O';
                $alphaNum['11'] = 'P';
                $begin = 0;
                $end = 12;
            }
            else{
                $alphaNum['12'] = 'Q';
                $alphaNum['13'] = 'R';
                $alphaNum['14'] = 'S';
                $alphaNum['15'] = 'T';
                $alphaNum['16'] = 'U';
                $alphaNum['17'] = 'V';
                $alphaNum['18'] = 'W';
                $alphaNum['19'] = 'X';
                $alphaNum['20'] = 'Y';
                $alphaNum['21'] = 'Z';
                $alphaNum['22'] = 'AA';
                $alphaNum['23'] = 'AB';
                $begin = 12;
                $end = 24;
            }
        }



        for ($i= $begin; $i < $end; $i++) { 
            
            $key = 'm ' . $i;

            if ($i === 0) {
                $key = 'm';
            }
            
            if (array_key_exists($key, $data)) {

                $sheet->setCellValue($alphaNum[$i]. $index, $data[$key]);
            }
        }

    }

    /**
     * Veification utilisateur Picdata
     *
     * @param Operateur $operateur
     *
     * @return boolean $isPicdataUser
     */
    public function isPicdataUser($operateur)
    {
        $isPicdataUser = false;

        if ($operateur->getId() != 507) {
            $accessOperatorId = $operateur->getAccesOperateur()->getId();

            if ($accessOperatorId == 16) {
               $isPicdataUser = true;
            }
        }


        return $isPicdataUser;
    }

    /**
     * Liste des clients par responsable
     *
     * @param integer $responsable id responsable
     *
     * @return JsonResponse
     */
    public function listClientsByResponsableAction($responsable)
    {

        if ($responsable != 0) {
            
            $clients = $this->getDoctrine()
                    ->getRepository('AppBundle:Client')
                    ->getAllClientByResponsable($responsable);
            
            return new JsonResponse($clients);
        }

        else{

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

            return new JsonResponse($clients);
        }


    }

    /**
     * Stocks des images
     *
     * @param integer $groupe id responsable
     * @param integer $client id client
     * @param integer $dossier id dossier
     * @param integer $exercice
     *
     * @return JsonResponse 
     */
    public function stocksImagesAction($groupe, $client, $dossier, $exercice)
    {

        $response = array();

        /*Tous les clients*/
        if ($client == 0) {

            $clients = array();
            
            /*Selection responsable*/
            if ($groupe != 0) {
                $clients = $this->getDoctrine()
                    ->getRepository('AppBundle:Client')
                    ->getAllClientByResponsable($groupe);
            }
            /*Tous les responsables*/
            else{
                $responsables = $this->getDoctrine()
                    ->getRepository('AppBundle:ResponsableScriptura')
                    ->getSubDirections();

                foreach ($responsables as $responsable) {

                    $clientsResp = $this->getDoctrine()
                        ->getRepository('AppBundle:Client')
                        ->getAllClientByResponsable($responsable['operateur_id']);

                    $clients = array_merge($clients,$clientsResp);

                }
            }


            foreach ($clients as $client) {

                $picdata = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksPicdata($exercice,$client->id);

                $reception = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksReception($exercice,$client->id);

                $separation = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksSeparation($exercice,$client->id);

                $saisies = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksSaisies($exercice,$client->id);

                $ctrlSaisie = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksCtrlSaisie($exercice,$client->id);

                $imputation = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksImputation($exercice,$client->id);

                $ctrlImputation = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksCtrlImputation($exercice,$client->id);

                $banquesRb1 = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksBanquesRb1($exercice,$client->id);

                $banquesRb2 = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksBanquesRb2($exercice,$client->id);

                $banquesOb = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksBanquesOb($exercice,$client->id);

                $item = array();
                
                /*picdata*/
                if (empty($picdata)) {
                    $item['client-dossier'] = $client->nom;
                    $item['picdata'] = 0;
                    $item['couleur-picdata'] = "transparent";
                    
                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksPicdataPriorite($exercice,$client->id);
                    $item['client-dossier'] = $picdata[0]->client;
                    $item['picdata'] = $picdata[0]->nb;
                    $item['couleur-picdata'] = $priorite['couleur'];
                    $item['dossier-picdata'] = $priorite['dossier'];
                }

                /*reception*/
                if (empty($reception)) {
                    $item['reception'] = 0;
                    $item['couleur-reception'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksReceptionPriorite($exercice,$client->id);
                    $item['reception'] = $reception[0]->nb;
                    $item['couleur-reception'] = $priorite['couleur'];
                    $item['dossier-reception'] = $priorite['dossier'];
                }

                /*separation*/
                if (empty($separation)) {
                    $item['separation'] = 0;
                    $item['couleur-separation'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksSeparationPriorite($exercice,$client->id);
                    $item['separation'] = $separation[0]->nb;
                    $item['couleur-separation'] = $priorite['couleur'];
                    $item['dossier-separation'] = $priorite['dossier'];
                }

                /*saisies*/
                if (empty($saisies)) {
                    $item['saisies'] = 0;
                    $item['couleur-saisies'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksSaisiesPriorite($exercice,$client->id);
                    $item['saisies'] = $saisies[0]->nb;
                    $item['couleur-saisies'] = $priorite['couleur'];
                    $item['dossier-saisies'] = $priorite['dossier'];
                }

                /*ctrl-saisie*/
                if (empty($ctrlSaisie)) {
                    $item['ctrl-saisie'] = 0;
                    $item['couleur-ctrl-saisie'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksCtrlSaisiePriorite($exercice,$client->id);
                    $item['ctrl-saisie'] = $ctrlSaisie[0]->nb;
                    $item['couleur-ctrl-saisie'] = $priorite['couleur'];
                    $item['dossier-ctrl-saisie'] = $priorite['dossier'];

                }

                /*imputation*/
                if (empty($imputation)) {
                    $item['imputation'] = 0;
                    $item['couleur-imputation'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksImputationPriorite($exercice,$client->id);
                    $item['imputation'] = $imputation[0]->nb;
                    $item['couleur-imputation'] = $priorite['couleur'];
                    $item['dossier-imputation'] = $priorite['dossier'];

                }

                /*ctrl-imputation*/
                if (empty($ctrlImputation)) {
                    $item['ctrl-imputation'] = 0;
                    $item['couleur-ctrl-imputation'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksCtrlImputationPriorite($exercice,$client->id);
                    $item['ctrl-imputation'] = $ctrlImputation[0]->nb;
                    $item['couleur-ctrl-imputation'] = $priorite['couleur'];
                    $item['dossier-ctrl-imputation'] = $priorite['dossier'];

                }

                /*banques-rb1*/
                if (empty($banquesRb1)) {
                    $item['banques-rb1'] = 0;
                    $item['couleur-banques-rb1'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksBanquesRb1Priorite($exercice,$client->id);
                    $item['banques-rb1'] = $banquesRb1[0]->nb;
                    $item['couleur-banques-rb1'] = $priorite['couleur'];
                    $item['dossier-banques-rb1'] = $priorite['dossier'];

                }

                /*banques-rb2*/
                if (empty($banquesRb2)) {
                    $item['banques-rb2'] = 0;
                    $item['couleur-banques-rb2'] = "transparent";

                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksBanquesRb2Priorite($exercice,$client->id);
                    $item['banques-rb2'] = $banquesRb2[0]->nb;
                    $item['couleur-banques-rb2'] = $priorite['couleur'];
                    $item['dossier-banques-rb2'] = $priorite['dossier'];
                }

                /*banques-ob*/
                if (empty($banquesOb)) {
                    $item['banques-ob'] = 0;
                    $item['couleur-banques-ob'] = "transparent";
                }
                else{
                    $priorite = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksBanquesObPriorite($exercice,$client->id);
                    $item['banques-ob'] = $banquesOb[0]->nb;
                    $item['couleur-banques-ob'] = $priorite['couleur'];
                    $item['dossier-banques-ob'] = $priorite['dossier'];

                }

                array_push($response, $item);

            }

        }

        /*Seletion client*/
        else{
            $selectedClient = $this->getDoctrine()
                        ->getRepository('AppBundle:Client')
                        ->find($client);

            /*Tous les dossiers*/
            if ($dossier == 0) {

                $dossiers = $this->getDoctrine()
                        ->getRepository('AppBundle:Dossier')
                        ->getDossierByClient($selectedClient);

                foreach ($dossiers as $dossierItem) {

                    $picdata = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksPicdata($exercice,$client, $dossierItem->getId());

                        $reception = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksReception($exercice,$client, $dossierItem->getId());

                    $separation = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksSeparation($exercice,$client, $dossierItem->getId());

                    $saisies = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksSaisies($exercice,$client, $dossierItem->getId());

                    $ctrlSaisie = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksCtrlSaisie($exercice,$client, $dossierItem->getId());

                    $imputation = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksImputation($exercice,$client, $dossierItem->getId());

                    $ctrlImputation = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksCtrlImputation($exercice,$client, $dossierItem->getId());

                    $banquesRb1 = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksBanquesRb1($exercice,$client, $dossierItem->getId());

                    $banquesRb2 = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksBanquesRb2($exercice,$client, $dossierItem->getId());

                    $banquesOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksBanquesOb($exercice,$client, $dossierItem->getId());           

                    $item = array();

                    if (empty($picdata)) {

                        $item['client-dossier'] = $dossierItem->getNom();
                        $item['picdata'] = 0;
                        $item['couleur-picdata'] = "transparent";
                    }
                    else{
                        $item['client-dossier'] = $picdata[0]->dossier;
                        $item['picdata'] = $picdata[0]->nb;
                        $item['couleur-picdata'] = $picdata[0]->couleur;

                    }

                    if (empty($reception)) {
                        $item['reception'] = 0;
                        $item['couleur-reception'] = "transparent";

                    }
                    else{
                        $item['reception'] = $reception[0]->nb;
                        $item['couleur-reception'] = $reception[0]->couleur;

                    }

                    if (empty($separation)) {
                        $item['separation'] = 0;
                        $item['couleur-separation'] = "transparent";

                    }
                    else{
                        $item['separation'] = $separation[0]->nb;
                        $item['couleur-separation'] = $separation[0]->couleur;


                    }

                    if (empty($saisies)) {
                        $item['saisies'] = 0;
                        $item['couleur-saisies'] = "transparent";

                    }
                    else{
                        $item['saisies'] = $saisies[0]->nb;
                        $item['couleur-saisies'] = $saisies[0]->couleur;

                    }

                    if (empty($ctrlSaisie)) {
                        $item['ctrl-saisie'] = 0;
                        $item['couleur-ctrl-saisie'] = "transparent";

                    }
                    else{
                        $item['ctrl-saisie'] = $ctrlSaisie[0]->nb;
                        $item['couleur-ctrl-saisie'] = $ctrlSaisie[0]->couleur;

                    }

                    if (empty($imputation)) {
                        $item['imputation'] = 0;
                        $item['couleur-imputation'] = "transparent";

                    }
                    else{
                        $item['imputation'] = $imputation[0]->nb;
                        $item['couleur-imputation'] = $imputation[0]->couleur;

                    }

                    if (empty($ctrlImputation)) {
                        $item['ctrl-imputation'] = 0;
                        $item['couleur-ctrl-imputation'] = "transparent";

                    }
                    else{
                        $item['ctrl-imputation'] = $ctrlImputation[0]->nb;
                        $item['couleur-ctrl-imputation'] = $ctrlImputation[0]->couleur;

                    }

                    if (empty($banquesRb1)) {
                        $item['banques-rb1'] = 0;
                        $item['couleur-banques-rb1'] = "transparent";

                    }
                    else{
                        $item['banques-rb1'] = $banquesRb1[0]->nb;
                        $item['couleur-banques-rb1'] = $banquesRb1[0]->couleur;

                    }

                    if (empty($banquesRb2)) {
                        $item['banques-rb2'] = 0;
                        $item['couleur-banques-rb2'] = "transparent";

                    }
                    else{
                        $item['banques-rb2'] = $banquesRb2[0]->nb;
                        $item['couleur-banques-rb2'] = $banquesRb2[0]->couleur;

                    }

                    if (empty($banquesOb)) {
                        $item['banques-ob'] = 0;
                        $item['couleur-banques-ob'] = "transparent";

                    }
                    else{

                        $item['banques-ob'] = $banquesOb[0]->nb;
                        $item['couleur-banques-ob'] = $banquesOb[0]->couleur;

                    }
                    array_push($response, $item);

                }
            }

            /*Selection dossier*/
            else{

                $selectedDossier = $this->getDoctrine()
                        ->getRepository('AppBundle:Dossier')
                        ->find($dossier);


                    $picdata = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksPicdata($exercice,$client, $dossier);

                        $reception = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesStocksReception($exercice,$client, $dossier);

                    $separation = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksSeparation($exercice,$client, $dossier);

                    $saisies = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksSaisies($exercice,$client, $dossier);

                    $ctrlSaisie = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksCtrlSaisie($exercice,$client, $dossier);

                    $imputation = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksImputation($exercice,$client, $dossier);

                    $ctrlImputation = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksCtrlImputation($exercice,$client, $dossier);

                    $banquesRb1 = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksBanquesRb1($exercice,$client, $dossier);

                    $banquesRb2 = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksBanquesRb2($exercice,$client, $dossier);

                    $banquesOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesStocksBanquesOb($exercice,$client, $dossier);           

                    $item = array();

                    if (empty($picdata)) {

                        $item['client-dossier'] = $selectedDossier->getNom();
                        $item['picdata'] = 0;
                        $item['couleur-picdata'] = "transparent";
                    }
                    else{
                        $item['client-dossier'] = $picdata[0]->dossier;
                        $item['picdata'] = $picdata[0]->nb;
                        $item['couleur-picdata'] = $picdata[0]->couleur;
                    }

                    if (empty($reception)) {
                        $item['reception'] = 0;
                        $item['couleur-reception'] = "transparent";
                    }
                    else{
                        $item['reception'] = $reception[0]->nb;
                        $item['couleur-reception'] = $reception[0]->couleur;
                    }

                    if (empty($separation)) {
                        $item['separation'] = 0;
                        $item['couleur-separation'] = "transparent";
                    }
                    else{
                        $item['separation'] = $separation[0]->nb;
                        $item['couleur-separation'] = $separation[0]->couleur;
                    }

                    if (empty($saisies)) {
                        $item['saisies'] = 0;
                        $item['couleur-saisies'] = "transparent";
                    }
                    else{
                        $item['saisies'] = $saisies[0]->nb;
                        $item['couleur-saisies'] = $saisies[0]->couleur;
                    }

                    if (empty($ctrlSaisie)) {
                        $item['ctrl-saisie'] = 0;
                        $item['couleur-ctrl-saisie'] = "transparent";
                    }
                    else{
                        $item['ctrl-saisie'] = $ctrlSaisie[0]->nb;
                        $item['couleur-ctrl-saisie'] = $ctrlSaisie[0]->couleur;
                    }

                    if (empty($imputation)) {
                        $item['imputation'] = 0;
                        $item['couleur-imputation'] = "transparent";
                    }
                    else{
                        $item['imputation'] = $imputation[0]->nb;
                        $item['couleur-imputation'] = $imputation[0]->couleur;
                    }

                    if (empty($ctrlImputation)) {
                        $item['ctrl-imputation'] = 0;
                        $item['couleur-ctrl-imputation'] = "transparent";
                    }
                    else{
                        $item['ctrl-imputation'] = $ctrlImputation[0]->nb;
                        $item['couleur-ctrl-imputation'] = $ctrlImputation[0]->couleur;
                    }

                    if (empty($banquesRb1)) {
                        $item['banques-rb1'] = 0;
                        $item['couleur-banques-rb1'] = "transparent";
                    }
                    else{
                        $item['banques-rb1'] = $banquesRb1[0]->nb;
                        $item['couleur-banques-rb1'] = $banquesRb1[0]->couleur;
                    }

                    if (empty($banquesRb2)) {
                        $item['banques-rb2'] = 0;
                        $item['couleur-banques-rb2'] = "transparent";
                    }
                    else{
                        $item['banques-rb2'] = $banquesRb2[0]->nb;
                        $item['couleur-banques-rb2'] = $banquesRb2[0]->couleur;
                    }

                    if (empty($banquesOb)) {
                        $item['banques-ob'] = 0;
                        $item['couleur-banques-ob'] = "transparent";
                    }
                    else{
                        $item['banques-ob'] = $banquesOb[0]->nb;
                        $item['couleur-banques-ob'] = $banquesOb[0]->couleur;
                    }

                    array_push($response, $item);


            }
        }


        return new JsonResponse($response);

    }

    
    /**
     * Réccupération des données pour graphe répartition
     *
     * @param integer $client id client
     * @param string $exercice année de l'exercice
     *
     * @return JsonResponse
     */
    public function reputationImageAction($client,$exercice)
    {
        $param['client'] = $client;
        $param['exercice'] = $exercice;

        $result = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getImageReputation($param);

        return new JsonResponse($result);

    }


    /**
     * Réccupération des valuers pour le tableau des images et pour la graphe
     *
     * @param integer $client id client
     * @param integer $dossier id dossier
     * @param string $exercice année de l' exercice
     * @param integer $periode type de période
     * @param mixed $perioddeb date de début/0 si vide
     * @param mixed $periodfin date fin/0 si vide
     * @param integer $typedate type de la filtre date
     * @param integer $analyse type de la filtre analyse
     * @param integer $tab spécisication des onglets
     *
     * @return JsonResponse
     */
    public function listeImageAction($client/*, $site*/, $dossier, $exercice, $periode, $perioddeb, $periodfin, $typedate, $analyse, $tab, $filtre_nb = '', $operateur_nb = '', $value_nb = '')
    {
        $param = array();
        $param['client'] = $client;
        /*$param['site'] = $site;*/
        $param['dossier'] = $dossier;
        $param['exercice'] = $exercice;
        $param['periode'] = intval($periode);
        $param['analyse'] = intval($analyse);
        $param['typedate'] = intval($typedate);

        switch ($periode) {
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
                // Depuis 2 semaine
                $param['cas'] = 3;
                $periodeNow = new \DateTime();
                $now = clone $periodeNow;
                $twoWeeks = date_modify($periodeNow, "-7 days");
                $param['dateDeb'] = $twoWeeks->format('Y-m-d');
                $param['dateFin'] = $now->format('Y-m-d');
                break;
            case 4:
                // Depuis un mois
                $param['cas'] = 4;
                $periodeNow = new \DateTime();
                $now = clone $periodeNow;
                $oneMonth = date_modify($periodeNow, "-1 months");
                $param['dateDeb'] = $oneMonth->format('Y-m-d');
                $param['dateFin'] = $now->format('Y-m-d');

                break;
            case 5:
                // Tous les exercixe
                $param['cas'] = 5;

                break;
            case 6:
                // Fourchette date debut et date fin
                $param['cas'] = 6;
                $debPeriode = $perioddeb;
                $finPeriode = $periodfin;
                if( (isset($debPeriode) && !is_null($debPeriode)) && (isset($finPeriode) && !is_null($finPeriode)) ) {
                    $param['dateDeb'] = $debPeriode;
                    $param['dateFin'] = $finPeriode;
                }
                break;  
        }


        //Onglet Details
        if ($tab == 1) {

            $repository =  $this->getDoctrine()
                                ->getRepository('AppBundle:Image');

            $result     = $repository->getImagesRecues($param);

            $images = array();

            $images = $this->formatData($result,$param);

            $paramFilter = array();

            $paramFilter['filtre_nb'] = $filtre_nb;
            $paramFilter['operateur_nb'] = $operateur_nb;
            $paramFilter['value_nb'] = $value_nb;

            $afterFilter = array();

            $afterFilter = $this->ImagesFilter($images,$paramFilter);

            return new JsonResponse($afterFilter);


        }

        //Onglet Graphe
        else{

            $json = $this->getCourbeData($param);

            if ($analyse == 2)
                $json = $this->grapheCumul($json);

            $array            = array();
            $array['courbe']  = $json;
            $array['analyse'] = $analyse;

            return new JsonResponse($array);

        }
    }

    /**
     * Filtre selection Dossiiers
     *
     * @param array $images
     * @param array $paramFilter
     *
     * @return array
     */
    public function ImagesFilter($images,$paramFilter)
    {

        //$result = array();

        $response = array();
        
        $size = count($images);

        $nbDossier = $size / 3;

        $total = 0;

        $all = $images[1]['totalN'];

        if ($paramFilter['filtre_nb'] != 0 && $paramFilter['operateur_nb'] != 0 && $paramFilter['value_nb'] != '') {

            $count = 0;

            for ($i=1; $i <  $size; $i = $i + 3) { 


                switch ($paramFilter['filtre_nb']) {
                    // Nombre
                    case 1:
                        switch ($paramFilter['operateur_nb']) {
                            //Egal
                            case 1:
                                if ($images[$i]['total'] == $paramFilter['value_nb'] ) {
                                    array_push($response, $images[$i - 1]);
                                    array_push($response, $images[$i]);
                                    array_push($response, $images[$i + 1]);
                                    $count++;
                                    $total += $images[$i]['total'];
                                }
                                break;
                            //Supérieur
                            case 2:
                                if ($images[$i]['total'] > $paramFilter['value_nb'] ) {
                                    array_push($response, $images[$i - 1]);
                                    array_push($response, $images[$i]);
                                    array_push($response, $images[$i + 1]);
                                    $count++;
                                    $total += $images[$i]['total'];
                                }
                                break;
                            //Inférieur
                            case 3:
                                if ($images[$i]['total'] < $paramFilter['value_nb'] ) {
                                    array_push($response, $images[$i - 1]);
                                    array_push($response, $images[$i]);
                                    array_push($response, $images[$i + 1]);
                                    $count++;
                                    $total += $images[$i]['total'];
                                }
                                break;
                        }
                        break;
                    // Pourcentage
                    case 2:
                        $percentageN1 = round( intval($images[$i + 1]['total']) * intval($paramFilter['value_nb']) / 100 );

                        switch ($paramFilter['operateur_nb']) {
                            // Egal
                            case 1:
                                if ($images[$i]['total'] == $percentageN1 ) {
                                    array_push($response, $images[$i - 1]);
                                    array_push($response, $images[$i]);
                                    array_push($response, $images[$i + 1]);
                                    $count++;
                                    $total += $images[$i]['total'];
                                }
                                break;
                            // Supérieur
                            case 2:
                                if ($images[$i]['total'] > $percentageN1 ) {
                                    array_push($response, $images[$i - 1]);
                                    array_push($response, $images[$i]);
                                    array_push($response, $images[$i + 1]);
                                    $count++;
                                    $total += $images[$i]['total'];
                                }
                                break;
                            // Inférieur
                            case 3:
                                if ($images[$i]['total'] < $percentageN1 ) {
                                    array_push($response, $images[$i - 1]);
                                    array_push($response, $images[$i]);
                                    array_push($response, $images[$i + 1]);
                                    $count++;
                                    $total += $images[$i]['total'];
                                }
                                break;
                        }
                        break;
                }

            }

            $result = array(
                'data' => $response,
                'count' => $count,
                'percent' => number_format(($total * 100) / $all,2) . ' %'
            );

            return $result;
        }
        else{

            $result = array(
                'data' => $images,
                'count' => false,
                'percent' => false
            );

            return $result;
        }



    }


    /**
     * Calcule des nombres d'images en cumul pour les courbes
     *
     * @param array $data
     *
     * @return array
     */
    public function grapheCumul($json)
    {

        for ($i=1; $i < 27; $i++) { 
            $json[0]['data'][$i] += $json[0]['data'][$i - 1];
            $json[1]['data'][$i] += $json[1]['data'][$i - 1];
            $json[2]['data'][$i] += $json[2]['data'][$i - 1];
        }

        return $json;
    }

    /**
     * Réccupérations des données pour le graphe
     *
     * @param array $param
     *
     * @return array
     */
    public function getCourbeData($param)
    {
        $result = array();

        $result[0] = $this->prepareToGraph($param);

        $param['exercice'] = $param['exercice'] - 1;

        $result[1] = $this->prepareToGraph($param,1);

        $param['exercice'] = $param['exercice'] - 1;

        $result[2] = $this->prepareToGraph($param);

        return $result;

    }



    /**
     * Réccupération des données de la grahpe images reçues pour 1 exercice
     *
     * @param array $result tableau retourné par une demande de ImageRepoitory
     * @param array $param tableau des paramètres passés par listeImageAction
     * @param string $exercice
     * @param array $data
     * @return array 
     */
    public function prepareToGraph($param)
    {

        $betweens = array();
        
        $repository =  $this->getDoctrine()
                            ->getRepository('AppBundle:Image');

        $result     = $repository->getImagesRecues($param);

        $data = $this->initializeMonthKey();

        foreach ($result as $key => $value) {

            $debutFin = $this->get24Mois($param['exercice'],3);

            $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

            if ($k) {
                    $between = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
            } else{
                $between         = $this->getBetweenDate($debutFin['start'], $debutFin['end']);
                
                $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $between;

            }

            $monthKey        = 0;

            if ($param['typedate'] == 1) {
                $year_scan = explode('-', $value->date_scan)[0];

                if (in_array($value->date_scan, $between)) {
                        $monthKey = intval(explode('-', $value->date_scan)[1]);

                            if ($year_scan == $param['exercice']) {
                                $data[($monthKey + 6) + 1] += $value->nb;
                            }

                            if ($year_scan == $param['exercice'] - 1) {
                                if($monthKey >= 6){
                                    $data[($monthKey - 6) + 1] += $value->nb;
                                }
                                else{
                                    $data[0] += $value->nb;
                                }
                            }

                            if ($year_scan < $param['exercice'] - 1) {
                                $data[0] += $value->nb;
                            }

                            if ($year_scan == $param['exercice'] + 1) {
                                 if($monthKey <= 6){
                                    $data[($monthKey + 18) + 1] += $value->nb;
                                }
                                if($monthKey > 6){
                                    $data[26] += $value->nb;
                                }
                            }

                            if ($year_scan > $param['exercice'] + 1) {
                                $data[26] += $value->nb;
                            }

                }

            }
            else{
                $year_scan     = intval(explode('-', $value->date_piece)[0]);
                if (in_array($value->date_piece, $between)) {

                        $monthKey = intval(explode('-', $value->date_piece)[1]);

                            if ($year_scan == intval($param['exercice'])) {
                                $data[($monthKey + 6) + 1] += $value->nb;
                            }

                            if ($year_scan == intval($param['exercice']) - 1) {
                                if($monthKey >= 6){
                                    $data[($monthKey - 6) + 1] += $value->nb;
                                }
                                else{
                                    $data[0] += $value->nb;
                                }
                            }

                            if ($year_scan < intval($param['exercice']) - 1) {
                                $data[0] += $value->nb;
                            }

                            if ($year_scan == intval($param['exercice']) + 1) {
                                 if($monthKey <= 6){
                                    $data[($monthKey + 18) + 1] += $value->nb;
                                }
                                if($monthKey > 6){
                                    $data[26] += $value->nb;
                                }
                            }

                            if ($year_scan > intval($param['exercice']) + 1) {
                                $data[26] += $value->nb;
                            }
                    }
            }
            
        }

        $images = array(
            'data' => $data
        );

        return $images;

    }

    /**
     * Initialisation des nombres d'image par mois
     *
     * @param array $array
     *
     * @return array
     */
    public function initializeMonthKey()
    {

        $array = array();

        for ($i=0; $i <= 26 ; $i++) { 
            $array[$i] = 0;
        }

        return $array;
    }

    public function get24Mois($exercice, $nb = 1)
    {

        switch ($nb) {
            case 1:
                $start = $exercice . '-01-01';
                $end = new \DateTime($start);
                $end->add(new \DateInterval('P24M'));
                break;
            
            case 2:
                $last = intval($exercice) -1;
                $start = $last . '-01-01';
                $end = new \DateTime($start);
                $end->add(new \DateInterval('P24M'));
                break;
            case 3:
                $last = intval($exercice) -2;
                $start = $last . '-01-01';
                $end = new \DateTime($start);
                $end->add(new \DateInterval('P48M'));
                break;
            case 4:
                $last = intval($exercice);
                $start = $last . '-01-01';
                $end = new \DateTime($start);
                $end->add(new \DateInterval('P24M'));
                break;
        }

       

        return array(
            'start' => $start,
            'end' => $end->format('Y-m-d')
        );
    }

    public function getMoisInf($exercice)
    {

        $exercice = intval($exercice) - 2;
        $exercice2 = intval($exercice) + 2 - 1;

        $start = $exercice . '-01-01';

        $end = $exercice2 . '-12-01';

        return array(
            'start' =>$start,
            'end' => $end
        );

       
    }


    /**
     * Préparation des données pour le tableau des images
     *
     * @param array $result
     * @param $param
     *
     * @return array
     */
    public function formatData($result,$param)
    {
        $images = array();
        $data = array();
        $i = 1;
        $all = 0;
        $allPrev = 0;
        $count = count($result);
        $betweens = array();

        // reponse N vide
        if (empty($result)) {
            
            $dossier = $this->getDoctrine()
                        ->getRepository('AppBundle:Dossier')
                        ->find($param['dossier']);

            // Réccupération N - 1
            if ($param['dossier'] != 0) {

                $annee_cloture = '';

                if ($dossier->getDateCloture() != '') {
                    $annee_cloture = explode('-', $dossier->getDateCloture()->format('Y-m-d'))[0];
                }

                if ($param['typedate'] == 2 && $dossier->getDateCloture() && $dossier->getDebutActivite() && $annee_cloture == $param['exercice']) {

                    $debutFin = $this->get24Mois($param['exercice']);

                    $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                    if ($k) {
                        $between = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                    } else{
                        $between         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                        $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $between;
                    }

                    $label = $this->getMonthLabel($between);

                    $label['m+24'] = "< m";

                } else {

                    $debutFin = $this->get24Mois($param['exercice']);

                    $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                    if ($k) {
                        $between = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                    } else{
                        $between         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                        
                        $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $between;
                    }

                    $label = $this->getMonthLabel($between);

                    $label['m+24'] = "< m";

                }

                $images[0]             = $label;
                $images[0]['exercice'] = '';
                $images[0]['total']    = '';
                $images[1]             = $this->initializeM($images[0],count($label));
                $images[1]['dossier']  = $dossier->getNom();
                $images[1]['exercice'] = 'N';
                $images[1]['total']    = 0;
                $paramPrev             = array();
                $paramPrev             = $param;
                $paramPrev['exercice'] = strval(intval($param['exercice']) - 1);
                $paramPrev['dossier']  = $param['dossier'];
                $paramPrev['client']   = $param['client'];

                $prev = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getImagesRecues($paramPrev);

                $imagesPrev = array();

                if (empty($prev)){
                    $imagesPrev[0]['dossier']          = $dossier->getNom();
                    $imagesPrev[0]['total']            = 0;
                    $imagesPrev[0]['exercice']         = 'N - 1';
                    $imagesPrev[0]['totalN']           = 0;
                    $imagimagesPreves[0]['totalNPrev'] = 0;
                    $imagesPrev[0]                     = $this->initializeM($imagesPrev[0],count($label));
                } else{
                    $imagesPrev = $this->formatDataPrev($prev,$paramPrev,count($label));
                }

                if (empty($imagesPrev)) {
                    $imagesPrev[0]['dossier']          = $dossier->getNom();
                    $imagesPrev[0]['total']            = 0;
                    $imagesPrev[0]['exercice']         = 'N - 1';
                    $imagesPrev[0]['totalN']           = 0;
                    $imagimagesPreves[0]['totalNPrev'] = 0;
                    $imagesPrev[0]                     = $this->initializeM($imagesPrev[0],count($label));
                }

                $images[2] = $imagesPrev[0];
            }

        }

        else{

            foreach ($result as $key => $value) {

                if ($param['typedate'] == 2) {
                    $annee_cloture = explode('-', $value->date_cloture)[0];
                }

                $norm = true;

                if ($param['typedate'] == 2) {

                    $debutFin = $this->get24Mois($param['exercice']);
                    
                    $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                    if ($k) {
                        $moisCloture = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                    } else{
                        $moisCloture         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                        
                        $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $moisCloture;

                    }

                    $item['dossier'] = $value->dossier;
                    $item['nb'] = $value->nb;

                    $dfMoisInf = $this->getMoisInf($param['exercice']);

                    $moisInf = $this->getBetweenDate($dfMoisInf['start'], $dfMoisInf['end']);

                    $keyMonth = array_search($value->date_piece, $moisCloture);
                    if (!$keyMonth && strval($keyMonth) !== '0') {
                       $exist = array_search($value->date_piece, $moisInf);
                       if ($exist || strval($exist) === '0' ) {
                           $keyMonth = 24;
                       }

                    }

                    if ($keyMonth || strval($keyMonth) === '0') {

                        $m            = array();
                        $m[$keyMonth] = $value->nb;
                        $item['m']    = $m;
                        $not          = false;

                        if (!array_key_exists($value->dossier, $data)) {
                            $not = true;
                            $data[$value->dossier]['key'] = $i;
                            $i += 3;
                        }

                        if (isset($data[$value->dossier]['m'][$keyMonth])) {
                                $data[$value->dossier]['m'][$keyMonth] += $value->nb;
                        } else{
                            $data[$value->dossier]['m'][$keyMonth] = $value->nb;
                        }

                        $index  = $data[$value->dossier]['key'];
                        $index2 = intval($index) + 1 ;
                        $index3 = intval($index) - 1 ;
                        $total  = $this->getTotal($data[$value->dossier]['m'] );

                        if (array_key_exists($index, $images)) {
                            $lastTotal = $images[$index]['total'];
                            $all = $all - $lastTotal + $total;

                        } else{
                            $all += $total;
                        }

                        $client =  $this->getDoctrine()
                                        ->getRepository('AppBundle:Client')
                                        ->find($value->client_id)
                                        ->getNom();

                        $dossier=  $this->getDoctrine()
                                        ->getRepository('AppBundle:Dossier')
                                        ->find($value->dossier_id)
                                        ->getNom();

                        $images[$index3]               = $this->getMonthLabel($moisCloture);
                        $images[$index3]['client']     = $value->client;
                        $images[$index3]['dossier']    = $value->dossier;
                        $images[$index3]['total']      = '';
                        $images[$index3]['exercice']   = '';
                        $images[$index3]['totalN']     = 0;
                        $images[$index3]['totalNPrev'] = 0;
                        $images[$index]['client']      = $value->client;
                        $images[$index]['dossier']     = $value->dossier;
                        $images[$index]['total']       = $total;
                        $images[$index]['exercice']    = 'N';
                        $images[$index]                = $this->initializeM($images[$index],count($images[$index3]) - 6);
                        $images[$index]                = $this->pushM($data[$value->dossier]['m'],$images[$index],$param['analyse']);

                        $images[$index]['totalN'] = 0;
                        $images[$index]['totalNPrev'] = 0;

                        if ($not == true) {
                                $paramPrev = array();
                                $paramPrev = $param;
                                $paramPrev['exercice'] = strval(intval($param['exercice']) - 1);
                                $paramPrev['dossier'] = $value->dossier_id;
                                $paramPrev['client'] = $value->client_id;
                                $prev = $this->getDoctrine()
                                        ->getRepository('AppBundle:Image')
                                        ->getImagesRecues($paramPrev);

                                $imagesPrev = array();

                                if (empty($prev)){

                                    $imagesPrev[0]['client']           = $client;
                                    $imagesPrev[0]['dossier']          = $dossier;
                                    $imagesPrev[0]['total']            = 0;
                                    $imagesPrev[0]['exercice']         = 'N - 1';
                                    $imagesPrev[0]['totalN']           = 0;
                                    $imagimagesPreves[0]['totalNPrev'] = 0;
                                    $imagesPrev[0]                     = $this->initializeM($imagesPrev[0],count($images[$index3]) - 6);
                                }
                                else{
                                    $imagesPrev = $this->formatDataPrev($prev,$paramPrev,count($images[$index3]) - 6);
                                }

                                if ($index == 0){
                                    $images[1]               = $imagesPrev[0];
                                    $images[2]               = $this->getMonthLabel($moisCloture);
                                    $images[2]['client']     = $value->client;
                                    $images[2]['dossier']    = $value->dossier;
                                    $images[2]['total']      = '';
                                    $images[2]['exercice']   = '';
                                    $images[1]['totalN']     = 0;
                                    $images[1]['totalNPrev'] = 0;
                                    $images[2]['totalN']     = 0;
                                    $images[2]['totalNPrev'] = 0;

                                } else{
                                    if (empty($imagesPrev)) {
                                        $imagesPrev[0]['client']           = $client;
                                        $imagesPrev[0]['dossier']          = $dossier;
                                        $imagesPrev[0]['total']            = 0;
                                        $imagesPrev[0]['exercice']         = 'N - 1';
                                        $imagesPrev[0]['totalN']           = 0;
                                        $imagimagesPreves[0]['totalNPrev'] = 0;
                                        $imagesPrev[0]                     = $this->initializeM($imagesPrev[0],count($images[$index3]) - 6);
                                    }
                                  
                                    $images[$index2]               = $imagesPrev[0];
                                    $images[$index2]['totalN']     = 0;
                                    $images[$index2]['totalNPrev'] = 0;

                                }

                                $allPrev += $imagesPrev[0]['total'];
                            }
                    }
                } else {
                        
                        $item = array();

                        $debutFin = $this->get24Mois($param['exercice']);

                        $moisCloture     = $this->getBetweenDate($debutFin['start'], $debutFin['end']);

                        $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                        if ($k) {
                            $moisCloture = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                        } else{
                            $moisCloture         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                            
                            $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $moisCloture;

                        }

                        $item['dossier'] = $value->dossier;
                        $item['nb'] = $value->nb;

                        $dfMoisInf = $this->getMoisInf($param['exercice']);

                        $moisInf = $this->getBetweenDate($dfMoisInf['start'], $dfMoisInf['end']);

                        if ($param['typedate'] == 2) {
                            $keyMonth = array_search($value->date_piece, $moisCloture);
                            if (!$keyMonth && strval($keyMonth) !== '0') {
                               $exist = array_search($value->date_piece, $moisInf);
                               if ($exist || strval($exist) === '0' ) {
                                   $keyMonth = 24;
                               }

                            }
                        }
                        else{
                            $keyMonth = array_search($value->date_scan, $moisCloture);
                            if (!$keyMonth && strval($keyMonth) !== '0') {
                               $exist = array_search($value->date_scan, $moisInf);
                               if ($exist || strval($exist) === '0' ) {
                                   $keyMonth = 24;
                               }

                            }
                        }

                        if($keyMonth || strval($keyMonth) === '0' ){

                            $m = array();
                            $m[$keyMonth] = $value->nb;
                            $item['m'] = $m;
                            $not = false;

                            if (!array_key_exists($value->dossier, $data)) {
                                $not = true;
                                $data[$value->dossier]['key'] = $i;
                                $i += 3;
                            }
                            
                            if (isset($data[$value->dossier]['m'][$keyMonth])) {
                                $data[$value->dossier]['m'][$keyMonth] += $value->nb;
                            } else{
                                $data[$value->dossier]['m'][$keyMonth] = $value->nb;
                            }

                            $index = $data[$value->dossier]['key'];
                            $index2 = intval($index) + 1 ;
                            $index3 = intval($index) - 1 ;
                            $total = $this->getTotal($data[$value->dossier]['m'] );
                            
                            if (array_key_exists($index, $images)) {
                                $lastTotal = $images[$index]['total'];
                                $all = $all - $lastTotal + $total;
                            } else{
                                $all += $total;
                            }

                            $client = $this->getDoctrine()
                                    ->getRepository('AppBundle:Client')
                                    ->find($value->client_id)->getNom();

                               $dossier= $this->getDoctrine()
                                    ->getRepository('AppBundle:Dossier')
                                    ->find($value->dossier_id)->getNom();

                            $images[$index3] = $this->getMonthLabel($moisCloture);
                            $images[$index3]['client'] = $value->client;
                            $images[$index3]['dossier'] = $value->dossier;
                            $images[$index3]['total'] = '';
                            $images[$index3]['exercice'] = '';
                            $images[$index3]['totalN'] = 0;
                            $images[$index3]['totalNPrev'] = 0;

                            $images[$index]['client'] = $value->client;
                            $images[$index]['dossier'] = $value->dossier;
                            $images[$index]['total'] = $total;
                            $images[$index]['exercice'] = 'N';

                            $images[$index] = $this->initializeM($images[$index]);

                            $images[$index] = $this->pushM($data[$value->dossier]['m'],$images[$index],$param['analyse']);
                            $images[$index]['totalN'] = 0;
                            $images[$index]['totalNPrev'] = 0;

                            if ($not == true) {
                                $paramPrev             = array();
                                $paramPrev             = $param;
                                $paramPrev['exercice'] = strval(intval($param['exercice']) - 1);
                                $paramPrev['dossier']  = $value->dossier_id;
                                $paramPrev['client']   = $value->client_id;

                                $prev = $this->getDoctrine()
                                        ->getRepository('AppBundle:Image')
                                        ->getImagesRecues($paramPrev);

                                $imagesPrev = array();

                                if (empty($prev)){
                                    $imagesPrev[0]['client']           = $client;
                                    $imagesPrev[0]['dossier']          = $dossier;
                                    $imagesPrev[0]['total']            = 0;
                                    $imagesPrev[0]['exercice']         = 'N - 1';
                                    $imagesPrev[0]['totalN']           = 0;
                                    $imagimagesPreves[0]['totalNPrev'] = 0;
                                    $imagesPrev[0]                     = $this->initializeM($imagesPrev[0]);
                                } else{
                                    $imagesPrev = $this->formatDataPrev($prev,$paramPrev,24,$moisCloture);
                                }

                                if ($index == 0){
                                    $images[1]               = $imagesPrev[0];
                                    $images[2]               = $this->getMonthLabel($moisCloture);
                                    $images[2]['client']     = $value->client;
                                    $images[2]['dossier']    = $value->dossier;
                                    $images[2]['total']      = '';
                                    $images[2]['exercice']   = '';
                                    $images[1]['totalN']     = 0;
                                    $images[1]['totalNPrev'] = 0;
                                    $images[2]['totalN']     = 0;
                                    $images[2]['totalNPrev'] = 0;

                                }
                                else{

                                    if (empty($imagesPrev)) {
                                        $imagesPrev[0]['client']           = $client;
                                        $imagesPrev[0]['dossier']          = $dossier;
                                        $imagesPrev[0]['total']            = 0;
                                        $imagesPrev[0]['exercice']         = 'N - 1';
                                        $imagesPrev[0]['totalN']           = 0;
                                        $imagimagesPreves[0]['totalNPrev'] = 0;
                                        $imagesPrev[0]                     = $this->initializeM($imagesPrev[0]);
                                    }

                                    $images[$index2]               = $imagesPrev[0];
                                    $images[$index2]['totalN']     = 0;
                                    $images[$index2]['totalNPrev'] = 0;
                                }

                                $allPrev += $imagesPrev[0]['total'];

                            }
                        }

                }
            }
        }

        $images[1]['totalN'] = $all;
        $images[1]['totalNPrev'] = $allPrev;

        // Réccupération des N -1 pour N vide
        if ($param['dossier'] === '0') {

            $dossiers = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListDosierByExo($param['client'],$param['exercice']);

            if ($i != 0) {
                $i = $i - 1;
            }

            foreach ($dossiers as $dossier) {
                $exist = false; 
                foreach ($images as $image) {
                    if (array_key_exists('dossier', $image)) {
                        if ($dossier->nom == $image['dossier']) {
                            $exist = true;
                        }
                    }
                }
                // dossier N vide
                if (!$exist) {

                    if ($dossier->date_cloture != null) {
                        $annee_cloture = explode('-', $dossier->date_cloture)[0];
                    } else{
                        $annee_cloture = null;
                    }

                    if ($param['typedate'] == 2 && $dossier->date_cloture && $dossier->debut_activite && $annee_cloture == $param['exercice']) {
                        
                        $debutFin = $this->get24Mois($param['exercice']);

                        $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                        if ($k) {
                            $between = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                        } else{
                            $between         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                            
                            $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $between;

                        }

                        $label = $this->getMonthLabel($between);

                        $label['m+24'] = "< m";

                    } else {
                        $debutFin = $this->get24Mois($param['exercice']);

                        $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                        if ($k) {
                            $between = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                        } else{
                            $between         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                            
                            $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $between;

                        }

                        $label = $this->getMonthLabel($between);

                        $label['m+24'] = "< m";

                    }

                    $images[$i]                 = $label;
                    $images[$i]['client']       = $dossier->client;
                    $images[$i]['dossier']      = $dossier->nom;
                    $images[$i]['exercice']     = '';
                    $images[$i]['total']        = '';
                    $images[$i + 1]             = $this->initializeM($images[$i],count($label));
                    $images[$i + 1]['dossier']  = $dossier->nom;
                    $images[$i + 1]['exercice'] = 'N';
                    $images[$i + 1]['total']    = 0;
                    $paramPrev                  = array();
                    $paramPrev                  = $param;
                    $paramPrev['exercice']      = strval(intval($param['exercice']) - 1);
                    $paramPrev['dossier']       = $dossier->id;
                    $paramPrev['client']        = $dossier->client_id;

                    $prev = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getImagesRecues($paramPrev);

                    $imagesPrev = array();

                    if (empty($prev)){
                        $imagesPrev[0]['client']   = $dossier->client;
                        $imagesPrev[0]['dossier']  = $dossier->nom;
                        $imagesPrev[0]['total']    = 0;
                        $imagesPrev[0]['exercice'] = 'N - 1';
                        $imagesPrev[0]             = $this->initializeM($imagesPrev[0],count($label));
                    } else{
                        $imagesPrev = $this->formatDataPrev($prev,$paramPrev,count($label));
                    }

                    if (empty($imagesPrev)) {
                        $imagesPrev[0]['client']   = $dossier->client;
                        $imagesPrev[0]['dossier']  = $dossier->nom;
                        $imagesPrev[0]['total']    = 0;
                        $imagesPrev[0]['exercice'] = 'N - 1';
                        $imagesPrev[0]             = $this->initializeM($imagesPrev[0],count($label));
                    }

                    $images[$i + 2] = $imagesPrev[0];

                    $i = $i + 3;
                }

            }
        }

        return $images;

    }

    /**
     * Réccupération des valeurs de la date à afficher (m+1, ..., m+12) dans le tableau des images
     *
     * @param array $moisCloture tableau des mois par rapport a la cloture de l'exercice
     * 
     * @return array
     */
    public function getMonthLabel($moisCloture)
    {
        $result = array();
        foreach ($moisCloture as $key => $value) {


            if (intval($key) == 0) {

               $label = "m";

               $name = explode('-', $value)[1] . '-' . substr(explode('-', $value)[0], -2);

               $result[$label] = $name; 
            }

            else{
                $index = $key;

                $label = "m+" . $index;

                $name = explode('-', $value)[1] . '-' . substr(explode('-', $value)[0], -2);

                $result[$label] = $name;
            }

        }

        //$result['m+12'] = 0;

        return $result;
    }


    public function getMoisCloture($months)
    {
        
        $result = array();

        foreach ($months as $month) {
            
            $explode = explode('-', $month);

            $value = strval(intval($explode[0]) - 1) . '-' . $explode[1];

            array_push($result, $value);

        }

        return $result;

    }

    /**
     * Données de N-1 pour le tableau des images
     *
     * @param array $result
     * @param array $param
     *
     * @return array
     */
    public function formatDataPrev($result,$param, $count = 25,$months = array())
    {
        $betweens = array();

        $images = array();
        $data = array();
        $i = 0;
        $o = 0;
        foreach ($result as $key => $value) {

            $o += $value->nb;

            $annee_cloture = '';

            if ($param['typedate'] == 2) {
                $annee_cloture = explode('-', $value->date_cloture)[0];
            }

            if ($param['typedate'] == 2 && $value->date_cloture && $value->debut_activite && $param['exercice'] == $annee_cloture) {

                $debutFin = $this->get24Mois($param['exercice']);

                if ($months) {
                    $moisCloture = $this->getMoisCloture($months);
                } else{
                    $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                    if ($k) {
                        $moisCloture = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                    } else{
                        $moisCloture         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                        $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $moisCloture;
                    }
                }

                $item['dossier'] = $value->dossier;
                $item['nb']      = $value->nb;
                $keyMonth        = array_search($value->date_piece, $moisCloture);

                $dfMoisInf = $this->getMoisInf($param['exercice']);

                $moisInf = $this->getBetweenDate($dfMoisInf['start'], $dfMoisInf['end']);

                if (!$keyMonth && strval($keyMonth) !== '0') {
                   $exist = array_search($value->date_piece, $moisInf);
                   if ($exist || strval($exist) === '0' ) {
                       $keyMonth = 24;
                   }

                }

                if ($keyMonth || strval($keyMonth) === '0') {
                    if (!array_key_exists($value->dossier, $data)) {
                        $data[$value->dossier]['key'] = $i;
                        $i += 1;
                    }
                    
                    if (isset($data[$value->dossier]['m'][$keyMonth])) {
                        $data[$value->dossier]['m'][$keyMonth] += $value->nb;
                    } else{
                        $data[$value->dossier]['m'][$keyMonth] = $value->nb;
                    }

                    $index                      = $data[$value->dossier]['key'];
                    $total                      = $this->getTotal($data[$value->dossier]['m'] );
                    $images[$index]['client']   = $value->client;
                    $images[$index]['dossier']  = $value->dossier;
                    $images[$index]['total']    = $total;
                    $images[$index]['exercice'] = 'N - 1';
                    $images[$index]             = $this->initializeM($images[$index], $count);
                    $images[$index]             = $this->pushM($data[$value->dossier]['m'],$images[$index],$param['analyse']);
                }
            }

            else{


                $item = array();
                
                $debutFin = $this->get24Mois($param['exercice']);

                $k = array_key_exists($debutFin['start'] . '-' . $debutFin['end'], $betweens);

                if ($k) {
                    $moisCloture = $betweens[$debutFin['start'] . '-' . $debutFin['end']];
                } else{
                    $moisCloture         = $this->getBetweenDate($debutFin['start'],$debutFin['end']);
                    $betweens[$debutFin['start'] . '-' . $debutFin['end']] = $moisCloture;
                }

                $item['dossier'] = $value->dossier;
                $item['nb'] = $value->nb;

                $dfMoisInf = $this->getMoisInf($param['exercice']);

                $moisInf = $this->getBetweenDate($dfMoisInf['start'], $dfMoisInf['end']);

                if ($param['typedate'] == 2) {
                    $keyMonth = array_search($value->date_piece, $moisCloture);
                    if (!$keyMonth && strval($keyMonth) !== '0') {
                        $exist = array_search($value->date_piece, $moisInf);
                        if ($exist || strval($exist) === '0' ) {
                               $keyMonth = 24;
                        }
                    }
                }
                else{
                    $keyMonth = array_search($value->date_scan, $moisCloture);
                    if (!$keyMonth && strval($keyMonth) !== '0') {
                        $exist = array_search($value->date_scan, $moisInf);
                        if ($exist || strval($exist) === '0' ) {
                               $keyMonth = 24;
                       }
                    }
                }


                if ($keyMonth || strval($keyMonth) === '0') {
                    if (!array_key_exists($value->dossier, $data)) {
                        $data[$value->dossier]['key'] = $i;
                        $i += 1;
                    }
                    
                    if (isset($data[$value->dossier]['m'][$keyMonth])) {
                        $data[$value->dossier]['m'][$keyMonth] += $value->nb;
                    } else{
                        $data[$value->dossier]['m'][$keyMonth] = $value->nb;
                    }

                    $index                      = $data[$value->dossier]['key'];
                    $total                      = $this->getTotal($data[$value->dossier]['m'] );
                    $images[$index]['client']   = $value->client;
                    $images[$index]['dossier']  = $value->dossier;
                    $images[$index]['total']    = $total;
                    $images[$index]['exercice'] = 'N - 1';
                    $images[$index]             = $this->initializeM($images[$index]);
                    $images[$index]             = $this->pushM($data[$value->dossier]['m'],$images[$index],$param['analyse']);
                }
            }
        }

        return $images;
    }

    /**
     * Calcul du total des images
     *
     * @param array $data
     *
     * @return integer
     */
    public function getTotal($data)
    {
        $total = 0;

        foreach ($data as $nb) {
                $total += $nb;
        }

        return $total;
    }

    /**
     * Calcul de la nombre d'image par cumul
     *
     * @param array $result
     *
     * @return array
     */
    public function pushByCumul($result)
    {
       $i = 1;
       foreach ($result as $key => $value) {


           if (($key != "m") && ($key != "client") && ($key != "dossier") && ($key != "total") && ($key != 'exercice') && ($key != 'totalN') && ($key != 'totalNPrev')) {



                $label = "m+" .$i;


                $result[$key] = $result[$label] + $value;


                $i += 1;
           }

       }

       return $result; 
    }


    /**
     * Calcul du nombre d'image par mois ou par cumul
     *
     * @param array $m
     * @param array $result
     * @param integer $analyse
     *
     * @return array
     */
    public function pushM($m, $result, $analyse)
    {
        $last = count($result) - 1;

        foreach ($m as $key => $value) {
            $index = "m+" . $key;
            if ($key == 0) {
                $index = "m";
            }
            $result[$index] = $value;
       }

       if ($analyse == 2) {

            $i = 0;
            $result['m'] += $result['m+24'];
            foreach ($result as $key => $value) {
                if ($key != 'client' && $key != 'dossier' && $key != 'total' && $key != 'exercice' && $key != "totalN" && $key != 'totalNPrev') {
                    $key1   = $i + 1;
                    if ($key1 < 24) {
                        $index1 = "m+" .$key1;
                        if (array_key_exists($index1, $result)) {
                            $result[$index1] += $result[$key];
                        }
                        $i += 1;
                    }
                }
            }
       
       }



       return $result;
    }

    /**
     * Initialiation du valeur de la tableau $m
     *
     * @param array $m
     *
     * @return array
     */
    public function initializeM($m, $nb = 25)
    {
        for($j=0; $j<$nb; $j++){

            $label = "m+".$j;

            if ($j == 0) {
                $label = "m";
            }

            $m[$label] = 0;
        }

        return $m;
        
    }

    /**
     * Réccupération de la date de début et date de la fin de période par rapport à l'exercice et au cloture
     *
     * @param string $exercice
     * @param integer $cloture
     *
     * @return array
     */
    public function beginEnd($exercice, $cloture)
    {
        if ($cloture < 9) {
            $debutMois = ($exercice - 1) . '-0' . ($cloture + 1) . '-01';
        } else if ($cloture >= 9 and $cloture < 12) {
            $debutMois = ($exercice - 1) . '-' . ($cloture + 1) . '-01';
        } else {
            $debutMois = ($exercice) . '-01-01';
        }
        if ($cloture < 10) {
            $finMois = ($exercice) . '-0' . ($cloture) . '-01';
        } else {
            $finMois = ($exercice) . '-' . ($cloture) . '-01';
        }

        $result = array();
        $result['start'] = $debutMois;
        $result['end'] = $finMois;

        return $result;

    }

    /**
     * Réccupération des dates entre date de début et date de la fin de période
     *
     * @param array $param
     *
     * @return array
     */
    protected function getBetweenDate($start, $end)
    {
        $time1  = strtotime($start);
        $time2  = strtotime($end);
        $my     = date('mY', $time2);
        $months = array(date('Y-m', $time1));
        while ($time1 < $time2) {
            $time1 = strtotime(date('Y-m', $time1) . ' +1 month');
            if (date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('Y-m', $time1);
        }
        $months[] = date('Y-m', $time2);
        return $months;
    }
}
