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

namespace BaksDev\Auth\Email\Repository\CurrentUserAccount;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;

final class CurrentUserAccountRepository implements CurrentUserAccountInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder)
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /** Текущий аккаунт авторизованного пользователя
     * возвращает массив с полями:
     *
     * account_id - ID аккаунта; <br>
     * account_event - ID события; <br>
     * account_email - Email пользователя; <br>
     */
    public function fetchAccountAssociative(UserUid $usr): bool|array
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(User::class, 'users')
            ->where('users.usr = :usr')
            ->setParameter('usr', $usr, UserUid::TYPE);

        $dbal
            ->addSelect('account.id AS account_id')
            ->join(
                'users',
                Account::class,
                'account',
                'account.id = users.usr'
            );


        $dbal
            ->addSelect('account_event.id AS account_event')
            ->addSelect('account_event.email AS account_email')
            ->join(
                'account',
                AccountEvent::class,
                'account_event',
                'account_event.id = account.event'
            );

        $dbal->join(
            'account',
            AccountStatus::class,
            'account_status',
            '
              account_status.event = account.event AND
              account_status.status = :status
          '
        );

        $dbal->setParameter('status', EmailStatusActive::class, EmailStatus::TYPE);

        return $dbal
            ->enableCache('auth-email', 3600)
            ->fetchAssociative();
    }

}
