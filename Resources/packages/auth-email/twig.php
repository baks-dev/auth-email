<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\TwigConfig;

return static function (TwigConfig $config, ContainerConfigurator $configurator) {
    $config->path(__DIR__.'/../view', 'auth-email');
};
