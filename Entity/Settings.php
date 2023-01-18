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

namespace BaksDev\Auth\Email\Entity;


use BaksDev\Auth\Email\Type\Settings\AccountSettings;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/* Настройки Аккаунта */

#[ORM\Entity()]
#[ORM\Table(name: 'users_account_settings')]
class Settings
{
    public const TABLE = 'users_account_settings';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: AccountSettings::TYPE)]
    private AccountSettings $id;

    /** Очищать корзину старше n дней */
    #[ORM\Column(name: 'settings_truncate', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsTruncate = 365;
    
    
    /** Очищать события старше n дней */
    #[ORM\Column(name: 'settings_history', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsHistory = 365;
    
    /** Регистрация пользователей */
    #[ORM\Column(name: 'settings_registration', type: Types::BOOLEAN)]
    private bool $isRegistration = true;
    
    /** Регистрация пользователей */
    #[ORM\Column(name: 'settings_restore', type: Types::BOOLEAN)]
    private bool $isRestore = true;

    public function __construct() { $this->id = new AccountSettings();  }

    /**
    * @return AccountSettings
    */
    public function getId() : AccountSettings
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSettingsTruncate() : int
    {
        return $this->settingsTruncate;
    }
    
    /**
     * @param int $settingsTruncate
     */
    public function setSettingsTruncate(int $settingsTruncate) : void
    {
        $this->settingsTruncate = $settingsTruncate;
    }
    
    /**
     * @return int
     */
    public function getSettingsHistory() : int
    {
        return $this->settingsHistory;
    }
    
    /**
     * @param int $settingsHistory
     */
    public function setSettingsHistory(int $settingsHistory) : void
    {
        $this->settingsHistory = $settingsHistory;
    }
    
    /**
     * @return bool
     */
    public function isRegistration() : bool
    {
        return $this->isRegistration;
    }
    
    /**
     * @param bool $isRegistration
     */
    public function setIsRegistration(bool $isRegistration) : void
    {
        $this->isRegistration = $isRegistration;
    }
    
    /**
     * @return bool
     */
    public function isRestore() : bool
    {
        return $this->isRestore;
    }
    
    /**
     * @param bool $isRestore
     */
    public function setIsRestore(bool $isRestore) : void
    {
        $this->isRestore = $isRestore;
    }
    
    
}
