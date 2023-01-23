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

use App\Module\User\AuthEmail\Account\UseCase\AccountAggregate;
use App\Module\User\AuthEmail\Account\UseCase\Admin\NewEdit\AccountDTO;
use App\Module\User\AuthEmail\Account\UseCase\Admin\NewEdit\AccountForm;

use BaksDev\Core\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ACCOUNT_EMAIL_NEW')")]
final class NewController extends AbstractController
{
    #[Route('/admin/account/email/new', name: 'admin.newedit.new', methods: ['GET', 'POST'])]
    public function new(
      Request $request,
      //AccountAggregate $accountAggregate,
      //TranslatorInterface $translator,
      //Handler $handler
    ) : Response
    {
		
		dd();
        
        $account = new AccountDTO();
        
        /* Форма добавления */
        $form = $this->createForm(AccountForm::class, $account);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $handle = $accountAggregate->handle($account);
    
            if($handle)
            {
                $this->addFlash('success', 'admin.account.new.success', 'account.email');
                return $this->redirectToRoute('AccountEmail:admin.account.index');
            }
        }
        
        return $this->render(['form' => $form->createView()]);
        
    }

}