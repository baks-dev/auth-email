<?php

namespace BaksDev\Auth\Email\Tests\Controller\User\Login;

use BaksDev\Auth\Email\Controller\User\Login\LoginController;
use BaksDev\Auth\Email\Repository\AccountEventActiveByEmail\AccountEventActiveByEmailInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Repository\UserRepository;
use BaksDev\Users\User\Tests\TestUserAccount;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginControllerTest extends WebTestCase
{
    /** @link LoginController */
    private string $controller = '/login';

	/** Редирект авторизананных пользователей */
	public function testRoleGuestSuccessful() : void
	{
		$client = static::createClient();
		
		//$user = TestUserAccount::get();
		//$client->loginUser($user, 'user');
		
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