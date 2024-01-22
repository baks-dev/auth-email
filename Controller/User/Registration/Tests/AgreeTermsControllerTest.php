<?php

namespace BaksDev\Auth\Email\Controller\User\Registration\Tests;

use BaksDev\Auth\Email\Controller\User\Registration\AgreeTermsController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group auth-email */
#[When(env: 'test')]
final class AgreeTermsControllerTest extends WebTestCase
{
    /** @see AgreeTermsController */
    private string $controller = '/agree/terms';

    /** Любой доступ к странице */
    public function testRoleGuestSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach (TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->request('GET', $this->controller);
            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }

    /** Доступ авторизованных пользователей */
    public function testRoleUserRedirect(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach (TestUserAccount::getDevice() as $device)
        {
            $usr = TestUserAccount::getUsr();

            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->loginUser($usr, 'user');
            $client->request('GET', $this->controller);
            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }
}
