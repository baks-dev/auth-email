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

namespace BaksDev\Auth\Email\Controller\Admin\Tests;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group auth-email
 *
 * @depends BaksDev\Auth\Email\UseCase\Admin\NewEdit\Tests\AccountNewTest::class
 */
#[When(env: 'test')]
final class EditAdminControllerTest extends WebTestCase
{
    private const string URL = '/admin/account/email/edit/%s';

    private const string ROLE = 'ROLE_ACCOUNT_EMAIL_EDIT';


    /** Доступ по роли */
    public function testRoleSuccessful(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getModer(self::ROLE);

        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(self::URL, AccountEventUid::TEST));

        self::assertResponseIsSuccessful();

        self::assertTrue(true);
    }

    // доступ по роли ROLE_ADMIN
    public function testRoleAdminSuccessful(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getAdmin();

        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(self::URL, AccountEventUid::TEST));

        self::assertResponseIsSuccessful();

        self::assertTrue(true);
    }

    // доступ по роли ROLE_USER
    public function testRoleUserDeny(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getUsr();
        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(self::URL, AccountEventUid::TEST));

        self::assertResponseStatusCodeSame(403);

        self::assertTrue(true);
    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', sprintf(self::URL, AccountEventUid::TEST));

        // Full authentication is required to access this resource
        self::assertResponseStatusCodeSame(401);
    }
}
