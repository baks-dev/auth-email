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
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationForm;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\FriendlyCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class RegistrationController extends AbstractController
{
    /** Регистрация пользователя по Email */
    #[Route('/reg', name: 'public.registration', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        RegistrationHandler $handler,
    ): Response
    {
        // Если пользователь авторизован - редирект
        if($this->getUsr())
        {
            return $this->redirectToRoute('core:user.homepage');
        }

        $registrationDTO = new RegistrationDTO();

        $form = $this
            ->createForm(
                type: RegistrationForm::class,
                data: $registrationDTO,
                options: ['action' => $this->generateUrl('auth-email:public.registration')]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('registration'))
        {
            $this->refreshTokenForm($form);

            $Account = $handler->handle($registrationDTO);

            if(!$Account instanceof Account)
            {
                // Ошибка при регистрации
                $this->addFlash(
                    'danger',
                    'user.danger.reg',
                    'public.reg',
                    $Account
                );

                return $this->redirectToReferer();
            }

            $this->addFlash('success', 'user.success.reg', 'public.reg');
            $this->addFlash('success', 'user.success.confirmation', 'public.reg');

            return $this->redirectToRoute('auth-email:public.login');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
