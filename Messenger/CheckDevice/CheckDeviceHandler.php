<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Messenger\CheckDevice;

use BaksDev\Auth\Email\Repository\CurrentUserAccount\CurrentUserAccountInterface;
use BaksDev\Auth\Email\Repository\ExistUserDevice\ExistUserDeviceRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[Autoconfigure(public: true)]
#[AsMessageHandler(priority: 0)]
final class CheckDeviceHandler
{
    private CurrentUserAccountInterface $currentUserAccount;

    private ExistUserDeviceRepository $existUserDevice;

    public function __construct(
        CurrentUserAccountInterface $currentUserAccount,
        ExistUserDeviceRepository $existUserDevice
    )
    {
        $this->currentUserAccount = $currentUserAccount;
        $this->existUserDevice = $existUserDevice;
    }

    /**
     * Проверяем, что авторизованный пользователь ранее авторизовывался с такого девайса
     */
    public function __invoke(CheckDeviceMessage $command): void
    {
        $isBrowscap = (bool) ini_get('browscap');

        if($isBrowscap === false)
        {
            return;
        }

        /* Проверяем что пользователь активный */
        $Account = $this->currentUserAccount->fetchAccountAssociative($command->getId());

        if($Account)
        {
            /* Проверяем что пользователь ранее авторизовывался с таким User-agent */
            $isDevice = $this->existUserDevice->existDeviceByUser($command->getId(), $command->getAgent());

            /* Если User-agent новый - отправляем уведомление на Email */
            if($isDevice === false)
            {
                $browscap = get_browser($command->getAgent());

                $platform = $browscap->platform; // Операционная система
                $browser = $browscap->browser; // браузер
                $device_type = $browscap->device_type; // тип девайса (Desktop/Mobile)

                /*  TODO: отправляем уведомление на Email то UserAgent изменился */
            }
        }
    }
}
