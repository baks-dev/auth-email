<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Registration;

use BaksDev\Auth\Email\Controller\User\Registration\RegistrationController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationControllerTest extends WebTestCase
{
	/** @link RegistrationController */
	private string $controller = '/reg';
	
	/** Редирект авторизананных пользователей */
	public function testRoleGuestSuccessful() : void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseIsSuccessful();
	}
	
	
	/** Редирект авторизананных пользователей */
	public function testRoleUserRedirect() : void
	{
		$client = static::createClient();
		
		$user = TestUserAccount::getUser();
		$client->loginUser($user, 'user');

		$crawler = $client->request('GET', $this->controller);
		self::assertResponseRedirects();
	}
	
}