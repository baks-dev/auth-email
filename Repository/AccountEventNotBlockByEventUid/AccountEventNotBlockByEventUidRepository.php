<?php

namespace BaksDev\Auth\Email\Repository\AccountEventNotBlockByEventUid;


use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
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
	public function findAccountEventById(AccountEventUid $event) : ?AccountEvent
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
		
		$qb->join(AccountStatus::class,
			'status',
			'WITH',
			'status.event = event.id AND  status.status != :status'
		);
		
		/* Только не заблокированный пользователь */
		$qb->setParameter('status', new EmailStatus(EmailStatusBlock::class), EmailStatus::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
}