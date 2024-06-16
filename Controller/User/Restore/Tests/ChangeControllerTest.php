<?php

namespace BaksDev\Auth\Email\Controller\User\Restore\Tests;

use BaksDev\Auth\Email\Controller\User\Restore\ChangeController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group auth-email */
#[When(env: 'test')]
final class ChangeControllerTest extends WebTestCase
{
    /** @see ChangeController */
    private string $controller = '/change';

    /** Запрет доступа по прямой ссылке */
    public function testRoleGuestFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->request('GET', $this->controller);

            self::assertResponseStatusCodeSame(404);
        }
    }

    /** Запрет доступа авторизананных пользователей */
    public function testRoleUserRedirect(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $usr = TestUserAccount::getUsr();

            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->loginUser($usr, 'user');
            $client->request('GET', $this->controller);

            self::assertResponseStatusCodeSame(404);
        }
    }
}
