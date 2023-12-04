<?php

namespace BaksDev\Auth\Email\Repository\UserNew;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;

interface UserNewInterface
{
    public function getNewUserByAccountEvent(AccountEventUid $event) : ?UserUid;

    public function getNewUserByUserUid(UserUid $usr) : ?UserUid;
}