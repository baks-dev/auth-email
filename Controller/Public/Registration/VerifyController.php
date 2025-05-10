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

namespace BaksDev\Auth\Email\Controller\Public\Registration;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Repository\UserAccountEvent\UserAccountEventInterface;
use BaksDev\Auth\Email\Repository\UserNew\UserNewInterface;
use BaksDev\Auth\Email\Services\EmailVerify\Exception\VerifyEmailExceptionInterface;
use BaksDev\Auth\Email\Services\EmailVerify\VerifyEmailInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\UseCase\User\Verify\VerifyDTO;
use BaksDev\Auth\Email\UseCase\User\Verify\VerifyHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[AsController]
final class VerifyController extends AbstractController
{
    /** Поверка подлинности Email */
    #[Route('/verify/email', name: 'public.verify.email')]
    public function verify(
        Request $request,
        UserNewInterface $userNew,
        VerifyEmailInterface $verifyEmail,
        UserAccountEventInterface $userAccountEvent,
        VerifyHandler $handler,
        #[ParamConverter(UserUid::class)] $UserUid = null,
    ): Response
    {

        // Проверяем что пользователь не авторизован
        if($this->getUsr())
        {
            return $this->redirectToRoute('core:user.homepage');
        }

        // Проверяем что передан идентификатор пользователя
        if($UserUid === null)
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        $NewUser = $userNew->getNewUserByUserUid($UserUid);

        // Проверяем что пользователь не заблокирован
        if($NewUser === null)
        {
            // Редирект на страницу авторизации
            return $this->redirectToRoute('auth-email:public.login');
        }

        // Проверяем ссылку верификации
        try
        {
            // проверяем ссылку подтверждения Email
            $verifyEmail->validateEmailConfirmation(
                $request->getUri(),
                $NewUser,
                new AccountEmail($NewUser->getOption())
            );
        }
        catch(VerifyEmailExceptionInterface $exception)
        {
            // Ошибка верификации ссылки подтверждения Email
            $this->addFlash('danger', $exception->getReason(), 'public.reg');
            return $this->redirectToRoute('auth-email:public.login');
        }

        // Активируем пользователя
        $Event = $userAccountEvent->getAccountEventByUser($NewUser);

        if($Event)
        {
            $VerifyDTO = new VerifyDTO($Event->getId());
            $Account = $handler->handle($VerifyDTO);

            if(!$Account instanceof Account)
            {
                $this->addFlash('danger', 'user.danger.verified', 'public.reg', $Account);

                return $this->redirectToRoute('auth-email:public.registration');
            }

            // TODO: Отправляем мыло с подтверждением регистрации

            // Редирект на страницу после успешного подтверждения адреса
            $this->addFlash('success', 'user.success.verified', 'public.reg');

            return $this->redirectToRoute('auth-email:public.login');
        }

        throw new RouteNotFoundException('Page Not Found');
    }
}
