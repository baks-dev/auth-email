<?php

namespace BaksDev\Auth\Email\Repository\AccountEventActiveByEmail;

use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use Doctrine\ORM\EntityManagerInterface;

final class AccountEventActiveByEmail implements AccountEventActiveByEmailInterface
{
    
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(AccountEmail $email) : ?EntityAccount\Event\AccountEvent
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('event');
        
        $qb->from(EntityAccount\Event\AccountEvent::class, 'event');
        $qb->where('event.email = :email');
        $qb->setParameter('email', $email, AccountEmail::TYPE);

        $qb->join(EntityAccount\Account::class, 'account', 'WITH', 'account.event = event.id');
        
        /* Проверка статуса ACTIVE */
        $objQueryExistStatus = $this->entityManager->createQueryBuilder();
        $objQueryExistStatus->select('1');
        $objQueryExistStatus->from(EntityAccount\Status\AccountStatus::class, 'event_status');
        $objQueryExistStatus->where('event_status.event = event.id AND event_status.status = :status');
        
        /* Только активный пользователь */
        $qb->setParameter('status', new AccountStatus(AccountStatusEnum::ACTIVE), AccountStatus::TYPE);
        
        $qb->andWhere($qb->expr()->exists($objQueryExistStatus->getDQL()));
        
        return $qb->getQuery()->getOneOrNullResult();
        
    }
    
}