<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Registration;

use BaksDev\Auth\Email\Controller\User\Registration\VerifyController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class VerifyControllerTest extends WebTestCase
{
	/** @link VerifyController */
	private string $controller = '/verify/email';
	
	
	/** Запрет на прямой дуступ гостей */
	public function testRoleGuestFail() : void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseStatusCodeSame(500, 'Page Not Found');
	}
	
	
	/** Запрет на прямой дуступ пользователей */
	public function testRoleUserFail() : void
	{
		$client = static::createClient();
		
		$user = TestUserAccount::getUser();
		$client->loginUser($user, 'user');
		
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseStatusCodeSame(500, 'Page Not Found');
	}
	
}