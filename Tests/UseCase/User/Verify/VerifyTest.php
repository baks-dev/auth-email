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

namespace BaksDev\Auth\Email\Tests\UseCase\User\Verify;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Auth\Email\UseCase\User\Verify\VerifyDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class VerifyTest extends KernelTestCase
{
	public function testDTO() : void
	{
		$AccountEventUid = new AccountEventUid();
		$newDTO = new VerifyDTO($AccountEventUid);
		
		/* Проверка заполнения */
		self::assertEquals($AccountEventUid, $newDTO->getEvent());
		
		$status = $newDTO->getStatus();
		self::assertEquals($status->getStatus()->getStatus(), AccountStatusEnum::ACTIVE);
		
		$entity = new AccountEvent();
		$entity->setEntity($newDTO);
		
		/* EDIT */
		$AccountEventUidEdit = new AccountEventUid();
		$editDTO = new VerifyDTO($AccountEventUidEdit);
		$entity->getDto($editDTO);
		
		self::assertEquals($editDTO->getEvent(), $AccountEventUidEdit);
		
		$status = $editDTO->getStatus();
		self::assertEquals($status->getStatus()->getStatus(), AccountStatusEnum::ACTIVE);
		
	}
	
}