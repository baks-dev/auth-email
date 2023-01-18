<?php

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Repository\AccountEventNotBlockByEmail\AccountEventNotBlockByEmailInterface;
use BaksDev\Auth\Email\Repository\AccountEventNotBlockByEventUid\AccountEventNotBlockByEventUidInterface;
use BaksDev\Auth\Email\Security\UrlTokenGenerator;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

final class ResetController extends AbstractController
{
	/** Проверяет и обрабатывает URL-адрес сброса, который пользователь щелкнул в своем электронном письме. */
	#[Route('/reset', name: 'user.reset', methods: ['GET'])]
	public function reset(
		Request $request,
		?AccountEventUid $AccountEventUid,
		UriSigner $uriSigner,
		UrlTokenGenerator $urlTokenGenerator,
		AccountEventNotBlockByEventUidInterface $accountEventNotBlockByEventUid
	) : Response
	{
		/* Если пользователь авторизован - редирект */
		if($this->getUser())
		{
			return $this->redirectToRoute('Pages:user.homepage', status: 302);
		}
		
		/* Если пользователь авторизован - Page Not Found */
		if($this->getUser())
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		/* Проверяем, что ссылка не была ранее изменена */
		if($uriSigner->checkRequest($request) === false)
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		if($AccountEventUid === null)
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		
		$Event = $accountEventNotBlockByEventUid->get($AccountEventUid);
		
		if($Event === null)
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		
		/* Проверяем переданный токен с существующим */
		$knownToken = $urlTokenGenerator->createToken($Event->getAccount(), $Event->getId());
		
		if(!hash_equals($knownToken, $request->get('token')))
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		/* Проверяем срок действия ссылки (5 минут) */
		if((time() - (int) $request->get('exp')) > 300)
		{
			$this->addFlash('danger', 'user.danger.expired', 'user.reset');
			return $this->redirectToRoute('AuthEmail:user.restore');
		}
		
		/* Сохраняем идентификатор в сеансе и удаляем его из URL-адреса */
		$request->getSession()->set('ResetPasswordAccountEvent', $AccountEventUid->getValue());
		return $this->redirectToRoute('AuthEmail:user.change');
		
		
	}
}