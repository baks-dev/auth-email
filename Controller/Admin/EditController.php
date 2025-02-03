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

namespace BaksDev\Auth\Email\Controller\Admin;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountForm;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_ACCOUNT_EMAIL_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/account/email/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity] AccountEvent $Event,
        Request $request,
        AccountHandler $accountHandler
    ): Response
    {

        $account = new AccountDTO();
        $Event->getDto($account);

        $form = $this
            ->createForm(
                type: AccountForm::class,
                data: $account,
                options: ['action' => $this->generateUrl('auth-email:admin.newedit.edit', ['id' => $Event->getId()])]

            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('account'))
        {
            $this->refreshTokenForm($form);

            $Account = $accountHandler->handle($account);

            if($Account instanceof Account)
            {
                $this->addFlash('success', 'admin.success.update', 'admin.account');
                return $this->redirectToRoute('auth-email:admin.index');
            }

            $this->addFlash('danger', 'admin.danger.update', 'admin.account', $Account);
            return $this->redirectToRoute('auth-email:admin.index');

        }

        return $this->render(['form' => $form->createView()]);
    }

}
