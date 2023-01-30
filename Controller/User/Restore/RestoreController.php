<?php

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Messanger\Restore\RestoreCommand;
use BaksDev\Auth\Email\UseCase\User\Restore\RestoreDTO;
use BaksDev\Auth\Email\UseCase\User\Restore\RestoreForm;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class RestoreController extends AbstractController
{
	#[Route('/restore', name: 'user.restore', methods: ['GET', 'POST'])]
	public function restore(
		Request $request,
		MessageBusInterface $bus,
	) : Response
	{
		/* Если пользователь авторизован - редирект */
		if($this->getUser())
		{
			return $this->redirectToRoute('Pages:user.homepage', status: 302);
		}
		
		$registrationDTO = new RestoreDTO();
		$form = $this->createForm(RestoreForm::class, $registrationDTO);
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() && $form->has('restore'))
		{
			/* Делаем отправку на Email письмо для подтверждения */
			$restore = new RestoreCommand($registrationDTO->getEmail());
			$bus->dispatch($restore);
			
			$this->addFlash('success', 'user.success.restore', 'user.restore');
			
			return $this->redirectToRoute('AuthEmail:user.login');
		}
		
		return $this->render(['form' => $form->createView()]);
	}
	
}