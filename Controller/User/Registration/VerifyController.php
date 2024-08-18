<?php

namespace BaksDev\Auth\Email\Controller\User\Registration;

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
    #[Route('/verify/email', name: 'user.verify.email')]
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
            throw new RouteNotFoundException('Page Not Found');
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
            return $this->redirectToRoute('auth-email:user.login');
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
            $this->addFlash('danger', $exception->getReason(), 'user.reg');
            return $this->redirectToRoute('auth-email:user.login');
        }

        // Активируем пользователя
        $Event = $userAccountEvent->getAccountEventByUser($NewUser);

        if($Event)
        {
            $VerifyDTO = new VerifyDTO($Event->getId());
            $Account = $handler->handle($VerifyDTO);

            if(!$Account instanceof Account)
            {
                $this->addFlash('danger', 'user.danger.verified', 'user.reg', $Account);

                return $this->redirectToRoute('auth-email:user.registration');
            }

            // TODO: Отправляем мыло с подтверждением регистрации

            // Редирект на страницу после успешного подтверждения адреса
            $this->addFlash('success', 'user.success.verified', 'user.reg');

            return $this->redirectToRoute('auth-email:user.login');
        }

        throw new RouteNotFoundException('Page Not Found');
    }
}
