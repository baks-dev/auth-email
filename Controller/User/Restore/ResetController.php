<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Repository\UserAccountEvent\UserAccountEventInterface;
use BaksDev\Auth\Email\Security\UrlTokenGenerator;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Type\UidType\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

//use Symfony\Component\HttpKernel\UriSigner;

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
            return $this->redirectToRoute('core:user.homepage');
        }

        // Проверяем, что ссылка не была ранее изменена
        if(false === $uriSigner->checkRequest($request))
        {
            $this->addFlash('danger', 'Произошла ошибка! Обратитесь в службу техподдержки, либо попробуйте еще раз.', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
        }

        // Проверяем, что передан идентификатор события
        if(null === $AccountEventUid)
        {
            $this->addFlash('danger', 'Произошла ошибка! Обратитесь в службу техподдержки, либо попробуйте еще раз.', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
        }

        $Event = $accountEvent->getAccountEventNotBlockByEvent($AccountEventUid);

        // Проверяем что пользователь с событием не заблокирован
        if(null === $Event)
        {
            $this->addFlash('danger', 'Произошла ошибка! Обратитесь в службу техподдержки, либо попробуйте еще раз.', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
        }

        // Проверяем переданный токен с существующим
        $knownToken = $urlTokenGenerator->createToken($Event->getAccount(), $Event->getId());

        if(!hash_equals($knownToken, $request->get('_token')))
        {
            $this->addFlash('danger', 'Произошла ошибка! Обратитесь в службу техподдержки, либо попробуйте еще раз.', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
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
