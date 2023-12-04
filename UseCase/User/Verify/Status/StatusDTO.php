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

namespace BaksDev\Auth\Email\UseCase\User\Verify\Status;

use BaksDev\Auth\Email\Entity\Status\AccountStatusInterface;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use Symfony\Component\Validator\Constraints as Assert;

final class StatusDTO implements AccountStatusInterface
{
	
	#[Assert\NotBlank]
	private readonly EmailStatus $status;
	
	
	public function __construct()
	{
		$this->status = new EmailStatus(EmailStatusActive::class);
	}
	
	
	/**
	 * @return EmailStatus
	 */
	public function getStatus() : EmailStatus
	{
		return $this->status;
	}
	
}

