<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Auth\Email\Type\EmailStatus;

use BaksDev\Auth\Email\Type\EmailStatus\Status\Collection\EmailStatusInterface;

final class EmailStatus
{
    public const string TYPE = 'account_status';

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
                $this->status = new $declare();
                return;
            }
        }
    }


    public function __toString(): string
    {
        return $this->status->getValue();
    }


    public function getEmailStatus(): EmailStatusInterface
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
            $class = new $status();
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
