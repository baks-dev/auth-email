<?php

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Repository\UserAccountEvent\UserAccountEventInterface;
use BaksDev\Auth\Email\Security\UrlTokenGenerator;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Type\UidType\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

//use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[AsController]
final class ResetController extends AbstractController
{
    /** Проверяет и обрабатывает URL-адрес сброса, по которому пользователь перешел в своем электронном письме. */
    #[Route('/reset', name: 'user.reset', methods: ['GET'])]
    public function reset(
        UriSigner $uriSigner,
        UrlTokenGenerator $urlTokenGenerator,
        UserAccountEventInterface $accountEvent,
        Request $request,
        AppCacheInterface $cache,
        #[ParamConverter(AccountEventUid::class)] $AccountEventUid = null,
    ): Response
    {
        // Проверяем что пользователь не авторизован
        if($this->getUsr())
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        // Проверяем, что ссылка не была ранее изменена
        if(false === $uriSigner->checkRequest($request))
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        // Проверяем, что передан идентификатор события
        if(null === $AccountEventUid)
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        $Event = $accountEvent->getAccountEventNotBlockByEvent($AccountEventUid);

        // Проверяем что пользователь с событием не заблокирован
        if(null === $Event)
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        // Проверяем переданный токен с существующим
        $knownToken = $urlTokenGenerator->createToken($Event->getAccount(), $Event->getId());

        if(!hash_equals($knownToken, $request->get('_token')))
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        // Проверяем срок действия ссылки (5 минут)
        if((time() - (int) $request->get('expires')) > 300)
        {
            $this->addFlash('danger', 'user.danger.expired', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
        }

        /**
         * Генерируем временный идентификатор для сброса
         */
        $resetUid = new AccountEventUid();
        $AppCache = $cache->init($resetUid, 300);

        $cacheData = $AppCache->getItem((string) $resetUid);
        $cacheData->set($AccountEventUid);

        $AppCache->save($cacheData);


        return $this->redirectToRoute('auth-email:user.change', ['event' => $resetUid]);
    }
}
