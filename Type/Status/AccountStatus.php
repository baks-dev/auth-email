<?php

namespace BaksDev\Auth\Email\Type\Status;

/**
 * Типы полей
 */
final class AccountStatus
{
    
    public const TYPE = 'account_status';
    
    /**
     * @var AccountStatusEnum
     */
    private AccountStatusEnum $status;
    
    /**
     * Field constructor
     *
     * @param string|AccountStatusEnum $status
     */
    public function __construct(string|AccountStatusEnum $status)
    {
        $this->status = $status instanceof AccountStatusEnum ? $status : AccountStatusEnum::from($status);
    }
    
    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->status->value;
    }
    
    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->status->value;
    }
	
	/**
	 * @return AccountStatusEnum
	 */
	public function getStatus() : AccountStatusEnum
	{
		return $this->status;
	}
	
	
	
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->status->name;
    }
    
    /**
     * @return AccountStatusEnum
     */
    public function getGender() : AccountStatusEnum
    {
        return $this->status;
    }
    
    /**
     * @return array
     */
    public static function cases() : array
    {
        $case = null;
        
        foreach(AccountStatusEnum::cases() as $status)
        {
            $case[] = new self($status);
        }
        
        return $case;
    }
}