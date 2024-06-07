<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator) {

	$services = $configurator->services()
		->defaults()
		->autowire()
		->autoconfigure()
	;

    $NAMESPACE = 'BaksDev\Auth\Email\\';

    $MODULE = substr(__DIR__, 0, strpos(__DIR__, "Resources"));

    $services->load($NAMESPACE, $MODULE)
        ->exclude([
            $MODULE.'{Entity,Resources,Type}',
            $MODULE.'**/*Message.php',
            $MODULE.'**/*DTO.php',
        ])
    ;

    $services->load($NAMESPACE.'Type\EmailStatus\Status\\', $MODULE.'Type/EmailStatus/Status');

};

