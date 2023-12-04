<?php

namespace BaksDev\Auth\Email\Controller\User\Registration\Tests;

use BaksDev\Auth\Email\Controller\User\Registration\RegistrationController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group auth-email */
#[When(env: 'test')]
final class RegistrationControllerTest extends WebTestCase
{
    /** @see RegistrationController */
    private string $controller = '/reg';

    /** Редирект авторизананных пользователей */
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
    }

    /** Редирект авторизованных пользователей */
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
            self::assertResponseRedirects();
        }
    }
}
