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

namespace BaksDev\Auth\Email\UseCase\Admin\Delete\Tests;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Repository\UserAccountEvent\UserAccountEventInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusBlock;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\Admin\Delete\AccountDeleteDTO;
use BaksDev\Auth\Email\UseCase\Admin\Delete\AccountDeleteHandler;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group auth-email
 * @group auth-email-usecase
 *
 * @depends BaksDev\Auth\Email\UseCase\Admin\NewEdit\Tests\AccountEditTest::class
 * @depends BaksDev\Auth\Email\Controller\Admin\Tests\EditControllerTest::class
 * @depends BaksDev\Auth\Email\Controller\User\Account\Tests\EditControllerTest::class
 *
 * @see     AdminEditController
 * @see     UserEditController
 * @see     AccountEditTest
 */
#[When(env: 'test')]
final class AccountDeleteTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        //self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);


        /** @var UserAccountEventInterface $UserAccountEvent */
        $UserAccountEvent = $container->get(UserAccountEventInterface::class);
        $AccountEvent = $UserAccountEvent->getAccountEventByUser(new UserUid());
        self::assertNotNull($AccountEvent);


        /** @var AccountDTO $AccountDTO */
        $AccountDTO = $AccountEvent->getDto(AccountDTO::class);

        $AccountEmail = new AccountEmail('test@test.edit');
        self::assertTrue($AccountEmail->isEqual($AccountDTO->getEmail()));
        self::assertEquals('SULKpzLBIZ', $AccountDTO->getPassword());

        $StatusDTO = $AccountDTO->getStatus();
        self::assertEquals(EmailStatusActive::STATUS, $StatusDTO->getStatus()->getEmailStatusValue());


        /** @var AccountDeleteDTO $AccountDeleteDTO */
        $AccountDeleteDTO = $AccountEvent->getDto(AccountDeleteDTO::class);

        $AccountEmail = new AccountEmail('test@test.edit');
        self::assertTrue($AccountEmail->isEqual($AccountEmail));
        $StatusDTO = $AccountDeleteDTO->getStatus();
        self::assertEquals(EmailStatusBlock::STATUS, $StatusDTO->getStatus()->getEmailStatusValue());


        /** DELETE */

        /** @var AccountDeleteHandler $AccountDeleteHandler */
        $AccountDeleteHandler = self::getContainer()->get(AccountDeleteHandler::class);
        $handle = $AccountDeleteHandler->handle($AccountDeleteDTO);
        self::assertTrue(($handle instanceof Account), $handle.': Ошибка Account');


        $Account = $em->getRepository(Account::class)
            ->find(UserUid::TEST);

        self::assertNull($Account);

        $em->clear();
        //$em->close();

    }

    /**
     * Этот метод вызывается после выполнения последнего теста этого тестового класса.
     */
    public static function tearDownAfterClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $AccountEventCollection = $em->getRepository(AccountEvent::class)
            ->findBy(['account' => UserUid::TEST]);

        foreach($AccountEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        /** @var DBALQueryBuilder $dbal */
        $dbal = self::getContainer()->get(DBALQueryBuilder::class);

        $qb = $dbal->createQueryBuilder(self::class);
        $qb
            ->delete(User::TABLE)
            ->where('usr = :usr')
            ->setParameter('usr', UserUid::TEST)
            ->executeQuery();

        $qb = $dbal->createQueryBuilder(self::class);
        $qb
            ->delete(Account::TABLE)
            ->where('id = :account')
            ->setParameter('account', UserUid::TEST)
            ->executeQuery();

        $em->clear();
        //$em->close();
    }
}