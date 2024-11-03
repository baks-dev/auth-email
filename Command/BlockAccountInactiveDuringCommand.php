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

namespace BaksDev\Auth\Email\Command;


use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Repository\InactiveAccountsDuring\InactiveAccountsDuringInterface;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'baks:auth-email:inactive-block',
    description: 'Заблокировать список не активированных аккаунтов в течение недели'
)]
class BlockAccountInactiveDuringCommand extends Command
{
    public function __construct(
        private readonly InactiveAccountsDuringInterface $InactiveAccountsDuring,
        private readonly AccountHandler $AccountHandler,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('argument', InputArgument::OPTIONAL, 'Описание аргумента');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * Получаем список не активированных аккаунтов в течение недели
         */
        $accounts = $this->InactiveAccountsDuring->find();

        if(is_null($accounts))
        {
            $io->success('Пользовательских аккаунтов не найдено');
            return Command::SUCCESS;
        }

        /** @var AccountEvent $AccountEvent */

        foreach($accounts as $AccountEvent)
        {
            /** @var AccountDTO $AccountDTO */
            $AccountDTO = $AccountEvent->getDto(AccountDTO::class);

            $StatusDTO = $AccountDTO->getStatus();
            $StatusDTO->setStatus(new EmailStatus(EmailStatusBlock::class));

            $handle = $this->AccountHandler->handle($AccountDTO);

            if(false === ($handle instanceof Account))
            {
                $io->writeln(sprintf('<fg=red>%s: Ошибка при блокировке неактивного аккаунта %s</>', $handle, $AccountDTO->getEmail()));
                continue;
            }

            $io->writeln(sprintf('<fg=yellow>Аккаунт %s заблокирован</>', $AccountDTO->getEmail()));
        }

        return Command::SUCCESS;
    }
}