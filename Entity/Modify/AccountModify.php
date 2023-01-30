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

namespace BaksDev\Auth\Email\Entity\Modify;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Ip\IpAddress;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Модификатор */


#[ORM\Entity]
#[ORM\Table(name: 'users_account_modify')]
#[ORM\Index(columns: ['action'])]
class AccountModify extends EntityEvent
{
	public const TABLE = 'users_account_modify';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'modify', targetEntity: AccountEvent::class)]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	protected AccountEvent $event;
	
	/** Модификатор */
	#[ORM\Column(type: ModifyAction::TYPE, nullable: false)]
	protected ModifyAction $action;
	
	/** Дата */
	#[ORM\Column(name: 'mod_date', type: Types::DATETIME_IMMUTABLE, nullable: false)]
	private DateTimeImmutable $modDate;
	
	/** ID пользователя  */
	#[ORM\Column(name: 'user_id', type: UserUid::TYPE, nullable: true)]
	protected ?UserUid $user = null;
	
	/** Ip адресс */
	#[ORM\Column(name: 'user_ip', type: IpAddress::TYPE, nullable: false)]
	protected IpAddress $ipAddress;
	
	/** User-agent */
	#[ORM\Column(name: 'user_agent', type: Types::TEXT, nullable: false)]
	protected string $userAgent;
	
	
	public function __construct(AccountEvent $event, ModifyAction $modifyAction)
	{
		$this->event = $event;
		$this->modDate = new DateTimeImmutable();
		$this->ipAddress = new IpAddress('127.0.0.1');
		$this->userAgent = 'console';
		$this->action = $modifyAction;
	}
	
	
	public function __clone() : void
	{
		$this->modDate = new DateTimeImmutable();
		$this->action = new ModifyAction(ModifyActionEnum::UPDATE);
		$this->ipAddress = new IpAddress('127.0.0.1');
		$this->userAgent = 'console';
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof AccountModifyInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof AccountModifyInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function upModifyAgent(IpAddress $ipAddress, string $userAgent) : void
	{
		$this->ipAddress = $ipAddress;
		$this->userAgent = $userAgent;
		$this->modDate = new DateTimeImmutable();
	}
	
	
	public function setUser(UserUid|User|null $user) : void
	{
		$this->user = $user instanceof User ? $user->getId() : $user;
	}
	
	
	public function equals(ModifyActionEnum $action) : bool
	{
		return $this->action->equals($action);
	}
	
}
