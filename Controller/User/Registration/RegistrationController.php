<?php

namespace BaksDev\Auth\Email\Controller\User\Registration;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationForm;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\FriendlyCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class RegistrationController extends AbstractController
{
    /** Регистрация пользователя по Email */
    #[Route('/reg', name: 'user.registration', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        RegistrationHandler $handler,
    ): Response
    {
        // Если пользователь авторизован - редирект
        if ($this->getUsr()) {
            return $this->redirectToRoute('core:user.homepage');
        }

        $registrationDTO = new RegistrationDTO();
        $form = $this->createForm(RegistrationForm::class, $registrationDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('registration')) {

            $this->refreshTokenForm($form);

            // Проверяем капчу
            //			$solution = $request->get('frc-captcha-solution');
            //			if($captcha->verify($solution) === false)
            //			{
            //				/* Ошибка при регистрации */
            //				$this->addFlash('danger', 'user.danger.captcha', 'user.reg');
//
            //				return $this->redirectToReferer();
            //			}

            $Account = $handler->handle($registrationDTO);

            if (!$Account instanceof Account) {
                // Ошибка при регистрации
                $this->addFlash(
                    'danger',
                    'user.danger.reg',
                    'user.reg',
                    $Account
                );

                return $this->redirectToReferer();
            }

            $this->addFlash('success', 'user.success.reg', 'user.reg');
            $this->addFlash('success', 'user.success.confirmation', 'user.reg');

            return $this->redirectToRoute('auth-email:user.login');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
