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

namespace BaksDev\Auth\Email\Tests\UseCase\User\Registration;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationForm;
use Symfony\Component\Form\Test\TypeTestCase;

final class RegistrationFormTest extends TypeTestCase
{
	public function testSubmitValidData()
	{
		/* DATA */
		$AccountEmail = new AccountEmail('test@test.local');
		$passwordPlain = '7Njm2mI5Ep';
		
		/* FORM */
		$model = new RegistrationDTO();
		$form = $this->factory->create(RegistrationForm::class, $model);
		
		$formData = [
			'email' => $AccountEmail->getValue(),
			'passwordPlain' => $passwordPlain,
			'agreeTerms' => true,
			'registration' => true,
		];
		
		$form->submit($formData);
		
		self::assertTrue($form->isSynchronized());
		
		/* OBJECT */
		$expected = new RegistrationDTO();
		$expected->setEmail($AccountEmail);
		$expected->setPasswordPlain($passwordPlain);
		
		self::assertEquals($expected, $model);
		
		/* VIEW */
		$view = $form->createView();
		$children = $view->children;
		
		foreach(array_keys($formData) as $key)
		{
			self::assertArrayHasKey($key, $children);
		}
	}
	
}