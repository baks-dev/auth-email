<?php

// namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Core\Type\Locale\Locale;
use Symfony\Config\FrameworkConfig;

return static function(FrameworkConfig $config) {

	$config->translator()->paths([__DIR__.'/../translations']);
};






