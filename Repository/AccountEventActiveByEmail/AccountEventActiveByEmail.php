<?php

namespace BaksDev\Auth\Email\Repository\AccountEventActiveByEmail;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\ORMQueryBuilder;

final class AccountEventActiveByEmail implements AccountEventActiveByEmailInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    /**
     * Возвращает активное событие аккаунта по e-mail
     */
    public function getAccountEvent(AccountEmail $email): ?AccountEvent
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb->select('event');

        $qb->from(AccountEvent::class, 'event');

        $qb
            ->where('event.email = :email')
            ->setParameter('email', $email, AccountEmail::TYPE);

        $qb->join(Account::class,
            'account',
            'WITH',
            'account.event = event.id'
        );

        /* Проверка статуса ACTIVE */
        $objQueryExistStatus = $this->ORMQueryBuilder->createQueryBuilder(self::class);
        $objQueryExistStatus
            ->select('1')
            ->from(AccountStatus::class, 'event_status')
            ->where('event_status.event = event.id AND event_status.status = :status');


        /* Только активный пользователь */
        $qb->setParameter('status',
            new EmailStatus(EmailStatusActive::class),
            EmailStatus::TYPE
        );

        $qb->andWhere($qb->expr()->exists($objQueryExistStatus->getDQL()));

        return $qb->getOneOrNullResult();

    }

}