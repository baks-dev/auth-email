<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\Entity;

use BaksDev\Auth\Email\Type\Settings\AccountSettingsIdentifier;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* Настройки Аккаунта */

#[ORM\Entity()]
#[ORM\Table(name: 'users_account_settings')]
class AccountSettings
{
    /**
     * ID
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: AccountSettingsIdentifier::TYPE)]
    private AccountSettingsIdentifier $id;

    /**
     * Очищать корзину старше n дней
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Assert\Range(max: 365)]
    #[ORM\Column(name: 'settings_truncate', type: Types::SMALLINT)]
    private int $settingsTruncate = 30;

    /**
     * Очищать события старше n дней
     */
    #[ORM\Column(name: 'settings_history', type: Types::SMALLINT)]
    private int $settingsHistory = 30;

    /**
     * Регистрация пользователей
     */
    #[ORM\Column(name: 'settings_registration', type: Types::BOOLEAN)]
    private bool $isRegistration = true;

    /**
     * Регистрация пользователей
     */
    #[ORM\Column(name: 'settings_restore', type: Types::BOOLEAN)]
    private bool $isRestore = true;


    public function __construct()
    {
        $this->id = new AccountSettingsIdentifier();
    }


    public function getId(): AccountSettingsIdentifier
    {
        return $this->id;
    }


    public function getSettingsTruncate(): int
    {
        return $this->settingsTruncate;
    }


    public function setSettingsTruncate(int $settingsTruncate): void
    {
        $this->settingsTruncate = $settingsTruncate;
    }


    public function getSettingsHistory(): int
    {
        return $this->settingsHistory;
    }


    public function setSettingsHistory(int $settingsHistory): void
    {
        $this->settingsHistory = $settingsHistory;
    }


    public function isRegistration(): bool
    {
        return $this->isRegistration;
    }


    public function setIsRegistration(bool $isRegistration): void
    {
        $this->isRegistration = $isRegistration;
    }


    public function isRestore(): bool
    {
        return $this->isRestore;
    }


    public function setIsRestore(bool $isRestore): void
    {
        $this->isRestore = $isRestore;
    }

}
