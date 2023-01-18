<?php

namespace BaksDev\Auth\Email\Repository\ExistAccountByEmail;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Connection;

final class ExistAccountByEmail implements ExistAccountByEmailInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get(AccountEmail $email, ?UserUid $userUid = null)
    {
        $qbExist = $this->connection->createQueryBuilder();

        $qbExist->select('1');
        $qbExist->from(AccountEvent::TABLE, 'users');
        $qbExist->where('users.email = :account_email');


        $qb = $this->connection->createQueryBuilder();
        $qb->select(sprintf('EXISTS(%s)', $qbExist->getSQL()));

        $qb->setParameter('account_email', $email, AccountEmail::TYPE);

        if($userUid)
        {
            $qb->andWhere('users.account != :account_id');
            $qb->setParameter('account_id', $userUid, UserUid::TYPE);
        }

        return $qb->fetchOne();
    }




}