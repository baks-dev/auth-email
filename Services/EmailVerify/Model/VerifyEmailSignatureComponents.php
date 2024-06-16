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

namespace BaksDev\Auth\Email\Services\EmailVerify\Model;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use LogicException;

final class VerifyEmailSignatureComponents
{
    /**
     * @var DateTimeInterface
     */
    private $expiresAt;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var int|null метка времени создания подписи
     */
    private $generatedAt;

    /**
     * @var int expiresAt интервал транслятора
     */
    private $transInterval = 0;

    public function __construct(DateTimeInterface $expiresAt = null, string $uri = null, int $generatedAt = null)
    {
        $this->expiresAt = $expiresAt;
        $this->uri = $uri;
        $this->generatedAt = $generatedAt;
    }

    /**
     * Возвращает полный подписанный URL-адрес, который должен быть отправлен пользователю.
     */
    public function getSignedUrl(): string
    {
        return $this->uri;
    }

    /**
     * Получите время в секундах, в течение которого действительна подпись.
     */
    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * Get the translation message for when a signature expires.
     *
     * This is used in conjunction with the getExpirationMessageData() method.
     * Example usage in a Twig template:
     *
     * <p>{{ components.expirationMessageKey|trans(components.expirationMessageData) }}</p>
     *
     * symfony/translation is required to translate into a non-English locale.
     *
     * @throws LogicException
     */
    public function getExpirationMessageKey(): string
    {
        $interval = $this->getExpiresAtIntervalInstance();

        switch($interval)
        {
            case $interval->y > 0:
                $this->transInterval = $interval->y;

                return '%count% year|%count% years';
            case $interval->m > 0:
                $this->transInterval = $interval->m;

                return '%count% month|%count% months';
            case $interval->d > 0:
                $this->transInterval = $interval->d;

                return '%count% day|%count% days';
            case $interval->h > 0:
                $this->transInterval = $interval->h;

                return '%count% hour|%count% hours';
            default:
                $this->transInterval = $interval->i;

                return '%count% minute|%count% minutes';
        }
    }

    /**
     * @throws LogicException
     */
    public function getExpirationMessageData(): array
    {
        $this->getExpirationMessageKey();

        return ['%count%' => $this->transInterval];
    }

    /**
     * Получаем интервал, для которого действительна подпись.
     *
     * @throws LogicException
     *
     * @psalm-suppress PossiblyFalseArgument
     */
    public function getExpiresAtIntervalInstance(): DateInterval
    {
        if(null === $this->generatedAt)
        {
            throw new LogicException(sprintf('%s initialized without setting the $generatedAt timestamp.', self::class));
        }

        $createdAtTime = DateTimeImmutable::createFromFormat('U', (string) $this->generatedAt);

        return $this->expiresAt->diff($createdAtTime);
    }

}
