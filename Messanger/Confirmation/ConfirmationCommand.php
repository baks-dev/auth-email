<?php

namespace BaksDev\Auth\Email\Messanger\Confirmation;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;

final class ConfirmationCommand
{
	/** Идентификатор события */
	private readonly AccountEventUid $event;
	
	
	public function __construct(AccountEventUid $event)
	{
		$this->event = $event;
	}
	
	
	/**
	 * @return AccountEventUid
	 */
	public function getEvent() : AccountEventUid
	{
		return $this->event;
	}
	
}

