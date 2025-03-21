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

namespace BaksDev\Auth\Email\Repository\AccountEventNotBlock\AccountEventNotBlockByEmail;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use InvalidArgumentException;


final class AccountEventNotBlockByEmailRepository implements AccountEventNotBlockByEmailInterface
{
    private AccountEmail|false $email = false;

    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    public function forEmail(AccountEmail|string $email): self
    {
        if(is_string($email))
        {
            $email = new AccountEmail($email);
        }

        $this->email = $email;

        return $this;
    }

    /**
     * Метод возвращает активное событие пользователя по email со статусом, не равным «Block»
     *
     * Вызывается через вызов метода forEmail($event)
     *
     * <code>
     * $this
     * ->forEvent($event)
     * ->find();
     * </code>
     */
    public function find(): AccountEvent|false
    {
        if(false === ($this->email instanceof AccountEmail))
        {
            throw new InvalidArgumentException('Invalid Argument $email');
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->from(AccountEvent::class, 'event')
            ->where('event.email = :email')
            ->setParameter('email', $this->email, AccountEmail::TYPE);

        $orm
            ->leftJoin(Account::class,
                'account',
                'WITH',
                'account.id = event.account'
            );

        $orm
            ->join(
                AccountStatus::class,
                'status',
                'WITH',
                'status.event = account.event AND status.status != :status'
            )
            ->setParameter(
                'status',
                new EmailStatus(EmailStatusBlock::class),
                EmailStatus::TYPE
            );


        $orm
            ->select('account_event')
            ->join(
                AccountEvent::class,
                'account_event',
                'WITH',
                'account_event.id = account.event'
            );

        return $orm->getOneOrNullResult() ?: false;
    }
}