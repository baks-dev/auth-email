<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\TwigConfig;

return static function (TwigConfig $config)
{
    
    $config->path(__DIR__.'/../view', 'AuthEmail');

//    $config->global('AuthEmail')->value(true);
//    $config->global('AuthEmailLogin')->value(true);
//    $config->global('AuthEmailRegistration')->value(true);
//    $config->global('AuthEmailRestore')->value(true);
    
};






