<?php

namespace BaksDev\Auth\Email\Repository\UserNewByAccountEvent;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;
use BaksDev\Auth\Email\Entity as EntityAccount;

final class UserNewByAccountEvent implements UserNewByAccountEventInterface
{
	private EntityManagerInterface $entityManager;
	
	
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	
	/** Получаем UserUid по событию со статусом NEW */
	public function get(AccountEventUid $event)
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$select = sprintf('NEW %s(account.id, event.email)', UserUid::class);
		$qb->select($select);
		
		$qb->from(EntityAccount\Account::class, 'account');
		$qb->where('account.event = :event');
		$qb->setParameter('event', $event, AccountEventUid::TYPE);
		
		$qb->join(EntityAccount\Event\AccountEvent::class, 'event', 'WITH', 'event.id = account.event');
		
		/* только со статусом НОВЫЙ */
		$qb->join(EntityAccount\Status\AccountStatus::class,
			'status',
			'WITH',
			'status.event = event.id AND status.status = :status'
		);
		
		$AccountStatusNEW = new AccountStatus(AccountStatusEnum::NEW);
		$qb->setParameter('status', $AccountStatusNEW, AccountStatus::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
}