<?php

namespace BaksDev\Auth\Email\Type\Event;

use BaksDev\Core\Type\UidType\UidType;

final class AccountEventType extends UidType
{
	public function getClassType(): string
	{
		return AccountEventUid::class;
	}
	
	
	public function getName(): string
	{
		return AccountEventUid::TYPE;
	}
	
}