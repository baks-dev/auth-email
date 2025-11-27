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

namespace BaksDev\Auth\Email\Repository\ExistAccountByEmail;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use InvalidArgumentException;

final class ExistAccountByEmailRepository implements ExistAccountByEmailInterface
{

    private AccountEmail|false $email = false;

    private UserUid|false $user = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function fromEmail(AccountEmail|string $email): self
    {
        if(is_string($email))
        {
            $email = new AccountEmail($email);
        }

        $this->email = $email;

        return $this;
    }

    /**
     * Вызвать если необходимо найти кроме указанного пользователя
     */
    public function fromUser(User|UserUid|null|false $user): self
    {
        if(empty($user))
        {
            $this->user = false;
            return $this;
        }

        if($user instanceof User)
        {
            $user = $user->getId();
        }

        $this->user = $user;

        return $this;
    }

    /**
     * Метод проверяет наличие указанного e-mail.
     */
    public function isExists(): bool
    {
        if(false === ($this->email instanceof AccountEmail))
        {
            throw new InvalidArgumentException('Invalid Argument AccountEmail');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(AccountEvent::class, 'users')
            ->where('users.email = :account_email')
            ->setParameter(
                key: 'account_email',
                value: $this->email,
                type: AccountEmail::TYPE,
            );

        /* Если указан идентификатор пользователя - исключаем из поиска */
        if($this->user instanceof UserUid)
        {
            $dbal
                ->andWhere('users.account != :account')
                ->setParameter(
                    key: 'account',
                    value: $this->user,
                    type: UserUid::TYPE,
                );
        }

        return $dbal->fetchExist();
    }
}
