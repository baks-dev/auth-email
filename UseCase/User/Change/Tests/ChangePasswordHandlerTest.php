<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\UseCase\User\Change\Tests;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Repository\CurrentAccountEvent\CurrentAccountEventInterface;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordHandler;
use BaksDev\Users\User\Type\Id\UserUid;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('auth-email')]
class ChangePasswordHandlerTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var $CurrentAccountEventRepository CurrentAccountEventInterface */
        $CurrentAccountEventRepository = $container->get(CurrentAccountEventInterface::class);

        $AccountEvent = $CurrentAccountEventRepository->getByUser(new UserUid(UserUid::TEST));
        self::assertInstanceOf(AccountEvent::class, $AccountEvent);


        $ChangePasswordDTO = new ChangePasswordDTO($AccountEvent->getId());
        $ChangePasswordDTO->setPasswordPlain(uniqid('', true));

        /** @var ChangePasswordHandler $ChangePasswordHandler */
        $ChangePasswordHandler = self::getContainer()->get(ChangePasswordHandler::class);
        $handle = $ChangePasswordHandler->handle($ChangePasswordDTO);

        self::assertInstanceOf(Account::class, $handle);
    }

}