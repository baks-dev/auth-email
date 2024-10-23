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

namespace BaksDev\Auth\Email\Repository\UserNew;

use BaksDev\Auth\Email\Entity as AccountEntity;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;

final class UserNewRepository implements UserNewInterface
{
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /** Получаем идентификатор UserUid с атрибутом Email по идентификатору со статусом NEW */

    public function getNewUserByUserUid(UserUid $usr): ?UserUid
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('NEW %s(account.id, event.email)', UserUid::class);
        $qb->select($select);

        $qb
            ->from(AccountEntity\Account::class, 'account')
            ->where('account.id = :usr')
            ->setParameter('usr', $usr, UserUid::TYPE);

        $qb->join(
            AccountEntity\Event\AccountEvent::class,
            'event',
            'WITH',
            'event.id = account.event'
        );

        $qb->join(
            AccountEntity\Status\AccountStatus::class,
            'status',
            'WITH',
            'status.event = event.id AND status.status = :status'
        );


        $qb->setParameter(
            'status',
            new EmailStatus(EmailStatusNew::class), /* только НОВЫЙ */
            EmailStatus::TYPE
        );

        return $qb->getQuery()->getOneOrNullResult();
    }


    /** Получаем UserUid по событию c атрибутом Email со статусом NEW */

    public function getNewUserByAccountEvent(AccountEventUid $event): ?UserUid
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('NEW %s(account.id, event.email)', UserUid::class);
        $qb->select($select);

        $qb
            ->from(AccountEntity\Account::class, 'account')
            ->where('account.event = :event')
            ->setParameter('event', $event, AccountEventUid::TYPE);

        $qb->join(
            AccountEntity\Event\AccountEvent::class,
            'event',
            'WITH',
            'event.id = account.event'
        );

        /* только со статусом НОВЫЙ */
        $qb->join(
            AccountEntity\Status\AccountStatus::class,
            'status',
            'WITH',
            'status.event = event.id AND status.status = :status'
        );

        $qb->setParameter(
            'status',
            new EmailStatus(EmailStatusNew::class),
            EmailStatus::TYPE
        );

        return $qb->getQuery()->getOneOrNullResult();
    }

}
