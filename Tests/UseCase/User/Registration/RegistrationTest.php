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

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class RegistrationTest extends KernelTestCase
{
	public function testDTO() : void
	{
		/* DATA */
		$AccountEmail = new AccountEmail('test@test.local');
		$passwordPlain = 'z0Vdtc17Wo';
		$passwordHash = 'hI6mgIpW0X';
		
		/* NEW */
		$newDTO = new RegistrationDTO();
		$newDTO->setId(new AccountEventUid());
		$newDTO->setEmail($AccountEmail);
		$newDTO->setPasswordPlain($passwordPlain);
		$newDTO->setPasswordHash($passwordHash);
		
		/* Проверка заполнения */
		self::assertNull($newDTO->getEvent());
		self::assertEquals($AccountEmail, $newDTO->getEmail());
		self::assertEquals($passwordPlain, $newDTO->getPasswordPlain());
		self::assertEquals($passwordHash, $newDTO->getPassword());
		
		$status = $newDTO->getStatus();
		self::assertEquals($status->getStatus()->getStatus(), AccountStatusEnum::NEW);
		
		$entity = new AccountEvent();
		$entity->setEntity($newDTO);
		
		/* EDIT */
		$editDTO = new RegistrationDTO();
		$entity->getDto($editDTO);
		
		self::assertNull($editDTO->getEvent());
		//self::assertNull($editDTO->getPasswordPlain());
		
		self::assertEquals($editDTO->getPassword(), $newDTO->getPassword());
		self::assertEquals($editDTO->getEmail(), $newDTO->getEmail());
		
		$status = $editDTO->getStatus();
		self::assertEquals($status->getStatus()->getStatus(), AccountStatusEnum::NEW);
		
		/* readonly */
		$this->expectError();
		$editDTO->setPasswordHash('546545645');
		$editDTO->setEmail($AccountEmail);
		
	}
	
}