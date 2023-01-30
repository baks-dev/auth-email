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

namespace BaksDev\Auth\Email\DataFixtures\Account\UseCase;

//use BaksDev\Users\AuthEmail\Account\Entity\Event\EventInterface;
//use BaksDev\Users\AuthEmail\Account\Type\Email\AccountEmail;
//use BaksDev\Users\AuthEmail\Account\Type\Event\AccountEvent;

use BaksDev\Auth\Email\DataFixtures\Account\UseCase\Status\StatusDTO;
use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use Symfony\Component\Validator\Constraints as Assert;

final class AccountDTO implements AccountEventInterface
{
	
	/** UserEvent ID */
	#[Assert\IsNull]
	private readonly ?AccountEventUid $id;
	
	/** Email */
	#[Assert\NotBlank]
	#[Assert\Email]
	private readonly AccountEmail $email;
	
	/** Дайджест Пароля */
	#[Assert\Blank]
	private readonly string $password;
	
	/** Пароль */
	#[Assert\NotBlank]
	private readonly string $passwordPlain;
	
	/** Статус */
	#[Assert\Valid]
	private readonly StatusDTO $status;
	
	
	public function __construct(AccountEmail $email)
	{
		$this->email = $email;
		
		do
		{
			$bytes = openssl_random_pseudo_bytes(5, $innerStrong);
		}
		while(!$bytes || !$innerStrong);
		
		$this->passwordPlain = \bin2hex($bytes);
		$this->status = new StatusDTO();
		
		$this->id = null;
	}
	
	
	/** UserEvent ID */
	
	public function setId(AccountEventUid $id) : void {}
	
	
	public function getEvent() : ?AccountEventUid
	{
		return $this->id;
	}
	
	
	/** Email */
	public function getEmail() : AccountEmail
	{
		return $this->email;
	}
	
	
	/** Пароль */
	public function setPasswordHash(string $password) : void
	{
		$this->password = $password;
	}
	
	
	public function getPassword() : string
	{
		return $this->password;
	}
	
	
	public function getPasswordPlain() : string
	{
		return $this->passwordPlain;
	}
	
	
	/** Статус */
	public function getStatus() : StatusDTO
	{
		return $this->status;
	}
	
}

