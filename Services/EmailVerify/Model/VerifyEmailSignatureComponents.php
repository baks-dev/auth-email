<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
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

    public function __construct(DateTimeInterface $expiresAt = null, string $uri = null, ?int $generatedAt = null)
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
