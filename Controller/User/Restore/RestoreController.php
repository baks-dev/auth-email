<?php

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Messenger\Restore\RestoreAccountMessage;
use BaksDev\Auth\Email\UseCase\User\Restore\RestoreDTO;
use BaksDev\Auth\Email\UseCase\User\Restore\RestoreForm;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Type\Locale\Locale;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class RestoreController extends AbstractController
{
    /** Восстановить пароль */
    #[Route('/restore', name: 'user.restore', methods: ['GET', 'POST'])]
    public function restore(
        Request $request,
        MessageBusInterface $bus,
        TranslatorInterface $translator,
        MessageDispatchInterface $messageDispatch
    ): Response
    {
        // Если пользователь авторизован - редирект
        if($this->getUsr())
        {
            return $this->redirectToRoute('core:user.homepage', status: 302);
        }

        $registrationDTO = new RestoreDTO();
        $form = $this->createForm(RestoreForm::class, $registrationDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('restore'))
        {

            $this->refreshTokenForm($form);

            // Делаем отправку на Email письмо для подтверждения
            $messageDispatch->dispatch(
                message: new RestoreAccountMessage($registrationDTO->getEmail(), new Locale($translator->getLocale())),
                transport: 'auth-email'
            );

            $this->addFlash('success', 'user.success.restore', 'user.restore');

            return $this->redirectToRoute('auth-email:user.login');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
