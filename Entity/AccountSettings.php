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

use BaksDev\Auth\Email\Type\Settings\AccountSettingsIdentifier;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* Настройки Аккаунта */

#[ORM\Entity()]
#[ORM\Table(name: 'users_account_settings')]
class AccountSettings
{
	public const TABLE = 'users_account_settings';
	
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
	
	
	public function __construct() { $this->id = new AccountSettingsIdentifier(); }
	
	
	public function getId() : AccountSettingsIdentifier
	{
		return $this->id;
	}
	
	
	public function getSettingsTruncate() : int
	{
		return $this->settingsTruncate;
	}
	
	
	public function setSettingsTruncate(int $settingsTruncate) : void
	{
		$this->settingsTruncate = $settingsTruncate;
	}
	
	
	public function getSettingsHistory() : int
	{
		return $this->settingsHistory;
	}
	
	
	public function setSettingsHistory(int $settingsHistory) : void
	{
		$this->settingsHistory = $settingsHistory;
	}
	
	
	public function isRegistration() : bool
	{
		return $this->isRegistration;
	}
	
	
	public function setIsRegistration(bool $isRegistration) : void
	{
		$this->isRegistration = $isRegistration;
	}
	
	
	public function isRestore() : bool
	{
		return $this->isRestore;
	}
	
	
	public function setIsRestore(bool $isRestore) : void
	{
		$this->isRestore = $isRestore;
	}
	
}
