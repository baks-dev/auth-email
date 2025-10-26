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

namespace BaksDev\Auth\Email\Repository\AccountEventNotBlockByEventUid;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final  class CurrentAccountEventNotBlockByEventRepository implements CurrentAccountEventNotBlockByEventInterface
{
    private AccountEventUid|false $event;

    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    public function forAccountEvent(AccountEventUid|AccountEvent $event): self
    {
        if($event instanceof AccountEvent)
        {
            $event = $event->getId();
        }

        $this->event = $event;

        return $this;
    }

    /**
     * Возвращает событие активированного пользователя по идентификатору события
     */
    public function find(): AccountEvent|false
    {
        if(false === ($this->event instanceof AccountEventUid))
        {
            throw new InvalidArgumentException('Invalid Argument AccountEvent');
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->from(AccountEvent::class, 'event')
            ->where('event.id = :event')
            ->setParameter(
                key: 'event',
                value: $this->event,
                type: AccountEventUid::TYPE,
            );

        $orm->join(
            Account::class,
            'account',
            'WITH',
            'account.id = event.account',
        );

        $orm
            ->select('current')
            ->join(
                AccountEvent::class,
                'current',
                'WITH',
                'current.id = account.event',
            );

        $orm
            ->join(
                AccountStatus::class,
                'status',
                'WITH',
                'status.event = current.id AND status.status != :status',
            )
            ->setParameter(
                key: 'status',
                value: EmailStatusBlock::class, // Только не заблокированный пользователь
                type: EmailStatus::TYPE,
            );

        return $orm->getQuery()->getOneOrNullResult() ?? false;
    }

}
