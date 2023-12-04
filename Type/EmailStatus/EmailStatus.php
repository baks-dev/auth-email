<?php

namespace BaksDev\Auth\Email\Type\EmailStatus;


use BaksDev\Auth\Email\Type\EmailStatus\Status\Collection\EmailStatusInterface;

final class EmailStatus
{
	
	public const TYPE = 'account_status';

	private EmailStatusInterface $status;
	

	public function __construct(EmailStatusInterface|self|string $status)
	{

        if(is_string($status) && class_exists($status))
        {
            $instance = new $status();

            if($instance instanceof EmailStatusInterface)
            {
                $this->status = $instance;
                return;
            }
        }

        if($status instanceof EmailStatusInterface)
        {
            $this->status = $status;
            return;
        }

        if($status instanceof self)
        {
            $this->status = $status->getEmailStatus();
            return;
        }

        /** @var EmailStatusInterface $declare */
        foreach(self::getDeclared() as $declare)
        {
            $instance = new self($declare);

            if($instance->getEmailStatusValue() === $status)
            {
                $this->status = new $declare;
                return;
            }
        }


	}


	public function __toString(): string
	{
        return $this->status->getValue();
	}


	public function getEmailStatus() : EmailStatusInterface
	{
		return $this->status;
	}

	public function getEmailStatusValue(): string
	{
		return $this->status->getValue();
	}


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $status)
        {
            /** @var EmailStatusInterface $status */
            $class = new $status;
            $case[$class::sort()] = new self($class);
        }

        ksort($case);

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(EmailStatusInterface::class, class_implements($className), true);
            }
        );
    }

    public function equals(mixed $status): bool
    {
        $status = new self($status);

        return $this->getEmailStatusValue() === $status->getEmailStatusValue();
    }









}