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

namespace BaksDev\Auth\Email\Repository\ExistUserDevice;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Modify\AccountModify;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\User\Type\Id\UserUid;

final class ExistUserDeviceRepository implements ExistUserDeviceInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder)
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод проверяет наличие указанного девайса у пользователя.
     */
    public function existDeviceByUser(UserUid $usr, string $agent): bool
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->from(AccountEvent::TABLE, 'event');

        $qb->join(
            'event',
            AccountModify::TABLE,
            'modify',
            'modify.event = event.id'
        );

        $qb
            ->andWhere('users.account = :account')
            ->setParameter('account', $usr, UserUid::TYPE);

        $qb
            ->andWhere('modify.user_agent = :agent')
            ->setParameter('agent', $agent);

        return $qb->fetchExist();
    }
}
