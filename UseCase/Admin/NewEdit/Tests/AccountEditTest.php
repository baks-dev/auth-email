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

namespace BaksDev\Auth\Email\UseCase\Admin\NewEdit\Tests;

use BaksDev\Auth\Email\Controller\Admin\Tests\EditControllerTest as AdminEditController;
use BaksDev\Auth\Email\Controller\User\Account\Tests\EditControllerTest as UserEditController;
use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group auth-email
 * @group auth-email-usecase
 *
 * @depends BaksDev\Auth\Email\UseCase\Admin\NewEdit\Tests\AccountNewTest::class
 * @depends BaksDev\Auth\Email\Controller\Admin\Tests\EditControllerTest::class
 * @depends BaksDev\Auth\Email\Controller\User\Account\Tests\EditControllerTest::class
 *
 * @see AccountNewTest
 * @see AdminEditController
 * @see UserEditController
 */
#[When(env: 'test')]
final class AccountEditTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        //self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $AccountEvent = $em->getRepository(AccountEvent::class)->find(AccountEventUid::TEST);
        self::assertNotNull($AccountEvent);


        /** @var AccountDTO $AccountDTO */
        $AccountDTO = $AccountEvent->getDto(AccountDTO::class);

        $AccountEmail = new AccountEmail('test@test.local');
        self::assertTrue($AccountEmail->isEqual($AccountDTO->getEmail()));

        $AccountDTO->setEmail(new AccountEmail('test@test.edit'));

        self::assertNotEquals('WitNZGkqTv', $AccountDTO->getPassword()); // не равен, т.к. пасс хешируется
        $AccountDTO->setPassword('SULKpzLBIZ');
        $AccountDTO->setPasswordHash('SULKpzLBIZ');

        $StatusDTO = $AccountDTO->getStatus();
        self::assertEquals(EmailStatusNew::STATUS, $StatusDTO->getStatus()->getEmailStatusValue());

        $EmailStatus = new EmailStatus(new EmailStatusActive());
        $StatusDTO->setStatus($EmailStatus);


        /** UPDATE */
        //self::bootKernel();

        /** @var AccountHandler $AccountHandler */
        $AccountHandler = self::getContainer()->get(AccountHandler::class);
        $handle = $AccountHandler->handle($AccountDTO);
        self::assertTrue(($handle instanceof Account), $handle.': Ошибка Account');

        $em->clear();
        //$em->close();
    }
}