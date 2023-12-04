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


namespace BaksDev\Auth\Email\Services\EmailVerify\Generator;


use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Type\Id\UserUid;

class VerifyEmailTokenGenerator
{
    /**
     * Уникальная, случайная, криптографически безопасная строка
     */
    private ?string $signingKey;

    public function __construct(string $key = null)
    {
        $this->signingKey = $key;
    }

    /**
     * Получаем криптографически безопасный токен.
     */
    public function createToken(UserUid $userId, AccountEmail $email): string
    {
        $encodedData = json_encode([$userId, $email]);
        return base64_encode(hash_hmac('sha256', $encodedData, $this->signingKey ?: microtime(), true));
    }
}
