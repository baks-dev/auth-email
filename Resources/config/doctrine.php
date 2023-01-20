<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Email\AccountEmailType;
use BaksDev\Auth\Email\Type\Event\AccountEventType;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\Settings\AccountSettingsIdentifier;
use BaksDev\Auth\Email\Type\Settings\AccountSettingsType;
use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusType;

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrine)
{
    $doctrine->dbal()->type(AccountEventUid::TYPE)->class(AccountEventType::class);
    $doctrine->dbal()->type(AccountEmail::TYPE)->class(AccountEmailType::class);
    $doctrine->dbal()->type(AccountStatus::TYPE)->class(AccountStatusType::class);
    $doctrine->dbal()->type(AccountSettingsIdentifier::TYPE)->class(AccountSettingsType::class);
	
    $emDefault = $doctrine->orm()->entityManager('default');
    
    $emDefault->autoMapping(true);
	
	$emDefault->mapping('AuthEmail')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Auth\Email\Entity')
		->alias('AuthEmail');
};