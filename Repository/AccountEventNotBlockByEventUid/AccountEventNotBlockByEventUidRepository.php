<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
use Doctrine\ORM\EntityManagerInterface;

final class AccountEventNotBlockByEventUidRepository implements AccountEventNotBlockByEventUidInterface
{
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Возвращает событие активированного пользователя по идентификатору события
     */
    public function findAccountEventById(AccountEventUid $event): ?AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb->from(AccountEvent::class, 'event');

        $qb
            ->where('event.id = :event')
            ->setParameter('event', $event, AccountEventUid::TYPE);

        $qb->join(
            Account::class,
            'account',
            'WITH',
            'account.event = event.id'
        );

        $qb->join(
            AccountStatus::class,
            'status',
            'WITH',
            'status.event = event.id AND  status.status != :status'
        );

        /* Только не заблокированный пользователь */
        $qb->setParameter(
            'status',
            new EmailStatus(EmailStatusBlock::class),
            EmailStatus::TYPE
        );

        return $qb->getQuery()->getOneOrNullResult();
    }

}
