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

namespace BaksDev\Auth\Email\Command\Upgrade;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use BaksDev\Core\Command\Update\ProjectUpgradeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function bin2hex;

#[AsCommand(
    name: 'baks:auth-email:admin',
    description: 'Добавляет администратора ресурса',
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeAccountAdminCommand extends Command implements ProjectUpgradeInterface
{
    private ExistAccountByEmailInterface $existAccountByEmail;
    private string $HOST;
    private AccountHandler $accountHandler;

    public function __construct(
        #[Autowire(env: 'HOST')] string $HOST,
        ExistAccountByEmailInterface $existAccountByEmail,
        AccountHandler $accountHandler,
    )
    {
        parent::__construct();

        $this->existAccountByEmail = $existAccountByEmail;
        $this->HOST = $HOST;
        $this->accountHandler = $accountHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $AccountEmail = new  AccountEmail('admin@'.$this->HOST);
        $existAccount = $this->existAccountByEmail->isExistsEmail($AccountEmail);

        if(!$existAccount)
        {
            $io = new SymfonyStyle($input, $output);
            $io->text('Добавляем аккаунт администратора ресурса');

            do
            {
                $bytes = openssl_random_pseudo_bytes(5, $innerStrong);
            }
            while(!$bytes || !$innerStrong);

            $passwordPlain = bin2hex($bytes);

            $AccountDTO = new AccountDTO();
            $AccountDTO->setEmail($AccountEmail);
            $AccountDTO->setPasswordPlain($passwordPlain);
            $AccountDTO->getStatus()->setStatus(new EmailStatus(EmailStatusActive::class));

            $handle = $this->accountHandler->handle($AccountDTO);

            if(!$handle instanceof Account)
            {
                $io->success(
                    sprintf('Ошибка %s при создании аккаунта', $handle)
                );

                return Command::FAILURE;
            }

            $io->success(
                sprintf(
                    'Администратор ресурса: %s / %s',
                    $AccountDTO->getEmail(),
                    $AccountDTO->getPasswordPlain()
                )
            );
        }

        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 100;
    }
}
