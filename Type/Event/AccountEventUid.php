<?php

namespace BaksDev\Auth\Email\Type\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class AccountEventUid implements ValueResolverInterface
{
    public const TYPE = 'account_event';
    
    private Uuid $value;
	
    public  $id;
    
    public function __construct(AbstractUid|string|null $value = null)
    {
        if($value === null)
        {
            $value = Uuid::v7();
        }
        
        else if(is_string($value))
        {
            $value = new UuidV7($value);
        }
        
        $this->value = $value;
    }
    
    public function __toString() : string
    {
        return $this->value;
    }
    
    public function getValue() : AbstractUid
    {
        return $this->value;
    }
	
	
	/**
	 * @return Uuid
	 */
	public function getId() : Uuid
	{
		return $this->id;
	}
	
	/**
	 * @param Uuid $id
	 */
	public function setId( $id) : void
	{
		$this->id = $id;
	}
	
	
	
	public function resolve(Request $request, ArgumentMetadata $argument) : iterable
	{
		$argumentType = $argument->getType();
		
		if($argumentType !== self::class)
		{
			return [];
		}
		
		$value = $request->attributes->get($argument->getName()) ?: $request->attributes->get('id') ?: $request->get('id');
		
		/*if(!is_string($value))
		{
			return [];
		}*/
		
		return [new self($value)];
	}
}