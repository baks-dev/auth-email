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

namespace BaksDev\Auth\Email\UseCase\User\Change\Status\Tests;

use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\UseCase\User\Change\Status\StatusDTO;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('auth-email')]
final class StatusDTOTest extends TestCase
{
    private StatusDTO $dto;

    protected function setUp(): void
    {
        $this->dto = new StatusDTO();
    }

    public function testStatus(): void
    {
        $status = $this->dto->getStatus();

        self::assertEquals(EmailStatusActive::STATUS, $status->getEmailStatus()->getValue());
    }

    //    public function testValidation(): void
    //    {
    //        $validator = Validation::createValidatorBuilder()
    //            //->enableAnnotationMapping()
    //            ->getValidator();
    //        $violations = $validator->validate($this->dto);
    //
    //        self::assertEquals(0, $violations->count());
    //
    ////        $violations = $validator->validate($this->dto);
    ////
    ////        self::assertEquals(1, $violations->count());
    ////        self::assertEquals('This value should not be blank.', $violations[0]->getMessage());
    //    }
}
