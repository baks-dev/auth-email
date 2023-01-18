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

namespace BaksDev\Auth\Email\Tests\UseCase\User\Change;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordForm;
use Symfony\Component\Form\Test\TypeTestCase;

final class ChangePasswordFormTest extends TypeTestCase
{
	public function testSubmitValidData()
	{
		/* DATA */
		$AccountEventUid = new AccountEventUid();
		$passwordPlain = 'hgbk609waG';
		
		/* FORM */
		$model = new ChangePasswordDTO($AccountEventUid);
		$form = $this->factory->create(ChangePasswordForm::class, $model);
		
		$formData = [
			'passwordPlain' => ['first' => $passwordPlain, 'second' => $passwordPlain],
			'change' => true,
		];
		
		$form->submit($formData);
		self::assertTrue($form->isSynchronized());
		
		/* OBJECT */
		$expected = new ChangePasswordDTO($AccountEventUid);
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