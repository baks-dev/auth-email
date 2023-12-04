<?php

namespace BaksDev\Auth\Email\Controller\User\Restore\Tests;

use BaksDev\Auth\Email\Controller\User\Restore\ResetController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group auth-email */
#[When(env: 'test')]
final class ResetControllerTest extends WebTestCase
{
    /** @see ResetController */
    private string $controller = '/reset';

    /** Запрет доступа по прямой ссылке */
    public function testRoleGuestFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', $this->controller);
        self::assertResponseStatusCodeSame(500);
    }

    /** Запрет доступа авторизананных пользователей */
    public function testRoleUserRedirect(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getUsr();
        $client->loginUser($usr, 'user');

        $crawler = $client->request('GET', $this->controller);
        self::assertResponseStatusCodeSame(500);
    }
}
