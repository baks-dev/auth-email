<?php

namespace BaksDev\Auth\Email\Type\Email;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

final class AccountEmailType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AccountEmail
    {
        return !empty($value) ? new AccountEmail($value) : null;
    }


    public function getName(): string
    {
        return AccountEmail::TYPE;
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

}
