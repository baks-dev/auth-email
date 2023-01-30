<?php

namespace BaksDev\Auth\Email\Type\Status;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AccountStatusExtension extends AbstractExtension
{
	public function getFunctions() : array
	{
		return [
			new TwigFunction(AccountStatus::TYPE,
				[$this, 'status'],
				['needs_environment' => true, 'is_safe' => ['html']]
			),
		];
	}
	
	
	public function status(Environment $twig, ?string $status) : string
	{
		return $twig->render('@AccountStatus/status.html.twig', ['status' => $status]);
	}
	
}