<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Auth\Email\Repository\CurrentUserAccount\CurrentUserAccountInterface;
use BaksDev\Auth\Email\Repository\CurrentUserAccount\UserProfileEmailDecorator;

return static function(ContainerConfigurator $configurator) {
	$services = $configurator->services()
		->defaults()
		->autowire()      // Automatically injects dependencies in your services.
		->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
	;
	
	$namespace = 'BaksDev\Auth\Email';
	
	$services->load($namespace.'\Controller\\', __DIR__.'/../../Controller')
		->tag('controller.service_arguments')
	;
	
	$services->load($namespace.'\Security\\', __DIR__.'/../../Security');
	
	$services->load($namespace.'\UseCase\\', __DIR__.'/../../UseCase')
		->exclude('../../UseCase/**/*DTO.php')
	;
	
	$services->load($namespace.'\Repository\\', __DIR__.'/../../Repository');
	
	$services->load($namespace.'\Messanger\\', __DIR__.'/../../Messanger')
		->exclude('../../Messanger/**/*Command.php')
	;
	
	$services->load($namespace.'\DataFixtures\\', __DIR__.'/../../DataFixtures')
		->exclude(__DIR__.'/../../DataFixtures/**/*DTO.php')
	;
	
	$services->set(UserProfileEmailDecorator::class)
		->decorate(\BaksDev\Users\User\Repository\UserProfile\UserProfileInterface::class, null, 20)
		->arg('$profile', service('.inner'))
		->arg('$current', service(CurrentUserAccountInterface::class))
		->autowire()
	;
	
};

