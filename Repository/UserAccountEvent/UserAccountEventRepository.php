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

declare(strict_types=1);

namespace BaksDev\Auth\Email\Repository\UserAccountEvent;


use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Repository\AccountEventNotBlock\AccountEventNotBlockByEvent\AccountEventNotBlockByEventInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;

final class UserAccountEventRepository implements UserAccountEventInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @deprecated
     * @see AccountEventByUserInterface
     *
     * Метод возвращает активное событие указанного пользователя
     */
    public function getAccountEventByUser(UserUid $usr): ?AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->from(Account::class, 'account')
            ->where('account.id = :usr')
            ->setParameter('usr', $usr, UserUid::TYPE);

        $qb
            ->select('account_event')
            ->join(
                AccountEvent::class,
                'account_event',
                'WITH',
                'account_event.id = account.event'
            );


        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @deprecated
     * @see AccountEventNotBlockByEmailInterface
     */
    public function getAccountEventNotBlockByEmail(AccountEmail $email): ?AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb
            ->from(AccountEvent::class, 'event')
            ->where('event.email = :email')
            ->setParameter('email', $email, AccountEmail::TYPE);

        $qb->join(Account::class, 'account', 'WITH', 'account.event = event.id');

        $qb->join(
            AccountStatus::class,
            'status',
            'WITH',
            'status.event = event.id AND  status.status != :status'
        );

        // Только не заблокированный пользователь
        $qb->setParameter(
            'status',
            new EmailStatus(EmailStatusBlock::class),
            EmailStatus::TYPE
        );

        return $qb->getQuery()->getOneOrNullResult();
    }


    /**
     * @deprecated
     * @see AccountEventNotBlockByEventInterface
     */
    public function getAccountEventNotBlockByEvent(AccountEventUid $event): ?AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb
            ->from(AccountEvent::class, 'event')
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

        // Только не заблокированный пользователь
        $qb->setParameter(
            'status',
            new EmailStatus(EmailStatusBlock::class),
            EmailStatus::TYPE
        );

        return $qb->getQuery()->getOneOrNullResult();
    }
}
