<?php

namespace UtilisateurBundle\Controller;

use AppBundle\Entity\Operateur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;
use UtilisateurBundle\Form\PasswordChangeType;
use UtilisateurBundle\Form\PasswordRequestType;

class SecurityController extends Controller
{
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@Utilisateur/Security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * Modification mot de passe
     * Lors de la première connexion d'un operateur
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function firstLoginAction(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $default_data = array();
            $form = $this->createForm(PasswordChangeType::class, $default_data);
            $error_pwd = false;
            $form->handleRequest($request);

            if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
                $password = $_POST['password_change']['password']['first'];
                if ($password == "scriptura" || $password == "SCRIPTURA") {
                    $error_pwd = true;
                    return $this->render('@Utilisateur/Security/first-login.html.twig', array(
                        'form' => $form->createView(),
                        'utilisateur' => $this->getUser(),
                        'error_pwd' => $error_pwd,
                    ));
                }
                $em = $this->getDoctrine()
                    ->getManager();
                /* @var Operateur $user */
                $user = $this->getUser();

                /* Mise à jour du mot de passe */

                $user->setPassword($password)
                    ->setLastLogin(new \DateTime());
                $em->flush();

                /* Déconnecter l'utilisateur et effacer les cookies */
                $this->get('security.token_storage')->setToken(null);
                $this->get('session')->invalidate();

                /* Rediriger vers la page succès */
                $response = $this->render('@Utilisateur/Security/first-password-changed.html.twig');

                /* Supprimer les cookies de connexion */
                $user_cookies = [
                    $this->getParameter('session.name'),
                    $this->getParameter('session.remember_me.name'),
                ];
                foreach ($user_cookies as $user_cookie) {
                    $response->headers->clearCookie($user_cookie);
                }

                return $response;
            }
            return $this->render('@Utilisateur/Security/first-login.html.twig', array(
                'form' => $form->createView(),
                'utilisateur' => $this->getUser(),
                'error_pwd' => $error_pwd,
            ));
        } else {
            return $this->redirectToRoute('login');
        }
    }
}
