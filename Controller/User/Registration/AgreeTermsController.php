<?php

namespace BaksDev\Auth\Email\Controller\User\Registration;

use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationHandler;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

//use BaksDev\Core\Services\FriendlyCaptcha;

#[AsController]
final class AgreeTermsController extends AbstractController
{
    /** Пользовательское соглашение  */
    #[Route('/agree/terms', name: 'user.agree.terms', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        RegistrationHandler $handler,
        MessageBusInterface $bus,
        //FriendlyCaptcha $captcha,
    ): Response
    {
        return $this->render();
    }
}
