<?php

namespace BaksDev\Auth\Email\Controller\User\Registration;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Repository\UserNewByAccountEvent\UserNewByAccountEventInterface;
use BaksDev\Auth\Email\Security\UrlTokenGenerator;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Verify\VerifyDTO;
use BaksDev\Auth\Email\UseCase\User\Verify\VerifyHandler;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

final class VerifyController extends AbstractController
{
	
	#[Route('/verify/email', name: 'user.verify.email')]
	public function verify(
		Request $request,
		?AccountEventUid $AccountEventUid,
		UriSigner $uriSigner,
		UserNewByAccountEventInterface $userNewByAccountEvent,
		UrlTokenGenerator $urlTokenGenerator,
		VerifyHandler $handler
	) : Response
	{
		
		/* Если пользователь авторизован - Page Not Found */
		if($this->getUser())
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		if($AccountEventUid === null)
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		/* Проверяем, что ссылка не была ранее изменена */
		if($uriSigner->checkRequest($request) === false)
		{
			throw new RouteNotFoundException('Page Not Found');
		}

		$UserUid = $userNewByAccountEvent->get($AccountEventUid);
		
		/* Если не найден UserUid */
		if($UserUid === null)
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		/* Проверяем переданный токен с существующим */
		$knownToken = $urlTokenGenerator->createToken($UserUid, $AccountEventUid);
		
		if(!hash_equals($knownToken, $request->get('token')))
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		/* Проверяем срок действия ссылки (1 час) */
		if((time() - (int) $request->get('exp')) > 3600)
		{
			$this->addFlash('danger', 'user.danger.expired', 'user.confirmation');
			return $this->redirectToRoute('AuthEmail:user.restore');
		}
		
		/* Активируем пользователя */
		$VerifyDTO = new VerifyDTO($AccountEventUid);
		$Account = $handler->handle($VerifyDTO);
		
		if($Account instanceof Account)
		{
			
			/** TODO: Отправляем мыло с подтверждением регистрации*/
			
			/* Редирект на страницу после активации аккаунта */
			$this->addFlash('user.page', 'user.success.verified', 'user.confirmation');
			return $this->redirectToRoute('AuthEmail:user.login');
		}
		
		$this->addFlash('danger', 'user.danger.verified', 'user.confirmation', $Account);
		return $this->redirectToRoute('AuthEmail:user.registration');
	}
}