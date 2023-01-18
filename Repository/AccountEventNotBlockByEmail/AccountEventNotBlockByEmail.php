<?php

namespace BaksDev\Auth\Email\Repository\AccountEventNotBlockByEmail;

use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use Doctrine\ORM\EntityManagerInterface;

final class AccountEventNotBlockByEmail implements AccountEventNotBlockByEmailInterface
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(AccountEmail $email): ?EntityAccount\Event\AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb->from(EntityAccount\Event\AccountEvent::class, 'event');
        $qb->where('event.email = :email');
        $qb->setParameter('email', $email, AccountEmail::TYPE);

        $qb->join(EntityAccount\Account::class, 'account', 'WITH', 'account.event = event.id');

        $qb->join(EntityAccount\Status\AccountStatus::class, 'status', 'WITH', 'status.event = event.id AND  status.status != :status');

        /* Только не заблокированный пользователь */
        $qb->setParameter('status', new AccountStatus(AccountStatusEnum::BLOCK), AccountStatus::TYPE);

        return $qb->getQuery()->getOneOrNullResult();
    }
}