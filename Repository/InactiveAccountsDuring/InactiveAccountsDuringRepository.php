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

namespace BaksDev\Auth\Email\Repository\InactiveAccountsDuring;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Modify\AccountModify;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;


final readonly class InactiveAccountsDuringRepository implements InactiveAccountsDuringInterface
{

    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Метод возвращает Email аккаунты, которые не были активированы в течении суток
     */
    public function find(): array|null
    {
        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm->select('event');
        $orm->from(Account::class, 'account');

        $orm
            ->join(
                AccountStatus::class,
                'account_status',
                'WITH',
                'account_status.event = account.event AND account_status.status = :status'
            )
            ->setParameter('status', EmailStatusNew::class, EmailStatus::TYPE);

        $date = (new DateTimeImmutable('now'))->add(DateInterval::createFromDateString('1 week'));

        $orm
            ->join(
                AccountModify::class,
                'modify',
                'WITH',
                'modify.event = account.event 
                
                ' // AND modify.modDate < :date
            )//->setParameter('date', $date, Types::DATETIME_IMMUTABLE)
        ;

        $orm
            ->select('event')
            ->leftJoin(
                AccountEvent::class,
                'event',
                'WITH',
                'event.id = account.event'
            );

        return $orm->getResult();
    }
}