<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Controller\User\Login;

use BaksDev\Auth\Email\UseCase\User\Login\LoginDTO;
use BaksDev\Auth\Email\UseCase\User\Login\LoginForm;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[AsController]
final class LoginController extends AbstractController
{
	#[Route('/login', name: 'user.login')]
	public function login(
		Request $request,
		AuthenticationUtils $authenticationUtils,
	) : Response
	{

		if($this->getUsr())
		{
			/* Редирект на главную страницу */
			return $this->redirectToRoute('core:user.homepage');
		}

		$LoginDTO = new LoginDTO();
		$form = $this->createForm(
			LoginForm::class,
			$LoginDTO,
			['action' => $this->generateUrl('auth-email:user.login'),]
		);

		return $this->render([
			'form' => $form->createView(),
		]);
	}

}
