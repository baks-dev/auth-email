<?php

namespace BaksDev\Auth\Email\Type\Email;

use InvalidArgumentException;

final class AccountEmail
{
	public const TEST = 'test@test.local';

	public const TYPE = 'account_email';

	private $value;
	

	public function __construct(?string $value = null)
	{

		if(!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			throw new InvalidArgumentException('Incorrect Email.');
		}
		
		$this->value = mb_strtolower($value);
	}
	
	
	public function __toString(): string
	{
		return $this->value;
	}
	
	
	public function isEqual(mixed $other) : bool
	{
        $other = new self((string) $other);
		return $this->getValue() === $other->getValue();
	}
	
	
	public function getValue(): string
	{
		return $this->value;
	}
	
	
	public function getUserName(): string
	{
		return substr($this->value, 0, strrpos($this->value, '@'));
	}
	
	
	public function getHostName(): string
	{
		return substr($this->value, strrpos($this->value, '@') + 1);
	}
	
}