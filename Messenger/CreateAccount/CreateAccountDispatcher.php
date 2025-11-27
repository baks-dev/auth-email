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
 *
 */

declare(strict_types=1);

namespace BaksDev\Auth\Email\Messenger\CreateAccount;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Создает профиль пользователя при создании аккаунта для авторизации через Яндекс
 */
#[AsMessageHandler(priority: 0)]
final readonly class CreateAccountDispatcher
{
    public function __construct(
        #[Target('authEmailLogger')] private LoggerInterface $logger,
        #[Autowire(env: 'APP_SECRET')] private string $secret,
        private TranslatorInterface $translator,
        private AccountHandler $accountHandler,
    ) {}

    public function __invoke(CreateAccountMessage $message): void
    {
        $AccountDTO = new AccountDTO();

        /** User */
        $AccountDTO->setUser($message->getUser());

        /** Email */
        $AccountDTO->setEmail($message->getEmail());

        /** Status */
        $StatusDTO = $AccountDTO->getStatus();
        $StatusDTO->setStatus(new EmailStatus(EmailStatusActive::class));

        /** Password */
        $AccountDTO->setPasswordPlain($this->secret);

        $Account = $this->accountHandler->handle($AccountDTO);

        if(false === $Account instanceof Account)
        {
            $this->logger->critical(
                message: sprintf(
                    '%s: Ошибка создания аккаунта',
                    $this->translator->trans($Account, domain: 'admin.account'),
                ),
                context: [self::class.':'.__LINE__,]
            );
        }
    }
}

