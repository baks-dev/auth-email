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


use App\Module\User\AuthEmail\Account\Entity\Event\Event;
use App\Module\User\AuthEmail\Account\UseCase\AccountAggregate;
use App\Module\User\AuthEmail\Account\UseCase\Admin\NewEdit\AccountDTO;
use App\Module\User\AuthEmail\Account\UseCase\Admin\NewEdit\AccountForm;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\Security\RoleSecurity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[RoleSecurity(['ROLE_ADMIN', 'ROLE_ACCOUNT_EMAIL_EDIT'])]
final class EditController extends AbstractController
{
    
    #[Route('/admin/account/email/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    //#[ParamConverter('Event', Event::class)]
    public function edit(
      Request $request,
      //Event $Event,
     // AccountAggregate $accountAggregate,
    ) : Response
    {
	
		dd();
		
        $account = new AccountDTO();
        $Event->getDto($account);
        
        /* Форма добавления */
        $form = $this->createForm(AccountForm::class, $account);
        $form->handleRequest($request);
    
    
        if($form->isSubmitted() && $form->isValid())
        {
            $handle = $accountAggregate->handle($account);
    
            if($handle)
            {
                $this->addFlash('success', 'admin.account.update.success', 'account.email');
                return $this->redirectToRoute('AccountEmail:admin.account.index');
            }
        }
    
        return $this->render(['form' => $form->createView()]);
    }
}