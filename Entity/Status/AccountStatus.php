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
use BaksDev\Auth\Email\Type\Status\AccountStatus as AccountActivityStatus;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Статус Аккаунта */
#[ORM\Entity]
#[ORM\Table(name: 'users_account_status')]
#[ORM\Index(columns: ['status'])]
class AccountStatus extends EntityEvent
{
    public const TABLE = 'users_account_status';
    
    /** ID события */
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'status', targetEntity: AccountEvent::class)]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    protected AccountEvent $event;
    
    #[ORM\Column(type: AccountActivityStatus::TYPE)]
    protected AccountActivityStatus $status;
    
    /**
     * @param AccountEvent $event
     */
    public function __construct(AccountEvent $event) { $this->event = $event; }
    
    /**
     * @param $dto
     * @return mixed
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof AccountStatusInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    /**
     * @param $dto
     * @return mixed
     * @throws Exception
     */
    public function setEntity($dto) : mixed
    {

        if($dto instanceof AccountStatusInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
