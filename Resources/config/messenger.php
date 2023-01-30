<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Auth\Email\Messanger\Confirmation\ConfirmationCommand;
use Symfony\Config\Framework\MessengerConfig;
use Symfony\Config\FrameworkConfig;

return static function(FrameworkConfig $config) {
	$messenger = $config->messenger();
	
	//$messenger->routing(ConfirmationCommand::class)->senders(['sync']);
	
	//$config->messenger()->routing(Handler\Reset\Token\Command::class)->senders(['async_priority_high']);
	//$config->messenger()->routing(Handler\Confirmation\Command::class)->senders(['async_priority_high']);
	
};

