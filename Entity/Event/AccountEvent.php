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

namespace BaksDev\Auth\Email\Entity\Event;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Modify\AccountModify;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/* События изменений Аккаунта */


#[ORM\Entity]
#[ORM\Table(name: 'users_account_event')]
#[ORM\Index(columns: ['account'])]
#[ORM\Index(columns: ['email'])]
class AccountEvent extends EntityEvent implements PasswordAuthenticatedUserInterface
{
    public const TABLE = 'users_account_event';

    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: AccountEventUid::TYPE)]
    private AccountEventUid $id;

    /**
     * ID пользователя
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserUid::TYPE, nullable: false)]
    private ?UserUid $account = null;

    /**
     * Email
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: AccountEmail::TYPE)]
    private AccountEmail $email;

    /**
     * Пароль
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private string $password;

    /**
     * Статус
     */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: AccountStatus::class, cascade: ['all'])]
    private AccountStatus $status;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: AccountModify::class, cascade: ['all'])]
    private AccountModify $modify;


    public function __construct()
    {
        $this->id = new AccountEventUid();
        $this->status = new AccountStatus($this);
        $this->modify = new AccountModify($this, new ModifyAction(ModifyActionNew::class));
    }


    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): AccountEventUid
    {
        return $this->id;
    }


    public function setMain(Account|UserUid $account): void
    {
        $this->account = $account instanceof Account ? $account->getId() : $account;
    }


    public function getAccount(): ?UserUid
    {
        return $this->account;
    }


    public function getEmail(): AccountEmail
    {
        return $this->email;
    }


    public function getPassword(): string
    {
        return $this->password;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof AccountEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {

        if($dto instanceof AccountEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}