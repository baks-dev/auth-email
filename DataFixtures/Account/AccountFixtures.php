<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\DataFixtures\Account;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Auth\Email\Repository\AccountEventActiveByEmail\AccountEventActiveByEmailInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Tests\TestUserAccount;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AccountFixtures extends Fixture
{
    //public const ADMIN_USER_REFERENCE = 'user_admin';
    public const  USER_EMAIL = 'admin@local.ru';

    private ExistAccountByEmailInterface $existAccountByEmail;

    private UseCase\AccountHandler $handler;
    private AccountEventActiveByEmailInterface $userAccountByEmail;
    private SymfonyStyle $io;

    public function __construct(
        ExistAccountByEmailInterface      $existAccountByEmail,
        UseCase\AccountHandler            $handler,
        AccountEventActiveByEmailInterface $userAccountByEmail
    )
    {

        $this->existAccountByEmail = $existAccountByEmail;
        $this->handler = $handler;
        //$this->output = $output;
        $this->userAccountByEmail = $userAccountByEmail;
		
        $this->io = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
		
    }

    public function load(ObjectManager $manager): void
    {
        # php bin/console doctrine:fixtures:load --append

		
        $email = new AccountEmail(self::USER_EMAIL);

        /* Проверяем, имеется ли такой пользователь */
        $existUser = $this->existAccountByEmail->get($email);


        if ($existUser) {
            $this->io->note('Пользователь был ранее добавлен');
        } else {
			
            $AccountFixturesDTO = new UseCase\AccountDTO($email);
			
            $Account = $this->handler->handle($AccountFixturesDTO);

            if (!$Account instanceof Account) {
                throw new \Exception(  sprintf('Ошибка %s при создании аккаунта', $Account));
            }

            $this->io->success(sprintf('Добавили пользователя: %s / %s', $AccountFixturesDTO->getEmail(), $AccountFixturesDTO->getPasswordPlain()));

        }


        /* Проверяем, имеется ли такой пользователь */
        //$countUser = $this->countUserAccountByEmail->get($email, null);

        $event = $this->userAccountByEmail->get($email);
		$this->io->warning(sprintf('Для тестирования в класса %s необходимо указать идентификатор : %s',
			TestUserAccount::class,
			$event?->getAccount()));
		
        $this->addReference(self::class, $event);

    }

}