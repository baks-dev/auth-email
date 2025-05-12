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

namespace BaksDev\Auth\Email\Controller\Public\Restore;

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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class RestoreController extends AbstractController
{
    /** Восстановить пароль */
    #[Route('/restore', name: 'public.restore', methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('core:public.homepage', status: 302);
        }

        $registrationDTO = new RestoreDTO();

        $form = $this
            ->createForm(
                type: RestoreForm::class,
                data: $registrationDTO,
                options: ['action' => $this->generateUrl('auth-email:public.restore')]

            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('restore'))
        {

            $this->refreshTokenForm($form);

            // Делаем отправку на Email письмо для подтверждения
            $messageDispatch->dispatch(
                message: new RestoreAccountMessage($registrationDTO->getEmail(), new Locale($translator->getLocale())),
                transport: 'auth-email'
            );

            $this->addFlash('success', 'user.success.restore', 'public.restore');

            return $this->redirectToRoute('auth-email:public.login');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
