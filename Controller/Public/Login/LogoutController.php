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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsController]
final class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'public.logout')]
    public function logout(
        Request $request,
        Security $security,
        TokenStorageInterface $tokenStorage,
        GetUserByIdInterface $getUserById,
        AppCacheInterface $cache
    ): Response
    {
        $authority = $this->getUsr()?->getUserIdentifier();

        if($authority)
        {
            /** Удаляем авторизацию доверенности пользователя */

            $Session = $request->getSession();
            $Session->remove('Authority');

        }

        if($SwitchUser = $request->getSession()->get('_switch_user'))
        {
            $CurrentUser = $getUserById->get(new UserUid($SwitchUser));

            if($CurrentUser)
            {
                // Олицетворение запрошенного пользователя
                $impersonationToken = new  UsernamePasswordToken(
                    $CurrentUser,
                    "user",
                    $CurrentUser->getRoles()
                );

                $tokenStorage->setToken($impersonationToken);
                $request->getSession()->remove('_switch_user');

                return $this->redirectToReferer();
            }
        }


        $security->logout(false);

        return $this->redirectToRoute('core:public.homepage');
    }

}