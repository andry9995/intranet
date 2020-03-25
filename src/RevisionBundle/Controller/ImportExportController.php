<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 18/02/2019
 * Time: 09:30
 */

namespace RevisionBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ImportExportController extends Controller
{
    public function indexAction()
    {
        return $this->render('RevisionBundle:ImportExport:index.html.twig');
    }

    public function resultsAction(Request $request)
    {
        return $this->render('@Tache/TacheAdmin/test.html.twig',[
            'test' => $request
        ]);
    }
}