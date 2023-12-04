<?php

namespace BaksDev\Auth\Email\Messenger\Restore;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Type\Locale\Locale;

final class RestoreAccountMessage
{
    /** Электронная почта */
    private readonly AccountEmail $email;


    /** Локаль */
    private readonly Locale $local;


    public function __construct(AccountEmail $email, Locale $local)
    {
        $this->email = $email;
        $this->local = $local;
    }


    public function getEmail(): AccountEmail
    {
        return $this->email;
    }

    /**
     * @return Locale
     */
    public function getLocal(): Locale
    {
        return $this->local;
    }


}

