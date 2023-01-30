<?php

namespace BaksDev\Auth\Email\Controller\User\Registration;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Messanger\Confirmation\ConfirmationCommand;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationForm;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\FriendlyCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class AgreeTermsController extends AbstractController
{
	#[Route('/agree/terms', name: 'user.agree.terms', methods: ['GET', 'POST'])]
	public function registration(
		Request $request,
		RegistrationHandler $handler,
		//AccountAggregate $aggregate,
		MessageBusInterface $bus,
		FriendlyCaptcha $captcha,
		//GetEmailAccountSettingsInterface $accountSettings
	) : Response
	{
		
		return $this->render();
	}
	
}