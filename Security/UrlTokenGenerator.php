<?php

namespace BaksDev\Auth\Email\Security;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;

final class UrlTokenGenerator
{
	/**
	 * Получаем криптографически безопасный токен.
	 */
	public function createToken(UserUid $userId, AccountEventUid $event): string
	{
		$encodedData = json_encode([$event], JSON_THROW_ON_ERROR);
		
		return base64_encode(hash_hmac('sha256', $encodedData, $userId, true));
	}
	
}