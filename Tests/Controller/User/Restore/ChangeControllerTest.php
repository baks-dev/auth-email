<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Restore;

use BaksDev\Auth\Email\Controller\User\Restore\ChangeController;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

final class ChangeControllerTest extends WebTestCase
{
	/** @link ChangeController */
	private string $controller = '/change';
	
	
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