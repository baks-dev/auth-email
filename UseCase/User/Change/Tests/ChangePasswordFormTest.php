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

namespace BaksDev\Auth\Email\UseCase\User\Change\Tests;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordForm;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\Test\TypeTestCase;

/** @group auth-email */
#[When(env: 'test')]
final class ChangePasswordFormTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        /* DATA */
        $AccountEventUid = new AccountEventUid();
        $passwordPlain = 'hgbk609waG';

        /* FORM */
        $model = new ChangePasswordDTO($AccountEventUid);
        $form = $this->factory->create(ChangePasswordForm::class, $model);

        $formData = [
            'passwordPlain' => ['first' => $passwordPlain, 'second' => $passwordPlain],
            'change' => true,
        ];

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());

        /* OBJECT */
        $expected = new ChangePasswordDTO($AccountEventUid);
        $expected->setPasswordPlain($passwordPlain);

        self::assertEquals($expected, $model);


        /* VIEW */
        $view = $form->createView();
        $children = $view->children;

        foreach(array_keys($formData) as $key)
        {
            self::assertArrayHasKey($key, $children);
        }
    }
}
