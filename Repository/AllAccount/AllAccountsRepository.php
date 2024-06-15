<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Repository\AllAccount;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Modify\AccountModify;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;

final class AllAccountsRepository implements AllAccountsInterface
{
    private PaginatorInterface $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    ) {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает пагинатор Account
     */
    public function fetchAllAccountsAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('account.id');
        $qb->from(Account::TABLE, 'account');

        /* Событие */
        $qb->addSelect('account_event.id as event');
        $qb->addSelect('account_event.email');
        $qb->join(
            'account',
            AccountEvent::TABLE,
            'account_event',
            'account_event.id = account.event'
        );

        /* Статус */
        $qb->addSelect('account_status.status');
        $qb->join(
            'account',
            AccountStatus::TABLE,
            'account_status',
            'account_status.event = account.event'
        );

        /* Модификатор */
        $qb->addSelect('account_modify.mod_date as update');
        $qb->join(
            'account',
            AccountModify::TABLE,
            'account_modify',
            'account_modify.event = account.event'
        );

        /* Поиск */
        if($search->getQuery())
        {
            $qb
                ->createSearchQueryBuilder($search)
                ->addSearchEqualUid('account.id')
                ->addSearchEqualUid('account.event')
                ->addSearchLike('account_event.email')
            ;
        }

        $qb->orderBy('account_status.status', 'ASC');
        $qb->addOrderBy('account_modify.mod_date', 'DESC');

        return $this->paginator->fetchAllAssociative($qb);
    }
}
