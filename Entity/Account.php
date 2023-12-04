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

namespace BaksDev\Auth\Email\Entity;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* Аккаунт пользователя */

#[ORM\Entity]
#[ORM\Table(name: 'users_account')]
class Account
{
    public const TABLE = 'users_account';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserUid::TYPE)]
    private readonly UserUid $id;

    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: AccountEventUid::TYPE, unique: true)]
    private AccountEventUid $event;


    public function __construct(User|UserUid $usr = null)
    {
        if($usr)
        {
            $this->id = $usr instanceof User ? $usr->getId() : $usr;
        }
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): UserUid
    {
        return $this->id;
    }


    public function setEvent(AccountEvent $event): void
    {
        $this->event = $event->getId();
    }


    public function getEvent(): AccountEventUid
    {
        return $this->event;
    }

}