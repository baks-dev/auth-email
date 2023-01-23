<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Auth\Email\Repository\CurrentUserAccount;

use BaksDev\Auth\Email\Repository\CurrentUserAccount\CurrentUserAccountInterface;
use BaksDev\Users\User\Repository\UserProfile\UserProfileInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileEmailDecorator implements UserProfileInterface
{

	public ?UserUid $user;
	
	public string $username;
	
	public string $contact;
	
	public string $type;
	
	public function __construct(
		UserProfileInterface $profile,
		CurrentUserAccountInterface $current,
	)
	{
		$this->user = $profile->user;
		$this->type = $profile->getType();
		
		/* Переопределяем свойства */
		$Account = $current->fetchAccountAssociative($profile->user);
		$this->username = $Account['account_email'];
		$this->contact = $Account['account_email'];
		
	
	}
	
	/**  Username пользователя */
	public function getUsername() : ?string
	{
		return $this->username;
	}
	
	/** Контакт */
	public function getContact() : ?string
	{
		return $this->contact;
	}
	
	/** Тип пользователя */
	public function getType() : ?string
	{
		return $this->type;
	}
	
	/** Адрес персональной страницы */
	public function getPage() : ?string
	{
		return null;
	}
	
	/** Аватарка */
	public function getImage() : ?string
	{
		return null;
	}
}