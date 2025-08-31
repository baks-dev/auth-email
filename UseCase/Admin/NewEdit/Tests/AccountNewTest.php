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

declare(strict_types=1);

namespace BaksDev\Auth\Email\UseCase\Admin\NewEdit\Tests;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountDTO;
use BaksDev\Auth\Email\UseCase\Admin\NewEdit\AccountHandler;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('auth-email')]
final class AccountNewTest extends KernelTestCase
{

    public static function setUpBeforeClass(): void
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
            ->delete(User::class)
            ->where('usr = :usr')
            ->setParameter('usr', UserUid::TEST)
            ->executeQuery();

        $qb = $dbal->createQueryBuilder(self::class);
        $qb
            ->delete(Account::class)
            ->where('id = :account')
            ->setParameter('account', UserUid::TEST)
            ->executeQuery();


    }


    public function testUseCase(): void
    {
        $EmailStatusNew = new EmailStatusNew();
        $AccountDTO = new AccountDTO();

        $AccountEmail = new AccountEmail('test@test.local');
        $AccountDTO->setEmail($AccountEmail);
        self::assertSame($AccountEmail, $AccountDTO->getEmail());

        $AccountDTO->setPasswordPlain('gkrJsIgdly');
        self::assertEquals('gkrJsIgdly', $AccountDTO->getPasswordPlain());

        $AccountDTO->setPasswordHash('WitNZGkqTv');
        self::assertEquals('WitNZGkqTv', $AccountDTO->getPassword());

        $StatusDTO = $AccountDTO->getStatus();
        self::assertEquals('new', $StatusDTO->getStatus()->getEmailStatusValue());


        /** PERSIST */

        //self::bootKernel();

        /** @var AccountHandler $AccountHandler */
        $AccountHandler = self::getContainer()->get(AccountHandler::class);
        $handle = $AccountHandler->handle($AccountDTO);

        self::assertTrue(($handle instanceof Account), $handle.': Ошибка Account');

    }


    public function testComplete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $Account = $em->getRepository(Account::class)->find(UserUid::TEST);
        self::assertNotNull($Account);

        $em->clear();
    }
}