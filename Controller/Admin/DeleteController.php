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
use BaksDev\Auth\Email\UseCase\Admin\Delete\AccountDeleteDTO;
use BaksDev\Auth\Email\UseCase\Admin\Delete\AccountDeleteForm;
use BaksDev\Auth\Email\UseCase\Admin\Delete\AccountDeleteHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_ACCOUNT_EMAIL_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/account/email/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        #[MapEntity] AccountEvent $Event,
        Request $request,
        AccountDeleteHandler $AccountDeleteHandler
    ): Response
    {

        $AccountDeleteDTO = new AccountDeleteDTO();
        $Event->getDto($AccountDeleteDTO);

        $form = $this
            ->createForm(
                type: AccountDeleteForm::class,
                data: $AccountDeleteDTO,
                options: ['action' => $this->generateUrl('auth-email:admin.delete', ['id' => $AccountDeleteDTO->getEvent()]),]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('account_delete'))
        {
            $this->refreshTokenForm($form);

            $Account = $AccountDeleteHandler->handle($AccountDeleteDTO);

            if($Account instanceof Account)
            {
                $this->addFlash(
                    type: 'success',
                    message: 'admin.success.delete',
                    domain: 'admin.account',
                );

                return $this->redirectToRoute('auth-email:admin.index');
            }

            $this->addFlash(
                type: 'danger',
                message: 'admin.danger.delete',
                domain: 'admin.account',
                arguments: $Account,
            );

            return $this->redirectToReferer();

        }

        return $this->render([
            'form' => $form->createView(),
            'name' => $Event->getEmail(),
        ]);
    }

}
