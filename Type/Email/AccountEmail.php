<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Type\Email;

use InvalidArgumentException;

final class AccountEmail
{
    public const string TEST = 'test@test.local';

    public const string TYPE = 'account_email';

    private string $value;

    public function __construct(?string $value = null)
    {

        if(!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            throw new InvalidArgumentException(sprintf('Incorrect Email %s', $value));
        }

        $this->value = mb_strtolower($value);
    }


    public function __toString(): string
    {
        return $this->value;
    }


    public function isEqual(mixed $other): bool
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
