<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Registration;

use BaksDev\Auth\Email\Controller\User\Registration\AgreeTermsController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AgreeTermsControllerTest extends WebTestCase
{
	/** @link AgreeTermsController */
	private string $controller = '/agree/terms';
	
	/** Любой доступ к странице */
	public function testRoleGuestSuccessful() : void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseIsSuccessful();
	}
	
	/** Доступ авторизананных пользователей */
	public function testRoleUserRedirect() : void
	{
		$client = static::createClient();
		
		$user = TestUserAccount::getUser();
		$client->loginUser($user, 'user');
		
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseIsSuccessful();
	}
}