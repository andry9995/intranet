<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\UserApplication;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class ApplicationExeController extends Controller
{
    /**
     * Lancement programme local dans le poste de l'utilisateur (Ex: Excel)
     *
     * @param Request $request
     * @param $code_app
     * @return JsonResponse
     */
    public function exeLocalAction(Request $request, $code_app)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $etape_traitement = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => $code_app
            ));
        if ($etape_traitement) {
            $user_app = $this->getDoctrine()
                ->getRepository('AppBundle:UserApplication')
                ->getUserApp($this->getUser());
            if ($user_app && $user_app->getIp() == $_SERVER['REMOTE_ADDR']) {
                $new_user_app = $this->getDoctrine()
                    ->getRepository('AppBundle:UserApplication')
                    ->find($user_app->getId());
                $new_user_app->setEtapeTraitement($etape_traitement);
                $em->flush();

                $data = array(
                    'erreur' => false,
                );
                return new JsonResponse(json_encode($data));
            } else {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => "Interface non connectée ou machine de  connexion différente",
                );
                return new JsonResponse(json_encode($data));
            }

        } else {
            $data = array(
                'erreur' => true,
                'erreur_text' => 'Application introuvable',
            );
            return new JsonResponse(json_encode($data));
        }
    }


    /**
     * Lancement programme dans le serveur
     *
     * @param Request $request
     * @param $code_app
     * @return JsonResponse
     */
    public function exeServerAction(Request $request, $code_app)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $etape_traitement = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => $code_app
            ));
        if ($etape_traitement) {
            $user_app = $this->getDoctrine()
                ->getRepository('AppBundle:UserApplication')
                ->getUserApp($this->getUser());

            if ($user_app && ($user_app->getIp() == $_SERVER['REMOTE_ADDR'])
                || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $new_user_app = $this->getDoctrine()
                    ->getRepository('AppBundle:UserApplication')
                    ->find($user_app->getId());

                $parametre = $request->request->get('parametre');

                $new_user_app->setEtapeTraitement($etape_traitement);
                $new_user_app->setParametre($parametre);

                $em->flush();

                $data = array(
                    'erreur' => false,
                    'param' => $parametre
                );
                return new JsonResponse($data);
            } else {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => "Interface non connectée ou machine de  connexion différente",
                );
                return new JsonResponse($data);
            }

        } else {
            $data = array(
                'erreur' => true,
                'erreur_text' => 'Application introuvable',
            );
            return new JsonResponse($data);
        }
    }
}
