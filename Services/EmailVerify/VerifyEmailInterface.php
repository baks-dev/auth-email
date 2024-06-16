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

namespace BaksDev\Auth\Email\Services\EmailVerify;

use BaksDev\Auth\Email\Services\EmailVerify\Exception\VerifyEmailExceptionInterface;
use BaksDev\Auth\Email\Services\EmailVerify\Model\VerifyEmailSignatureComponents;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Type\Id\UserUid;

/**
 * Генерирует и проверяет URL-адрес для подтверждения электронной почты.
 */
interface VerifyEmailInterface
{
    /**
     * URL-адрес, который можно отправить пользователю по электронной почте.
     *
     * @param string $routeName Имя маршрута, который будет использоваться для проверки пользователей
     * @param UserUid $userId Uid пользователя
     * @param AccountEmail $userEmail Электронная почта
     * @param array $extraParams Дополнительные параметры (например Uid пользователя)
     */
    public function generateSignature(
        string $routeName,
        UserUid $userId,
        AccountEmail $userEmail,
        array $extraParams = []
    ): VerifyEmailSignatureComponents;

    /**
     * Подтвердите подписанный запрос подтверждения по электронной почте.
     *
     * Если что-то не так с подтверждением по электронной почте, будет выдано VerifyEmailExceptionInterface.
     *
     * @param string $signedUrl URL-адрес, который пользователь щелкнул в своем электронном письме
     * @param UserUid $userId Uid пользователя
     * @param AccountEmail $userEmail Электронная почта, которую текущий пользователь пытается проверить
     *
     * @throws VerifyEmailExceptionInterface
     */
    public function validateEmailConfirmation(string $signedUrl, UserUid $userId, AccountEmail $userEmail): void;
}
