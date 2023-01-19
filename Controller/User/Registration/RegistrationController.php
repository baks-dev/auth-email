<?php

namespace BaksDev\Auth\Email\Controller\User\Registration;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Messanger\Confirmation\ConfirmationCommand;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationForm;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\FriendlyCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/reg', name: 'user.registration', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        RegistrationHandler $handler,
        //AccountAggregate $aggregate,
        MessageBusInterface $bus,
        //FriendlyCaptcha $captcha
        //GetEmailAccountSettingsInterface $accountSettings
    ) : Response
    {
        /* Если пользователь авторизован - редирект */
        if($this->getUser())
        {
            return $this->redirectToRoute('Pages:user.homepage');
        }

        $registrationDTO = new RegistrationDTO();
        $form = $this->createForm(RegistrationForm::class, $registrationDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            /* Проверяем капчу */
            $solution = $request->get('frc-captcha-solution');
            if ($captcha->verify($solution) === false)
            {
                /* Ошибка при регистрации */
                $this->addFlash('danger', 'user.danger.captcha', 'user.reg');
                return $this->redirectToReferer();
            }


            if($form->has('registration'))
            {
                $Account = $handler->handle($registrationDTO);

                if(!$Account instanceof Account)
                {
                    /* Ошибка при регистрации */
                    $this->addFlash('danger', 'user.danger.reg', 'user.reg');
                    return $this->redirectToReferer();
                }

                $this->addFlash('success', 'user.success.reg', 'user.reg');

                /* Делаем отправку на Email письмо для подтверждения */
                $confirm = new ConfirmationCommand($Account->getEvent());
                $bus->dispatch($confirm);

                $this->addFlash('success', 'user.success.confirmation', 'user.reg');
                return $this->redirectToRoute('AuthEmail:user.login');
            }



        }

        return $this->render(['form' => $form->createView(),]);
    }
}