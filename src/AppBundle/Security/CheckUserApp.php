<?php

namespace AppBundle\Security;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Doctrine\ORM\EntityManager;

class CheckUserApp
{
    protected $tokenStorage;
    protected $authChecker;
    protected $entityManager;
    protected $router;

    public function __construct(TokenStorage $tokenStorage, AuthorizationChecker $authChecker, EntityManager $entityManager, Router $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    /**
     * Tester si l'application interface est bien connectée
     * et c'est sur la même machine
     *
     * @return string
     */
    public function check()
    {

        if ($this->authChecker->isGranted('ROLE_ADMIN') ||
            $this->authChecker->isGranted('ROLE_BANQUE') ||
            $this->authChecker->isGranted('ROLE_CGP_REVISION')
        ) {
            return "ok";
        }

        $user_app_repo = $this->entityManager
            ->getRepository('AppBundle:UserApplication');
        $user_app = $user_app_repo
            ->getUserApp($this->tokenStorage->getToken()->getUser());

        if (!$user_app) {
            return "missing";
        } else {
            $ip = $user_app->getIp();

            if ($ip != $_SERVER['REMOTE_ADDR']) {
                return "mismatch";
            }
        }

        return "ok";
    }
}
