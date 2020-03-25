<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 06/09/2018
 * Time: 13:24
 */

namespace ImageBundle\Controller;


use AppBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ConsultationController extends Controller
{
    public function consultationAction(Request $request)
    {
        $post = $request->request;

        $imageId = $post->get('imageId');

        $height = $post->get('height');

        $height = (float)$height - 40;

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);

        $dossier = $image->getLot()->getDossier();
        $client = $dossier->getSite()->getClient();
        $exercice = $image->getExercice();
        $dateScan = $image->getLot()->getDateScan()->format("Ymd");


//        $chemin = 'http://192.168.0.9/Images comptabilisées/' .
//            $client->getNom() . '/' .
//            $dossier->getNom() . '/' .
//            $exercice . '/' .
//            $image->getNom() . '.pdf';

        $chemin = 'https://www.lesexperts.biz/IMAGES/'.$dateScan.'/'.$image->getNom().'.'.$image->getExtImage() ;

        $embed = '<object id="js_embed" 
            width="100%" 
            height="100%" 
            type="application/pdf" 
            trusted="yes" 
            application="yes" 
            title="IMAGE" 
            data="' . $chemin .
            '?#scrollbar=1&toolbar=0&navpanes=1">
           <p>Votre  navigateur ne peut pas affichier le fichier PDF. Vous pouvez le télécharger en cliquant <a target="_blank" href="' . $chemin . '" style="text-decoration: underline;">ICI</a></p>
        </object>';


        return $this->render('@Image/Consultation/consultation.html.twig', array(
            'img'=> $image,
            'embed'=> $embed,
            'height' => $height
        ));

    }

}