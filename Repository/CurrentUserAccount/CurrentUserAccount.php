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

namespace BaksDev\Auth\Email\Repository\CurrentUserAccount;

use BaksDev\Auth\Email\Entity as AccountEntity;

use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class CurrentUserAccount implements CurrentUserAccountInterface
{
	private Connection $connection;
	
	
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}
	
	
	/** Текущий аккаунт авторизованного пользователя
	 * возвращает массив с полями:
	 *
	 * account_id - ID аккаунта; <br>
	 * account_event - ID события; <br>
	 * account_email - Email пользователя; <br>
	 */
	public function fetchAccountAssociative(UserUid $user) : ?array
	{
		$qb = $this->connection->createQueryBuilder();
		
		$qb->from(User::TABLE, 'users');
		
		$qb->addSelect('account.id AS account_id'); /* ID аккаунта */
		$qb->join('users', AccountEntity\Account::TABLE, 'account', 'account.id = users.user_id');
		
		$qb->addSelect('account_event.id AS account_event'); /* ID события */
		$qb->addSelect('account_event.email AS account_email'); /* Email пользователя */
		
		$qb->join(
			'account',
			AccountEntity\Event\AccountEvent::TABLE,
			'account_event',
			'account_event.id = account.event'
		);
		
		$qb->join(
			'account',
			AccountEntity\Status\AccountStatus::TABLE,
			'account_status',
			'
              account_status.event = account.event AND
              account_status.status = :status
          '
		);
		$qb->setParameter('status', new AccountStatus(AccountStatusEnum::ACTIVE), AccountStatus::TYPE);
		
		$qb->where('users.user_id = :user');
		$qb->setParameter('user', $user, UserUid::TYPE);
		
		/* Кешируем результат запроса */
		$cacheFilesystem = new FilesystemAdapter('CacheAuthEmail');
		
		$config = $this->connection->getConfiguration();
		$config?->setResultCache($cacheFilesystem);
		
		return $this->connection->executeCacheQuery(
			$qb->getSQL(),
			$qb->getParameters(),
			$qb->getParameterTypes(),
			new QueryCacheProfile((60 * 60 * 30), 'current_user_account'.$user)
		)->fetchAssociative();
	}
	
}