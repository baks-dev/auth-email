<?php

namespace BaksDev\Auth\Email\Type\Event;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class AccountEventUid extends Uid  //implements ValueResolverInterface
{
    public const TEST = '0188a991-96f3-742d-b4ff-680422baeef6';

    public const TYPE = 'account_event';

}
