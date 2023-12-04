<?php

namespace BaksDev\Auth\Email\Controller\User\Registration\Tests;

use BaksDev\Auth\Email\Controller\User\Registration\VerifyController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group auth-email */
#[When(env: 'test')]
final class VerifyControllerTest extends WebTestCase
{
    /** @see VerifyController */
    private string $controller = '/verify/email';

    /** Запрет на прямой дуступ гостей */
    public function testRoleGuestFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach (TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->request('GET', $this->controller);
            
            self::assertResponseStatusCodeSame(500, 'Page Not Found');
        }
    }

    /** Запрет на прямой дуступ пользователей */
    public function testRoleUserFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach (TestUserAccount::getDevice() as $device)
        {
            $usr = TestUserAccount::getUsr();

            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->loginUser($usr, 'user');
            $client->request('GET', $this->controller);

            self::assertResponseStatusCodeSame(500, 'Page Not Found');
        }
    }
}
