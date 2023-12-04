<?php

namespace BaksDev\Auth\Email\Repository\AccountEventNotBlockByEventUid;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;

interface AccountEventNotBlockByEventUidInterface
{
    /**
     * Возвращает событие активированного пользователя по идентификатору
     */
    public function findAccountEventById(AccountEventUid $event) : ?AccountEvent;
}