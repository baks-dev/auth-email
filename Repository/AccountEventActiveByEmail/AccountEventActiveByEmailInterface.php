<?php

namespace BaksDev\Auth\Email\Repository\AccountEventActiveByEmail;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;

interface AccountEventActiveByEmailInterface
{
    /**
     * Возвращает активное событие аккаунта по e-mail
     */
    public function getAccountEvent(AccountEmail $email): ?AccountEvent;
}