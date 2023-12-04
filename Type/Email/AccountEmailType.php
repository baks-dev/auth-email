<?php

namespace BaksDev\Auth\Email\Type\Email;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Types;

final class AccountEmailType extends StringType
{
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
	{
		return (string) $value;
	}

	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		return !empty($value) ? new AccountEmail($value) : null;
	}
	
	
	public function getName(): string
	{
        return AccountEmail::TYPE;
	}
	
	
	public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
	{
		return true;
	}
	
}