<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Login;

use BaksDev\Auth\Email\Controller\User\Login\LogoutController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LogoutControllerTest extends WebTestCase
{
	/** @link LogoutController */
	private string $controller = '/logout';
	
	
	/** Редирект авторизананных пользователей */
	public function testRoleGuestSuccessful() : void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseRedirects();
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