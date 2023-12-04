<?php
/*
 *  Copyright Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
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
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_ACCOUNT_EMAIL_EDIT')]
final class EditController extends AbstractController
{
	
	#[Route('/admin/account/email/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
	public function edit(
        #[MapEntity] AccountEvent $Event,
		Request $request,
        AccountHandler $accountHandler
	) : Response
	{

		$account = new AccountDTO();
		$Event->getDto($account);
		
		/* Форма добавления */
		$form = $this->createForm(AccountForm::class, $account);
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() && $form->has('account'))
		{
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