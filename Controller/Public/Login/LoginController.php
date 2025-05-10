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

namespace BaksDev\Auth\Email\Controller\Public\Login;

use BaksDev\Auth\Email\UseCase\User\Login\LoginDTO;
use BaksDev\Auth\Email\UseCase\User\Login\LoginForm;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[AsController]
final class LoginController extends AbstractController
{
    #[Route('/login', name: 'public.login')]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils,
    ): Response
    {

        if($this->getUsr())
        {
            /* Редирект на главную страницу */
            return $this->redirectToRoute('core:user.homepage');
        }

        $LoginDTO = new LoginDTO();

        $form = $this
            ->createForm(
                type: LoginForm::class,
                data: $LoginDTO,
                options: ['action' => $this->generateUrl('auth-email:public.login'),]
            );

        return $this->render([
            'form' => $form->createView(),
        ]);
    }

}
