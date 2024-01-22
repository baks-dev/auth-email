<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\Controller\User\Account\Tests;

use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 *
 * @group auth-email
 *
 * @depends BaksDev\Auth\Email\UseCase\Admin\NewEdit\Tests\AccountNewTest::class
 */
#[When(env: 'test')]
final class EditControllerTest extends WebTestCase
{
    private const URL = '/account/email/edit';

    // доступ по роли ROLE_ADMIN
    public function testRolUserSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getUsr();

        $client->loginUser($usr, 'user');
        $client->request('GET', self::URL);
        
        // Page Not Found (500 Internal Server Error)
        //self::assertResponseStatusCodeSame(500);

        self::assertResponseIsSuccessful();
    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', self::URL);

        // Full authentication is required to access this resource
        self::assertResponseStatusCodeSame(401);
    }
}
