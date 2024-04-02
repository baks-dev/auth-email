<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

use BaksDev\Auth\Email\Entity as AccountEntity;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;

final class UserAccountEventRepository implements UserAccountEventInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAccountEventByUser(UserUid $usr): ?AccountEntity\Event\AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('account_event');

        $qb->from(AccountEntity\Account::class, 'account');

        $qb->join(AccountEntity\Event\AccountEvent::class, 'account_event', 'WITH', 'account_event.id = account.event');

        $qb->where('account.id = :usr');
        $qb->setParameter('usr', $usr, UserUid::TYPE);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAccountEventNotBlockByEmail(AccountEmail $email): ?AccountEntity\Event\AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb->from(AccountEntity\Event\AccountEvent::class, 'event');
        $qb->where('event.email = :email');
        $qb->setParameter('email', $email, AccountEmail::TYPE);

        $qb->join(AccountEntity\Account::class, 'account', 'WITH', 'account.event = event.id');

        $qb->join(
            AccountEntity\Status\AccountStatus::class,
            'status',
            'WITH',
            'status.event = event.id AND  status.status != :status'
        );

        // Только не заблокированный пользователь
        $qb->setParameter('status', new EmailStatus(EmailStatusBlock::class), EmailStatus::TYPE);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAccountEventNotBlockByEvent(AccountEventUid $event): ?AccountEntity\Event\AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb->from(AccountEntity\Event\AccountEvent::class, 'event');
        $qb->where('event.id = :event');
        $qb->setParameter('event', $event, AccountEventUid::TYPE);

        $qb->join(AccountEntity\Account::class, 'account', 'WITH', 'account.event = event.id');

        $qb->join(
            AccountEntity\Status\AccountStatus::class,
            'status',
            'WITH',
            'status.event = event.id AND  status.status != :status'
        );

        // Только не заблокированный пользователь
        $qb->setParameter('status', new EmailStatus(EmailStatusBlock::class), EmailStatus::TYPE);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
