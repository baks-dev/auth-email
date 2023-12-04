<?php

namespace BaksDev\Auth\Email\Controller\User\Restore\Tests;

use BaksDev\Auth\Email\Controller\User\Restore\RestoreController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group auth-email */
#[When(env: 'test')]
final class RestoreControllerTest extends WebTestCase
{
    /** @see RestoreController */
    private string $controller = '/restore';

    /** Запрет доступа по прямой ссылке */
    public function testRoleGuestFail(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', $this->controller);
        self::assertResponseIsSuccessful();
    }

    /** Редирект авторизованных пользователей */
    public function testRoleUserRedirect(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getUsr();
        $client->loginUser($usr, 'user');

        $client->request('GET', $this->controller);
        self::assertResponseRedirects();
    }
}
