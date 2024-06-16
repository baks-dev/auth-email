<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\UseCase\User\Verify;

use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use Symfony\Component\Validator\Constraints as Assert;

final class VerifyDTO implements AccountEventInterface
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly AccountEventUid $id;

    #[Assert\Valid]
    private readonly Status\StatusDTO $status;


    public function __construct(AccountEventUid $id)
    {
        $this->status = new Status\StatusDTO();
        $this->id = $id;
    }


    public function setId(AccountEventUid $id): void {}


    public function getEvent(): ?AccountEventUid
    {
        return $this->id;
    }


    /** Статус */
    public function getStatus(): Status\StatusDTO
    {
        return $this->status;
    }

}
