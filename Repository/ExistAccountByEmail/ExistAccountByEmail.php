<?php

namespace BaksDev\Auth\Email\Repository\ExistAccountByEmail;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\User\Type\Id\UserUid;

final class ExistAccountByEmail implements ExistAccountByEmailInterface
{

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder)
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод проверяет наличие указанного e-mail.
     */
    public function isExistsEmail(AccountEmail $email, ?UserUid $userUid = null): bool
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->from(AccountEvent::TABLE, 'users');
        $qb
            ->where('users.email = :account_email')
            ->setParameter('account_email', $email, AccountEmail::TYPE);

        /* Если указан идентификатор пользователя - исключаем из поиска */
        if($userUid)
        {
            $qb
                ->andWhere('users.account != :account')
                ->setParameter('account', $userUid, UserUid::TYPE);
        }

        return $qb->fetchExist();
    }
}
