<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\Type\Status;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class AccountStatusType extends StringType
{
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform) : mixed
	{
		return $value instanceof AccountStatus ? $value->getValue() : (new AccountStatus($value))->getValue();
	}
	
	
	public function convertToPHPValue($value, AbstractPlatform $platform) : mixed
	{
		return $value ? new AccountStatus($value) : null;
	}
	
	
	public function getName() : string
	{
		return AccountStatus::TYPE;
	}
	
	
	public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
	{
		return true;
	}
	
	
	public function getSQLDeclaration(array $column, AbstractPlatform $platform) : string
	{
		$column['length'] = 3;
		
		return $platform->getVarcharTypeDeclarationSQL($column);
	}
	
}