<?php

namespace BaksDev\Auth\Email\Repository\ExistAccountByEmail;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Type\Id\UserUid;

interface ExistAccountByEmailInterface
{
    /**
     * Метод проверяет наличие указанного e-mail.
     */
    public function isExistsEmail(AccountEmail $email, ?UserUid $userUid = null);
}