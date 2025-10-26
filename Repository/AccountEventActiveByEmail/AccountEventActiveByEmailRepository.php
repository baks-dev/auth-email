<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Repository\AccountEventActiveByEmail;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\ORMQueryBuilder;

final readonly class AccountEventActiveByEmailRepository implements AccountEventActiveByEmailInterface
{
    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Возвращает активное событие аккаунта по e-mail
     */
    public function getAccountEvent(AccountEmail $email): ?AccountEvent
    {
        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->select('event')
            ->from(AccountEvent::class, 'event')
            ->where('event.email = :email')
            ->setParameter(
                key: 'email',
                value: $email,
                type: AccountEmail::TYPE,
            );

        $orm->join(
            Account::class,
            'account',
            'WITH',
            'account.event = event.id',
        );

        /* Проверка статуса ACTIVE */
        $objQueryExistStatus = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $objQueryExistStatus
            ->select('1')
            ->from(AccountStatus::class, 'event_status')
            ->where('event_status.event = event.id AND event_status.status = :status');


        /* Только активный пользователь */
        $orm->setParameter(
            'status',
            new EmailStatus(EmailStatusActive::class),
            EmailStatus::TYPE,
        );

        $orm->andWhere($orm->expr()->exists($objQueryExistStatus->getDQL()));

        return $orm->getOneOrNullResult();

    }

}
