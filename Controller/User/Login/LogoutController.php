<?php

namespace BaksDev\Auth\Email\Controller\User\Login;

use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Bundle\SecurityBundle\Security;

final class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'user.logout')]
    public function logout(Security $security) : Response
    {
		$security->logout(false);
		return $this->redirectToRoute('Pages:user.homepage');
    }
}