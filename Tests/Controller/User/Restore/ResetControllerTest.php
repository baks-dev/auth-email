<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Restore;

use BaksDev\Auth\Email\Controller\User\Restore\ResetController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResetControllerTest  extends WebTestCase
{
	/** @link ResetController */
	private string $controller = '/reset';
	
	/** Запрет доступа по прямой ссылке */
	public function testRoleGuestFail() : void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', $this->controller);
		self::assertResponseStatusCodeSame(500);
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