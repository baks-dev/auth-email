<?php

/*
 * Copyright (c) 2023.  Baks.dev <admin@baks.dev>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace BaksDev\Auth\Email\UseCase\User\Login\Tests;


use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\UseCase\User\Login\LoginDTO;
use BaksDev\Auth\Email\UseCase\User\Login\LoginForm;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\Test\TypeTestCase;

/** @group auth-email */
#[When(env: 'test')]
final class LoginFormTest extends TypeTestCase
{
	public function testSubmitValidData(): void
    {
		/* DATA */
		$AccountEmail = new AccountEmail('test@example.com');
		$password = 'gUdMo9ylP6';
		
		/* FORM */
		$model = new LoginDTO();
		$form = $this->factory->create(LoginForm::class, $model);
		
		$formData = [
			'email' => $AccountEmail->getValue(),
			'password' => $password,
			'login' => true
		];
		
		$form->submit($formData);
		
		self::assertTrue($form->isSynchronized());
		
		/* OBJECT */
		$expected = new LoginDTO();
		$expected->setEmail($AccountEmail);
		$expected->setPassword($password);
		
		self::assertEquals($expected, $model);
		
		/* VIEW */
		$view = $form->createView();
		$children = $view->children;
		
		foreach(array_keys($formData) as $key)
		{
			self::assertArrayHasKey($key, $children);
		}

        self::assertTrue(true);
	}
}