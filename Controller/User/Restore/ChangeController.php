<?php

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordForm;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordHandler;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


final class ChangeController extends AbstractController
{
	
	#[Route('/change', name: 'user.change', methods: ['GET', 'POST'])]
	public function reset(
		Request $request,
		ChangePasswordHandler $handler,
	) : Response
	{
		/* Если пользователь авторизован - редирект */
		if($this->getUser())
		{
			return $this->redirectToRoute('Pages:user.homepage');
		}
		
		/* Получаем идентификатор события пользователя  */
		$AccountEvent = $this->getSessionResetPasswordAccountEvent($request);
		
		if($AccountEvent === null)
		{
			throw new RouteNotFoundException('Отсутствует идентификатор AccountEvent для сброса пароля');
		}
		
		$ChangePasswordDTO = new ChangePasswordDTO(new AccountEventUid($AccountEvent));
		$form = $this->createForm(ChangePasswordForm::class, $ChangePasswordDTO);
		$form->handleRequest($request);
		
		
		if($form->isSubmitted() && $form->isValid())
		{
			/* Сбрасываем сессию после смены пароля */
			$this->cleanSessionAfterReset($request);
			
			if($form->has('change'))
			{
				$Account = $handler->handle($ChangePasswordDTO);
				
				if($Account instanceof Account)
				{
					/** TODO: Отправляем мыло с подтверждением регистрации */
					
					/* Редирект на страницу после активации аккаунта */
					$this->addFlash('success', 'user.success.change', 'user.reset');
					return $this->redirectToRoute('AuthEmail:user.login');
				}
			}
			
			$this->addFlash('danger', 'user.danger.change', 'user.reset', $Account);
			return $this->redirectToRoute('AuthEmail:user.login');
		}
		
		return $this->render(['form' => $form->createView()]);
	}
	
	private function getSessionResetPasswordAccountEvent(Request $request)
	{
		return $request->getSession()->get('ResetPasswordAccountEvent');
	}
	
	private function cleanSessionAfterReset(Request $request) : void
	{
		$session = $request->getSession();
		$session->remove('ResetPasswordAccountEvent');
	}
	
}