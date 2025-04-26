<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
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
    #[ORM\OneToOne(targetEntity: AccountStatus::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private AccountStatus $status;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: AccountModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
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
