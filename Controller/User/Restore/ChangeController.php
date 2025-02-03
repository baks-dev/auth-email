<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordForm;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordHandler;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ChangeController extends AbstractController
{
    /** Страница для сброса пароля пользователя */
    #[Route('/change/{event}', name: 'user.change', methods: ['GET', 'POST'])]
    public function reset(
        Request $request,
        ChangePasswordHandler $handler,
        AppCacheInterface $cache,
        string $event
    ): Response
    {
        // Если пользователь авторизован - редирект
        if($this->getUsr())
        {
            $this->addFlash('danger', 'Произошла ошибка! Обратитесь в службу техподдержки, либо попробуйте еще раз.', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
        }

        // Получаем идентификатор события пользователя
        $AppCache = $cache->init($event);
        $AccountEvent = ($AppCache->getItem($event))->get();

        if(null === $AccountEvent)
        {
            $this->addFlash('danger', 'Произошла ошибка! Обратитесь в службу техподдержки, либо попробуйте еще раз.', 'user.reset');

            return $this->redirectToRoute('auth-email:user.restore');
        }

        $ChangePasswordDTO = new ChangePasswordDTO(new AccountEventUid($AccountEvent));

        $form = $this
            ->createForm(
                type: ChangePasswordForm::class,
                data: $ChangePasswordDTO,
                options: ['action' => $this->generateUrl('auth-email:user.change', ['event' => $event])]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('change'))
        {
            $this->refreshTokenForm($form);

            // Сбрасываем сессию после смены пароля
            $AppCache->delete($event);

            $Account = $handler->handle($ChangePasswordDTO);

            if($Account instanceof Account)
            {
                // Редирект на страницу после активации аккаунта
                $this->addFlash('success', 'user.success.change', 'user.reset');

                return $this->redirectToRoute('auth-email:user.login');
            }

            $this->addFlash('danger', 'user.danger.change', 'user.reset', $Account);

            return $this->redirectToRoute('auth-email:user.login');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
