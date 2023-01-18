<?php

namespace BaksDev\Auth\Email\Messanger\Restore;

//use BaksDev\Users\AuthEmail\Account\Type\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;

final class RestoreCommand
{
    /** Электронная почта */
    private readonly AccountEmail $email;

    public function __construct(AccountEmail $email)
    {
        $this->email = $email;
    }

    public function getEmail(): AccountEmail
    {
        return $this->email;
    }
}

