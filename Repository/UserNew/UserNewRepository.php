<?php

namespace BaksDev\Auth\Email\Repository\UserNew;

use BaksDev\Auth\Email\Entity as AccountEntity;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
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
