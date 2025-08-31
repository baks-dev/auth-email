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

namespace BaksDev\Auth\Email\UseCase\User\Change\Tests;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Validator\Validation;

/** @group auth-email */
#[When(env: 'test')]
#[Group('auth-email')]
final class ChangePasswordDTOTest extends TestCase
{
    private ChangePasswordDTO $dto;

    protected function setUp(): void
    {
        $this->dto = new ChangePasswordDTO(new AccountEventUid());
    }

    public function testId(): void
    {
        $id = new AccountEventUid();
        $changePasswordDTO = new ChangePasswordDTO($id);
        self::assertSame($id, $changePasswordDTO->getEvent());
        self::assertInstanceOf(AccountEventUid::class, $this->dto->getEvent());
    }

    public function testPasswordPlain(): void
    {
        $this->dto->setPasswordPlain('PtzPwYoTxR');
        self::assertSame('PtzPwYoTxR', $this->dto->getPasswordPlain());
    }

    public function testPasswordHash(): void
    {
        $this->dto->setPasswordHash('KLvXjIyMfj');
        self::assertSame('KLvXjIyMfj', $this->dto->getPassword());
    }

    public function testValidationSuccess(): void
    {
        $validator = Validation::createValidatorBuilder()->getValidator();

        $this->dto->setPasswordPlain('pnvdMpFhYl');
        $violations = $validator->validate($this->dto);

        self::assertEquals(0, $violations->count());
    }

    //    public function testValidationFiled(): void
    //    {
    //        $validator = Validation::createValidatorBuilder()->getValidator();
    //        $violations = $validator->validate($this->dto);
    //
    //        //self::assertEquals(1, $violations->count());
    //        //self::assertEquals('This value should not be blank.', $violations[0]->getMessage());
    //
    //        $this->dto->setPasswordPlain('jZUAi');
    //        $violations = $validator->validate($this->dto);
    //
    //        self::assertEquals(1, $violations->count());
    //        self::assertEquals('This value is too short. It should have 8 characters or more.', $violations[0]->getMessage());
    //    }
}
