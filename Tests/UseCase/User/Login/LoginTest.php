<?php

namespace BaksDev\Auth\Email\Tests\UseCase\User\Login;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\UseCase\User\Login\LoginDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LoginTest extends KernelTestCase
{
	public function testDTO() : void
	{
		$AccountEmail = new AccountEmail('test@test.local');
		$password = 'jYpXY7IGW9';
		
		$newDTO = new LoginDTO();
		$newDTO->setEmail($AccountEmail);
		$newDTO->setPassword($password);
		
		self::assertEquals($newDTO->getEmail(), $AccountEmail);
		self::assertEquals($newDTO->getPassword(), $password);
		
	}
}