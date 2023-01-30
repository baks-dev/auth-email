<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Restore;

use BaksDev\Auth\Email\Controller\User\Restore\RestoreController;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RestoreControllerTest extends WebTestCase
{
	/** @link RestoreController */
	private string $controller = '/restore';
	
	
	/** Запрет доступа по прямой ссылке */
	public function testRoleGuestFail() : void
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