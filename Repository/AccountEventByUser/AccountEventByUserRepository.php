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

namespace BaksDev\Auth\Email\Repository\AccountEventByUser;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use InvalidArgumentException;


final class AccountEventByUserRepository implements AccountEventByUserInterface
{
    private UserUid|false $user = false;

    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    public function forUser(User|UserUid|string $user): self
    {
        if($user instanceof User)
        {
            $user = $user->getId();
        }

        if(is_string($user))
        {
            $user = new UserUid($user);
        }

        $this->user = $user;

        return $this;
    }


    /**
     * Метод возвращает активное событие указанного пользователя
     */
    public function find(?UserUid $user = null): AccountEvent|false
    {
        if($user instanceof UserUid)
        {
            $this->user = $user;
        }

        if(false === ($this->user instanceof UserUid))
        {
            throw new InvalidArgumentException('Invalid Argument $event');
        }

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(Account::class, 'account')
            ->where('account.id = :usr')
            ->setParameter('usr', $this->user, UserUid::TYPE);

        $qb
            ->select('account_event')
            ->join(
                AccountEvent::class,
                'account_event',
                'WITH',
                'account_event.id = account.event'
            );

        return $qb->getOneOrNullResult() ?: false;
    }
}