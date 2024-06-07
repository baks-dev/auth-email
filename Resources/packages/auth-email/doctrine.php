<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Email\AccountEmailType;
use BaksDev\Auth\Email\Type\Event\AccountEventType;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\Settings\AccountSettingsIdentifier;
use BaksDev\Auth\Email\Type\Settings\AccountSettingsType;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatusType;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine) {




	$doctrine->dbal()->type(AccountEventUid::TYPE)->class(AccountEventType::class);
	$doctrine->dbal()->type(AccountEmail::TYPE)->class(AccountEmailType::class);
	$doctrine->dbal()->type(EmailStatus::TYPE)->class(EmailStatusType::class);
	$doctrine->dbal()->type(AccountSettingsIdentifier::TYPE)->class(AccountSettingsType::class);


    /** Резолверы */
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(AccountEventUid::class)->class(AccountEventUid::class);

	$emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $MODULE = substr(__DIR__, 0, strpos(__DIR__, "Resources"));

	$emDefault->mapping('auth-email')
		->type('attribute')
		->dir($MODULE.'Entity')
		->isBundle(false)
		->prefix('BaksDev\Auth\Email\Entity')
		->alias('auth-email')
	;
};