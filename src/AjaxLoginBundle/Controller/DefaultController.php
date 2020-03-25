<?php

namespace AjaxLoginBundle\Controller;

use AppBundle\Entity\Operateur;
use http\Env\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{


    public function autoAction(Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->find($request->query->get('l'));
        $this->autologin($user);

//        return $this->render('TacheBundle:TacheAdmin:test.html.twig', ['test' => $user]);

        return $this->redirectToRoute($request->query->get('ln'));
    }

    /**
     * @param Operateur|null $user
     * @return bool
     */
    private function autologin(Operateur $user = null)
    {
        if ($user)
        {
            $p = $user->getPassword();
            $token = new UsernamePasswordToken($user, $p, 'main', $user->getRoles());
//            $context = $this->get('security.context');
            $context = $this->get('security.token_storage');
            $context->setToken($token);
            return true;
        }
        return false;
    }
}
