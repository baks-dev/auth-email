<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Controller\User\Account;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Repository\UserAccountEvent\UserAccountEventInterface;
use BaksDev\Auth\Email\UseCase\User\Edit\AccountDTO;
use BaksDev\Auth\Email\UseCase\User\Edit\AccountForm;
use BaksDev\Auth\Email\UseCase\User\Edit\AccountHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

#[AsController]
#[RoleSecurity('ROLE_USER')]
final class EditController extends AbstractController
{
    /** Редактировать аккаунт пользователем */
    #[Route('/account/email/edit', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        UserAccountEventInterface $userAccountEvent,
        Request $request,
        AccountHandler $accountHandler,
        Security $security,
    ): Response
    {

        /* Показываем только собственные профили пользователя */
        $token = $security->getToken();

        /** @var User $usr */
        $usr = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUser() : $security->getUser();


        $Event = $userAccountEvent->getAccountEventByUser($usr->getId());
        $account = new AccountDTO();
        $Event ? $Event->getDto($account) : $account->setUsr($usr->getId());

        /* Форма добавления */
        $form = $this->createForm(AccountForm::class, $account);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('account'))
        {
            $this->refreshTokenForm($form);

            $Account = $accountHandler->handle($account);

            if($Account instanceof Account)
            {
                /* Закрываем сессию авторизации */
                $security->logout(false);

                $this->addFlash('success', 'user.success.update', 'user.account');
                return $this->redirectToRoute('auth-email:user.login');
            }

            $this->addFlash('danger', 'user.danger.update', 'user.account', $Account);
            return $this->redirectToRoute('auth-email:user.edit');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
