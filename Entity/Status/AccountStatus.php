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

namespace BaksDev\Auth\Email\Entity\Status;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Статус Аккаунта */

#[ORM\Entity]
#[ORM\Table(name: 'users_account_status')]
#[ORM\Index(columns: ['status'])]
class AccountStatus extends EntityEvent
{
    public const TABLE = 'users_account_status';

    /** ID события */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: AccountEvent::class, inversedBy: 'status')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private AccountEvent $event;

    #[Assert\NotBlank]
    #[ORM\Column(type: EmailStatus::TYPE)]
    private EmailStatus $status;


    public function __construct(AccountEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof AccountStatusInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {

        if($dto instanceof AccountStatusInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getStatus(): EmailStatus
    {
        return $this->status;
    }

}
