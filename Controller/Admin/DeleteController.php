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

use App\Module\User\Entity\User;
use App\Module\User\Handler\Admin\User\Delete\DeleteForm;
use App\Module\User\Handler\Admin\User\Delete\Handler;


use App\System\Type\Locale\Locale;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\Security\RoleSecurity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[RoleSecurity(['ROLE_ADMIN', 'ROLE_ACCOUNT_EMAIL_DELETE'])]
final class DeleteController extends AbstractController
{
    
    #[Route('/admin/account/email/delete/{id}', name: 'admin.delete', methods: ['POST'])]
    public function delete(
      Request $request,
      //TranslatorInterface $translator,
      //Handler $handler,
      //User $user,
    ) : Response
    {
        
        dd();
        
        $form = $this->createForm(DeleteForm::class, $user, [
          'action' => $this->generateUrl('User:admin.user.account.delete', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            if($form->has('delete'))
            {
                $handler->handle($user);
                
                $this->addFlash(
                  'success',
                  $translator->trans('admin.user.account.delete.success', domain: 'user.user'));
            }
            return $this->redirectToRoute('User:admin.user.account.index');
        }
        
        /* Получаем название согласно локали */
        //$name =   $getTransName($user->getEvent(), new Locale($request->getLocale()));
        
        return $this->render(['form' => $form->createView()]);
    }

}