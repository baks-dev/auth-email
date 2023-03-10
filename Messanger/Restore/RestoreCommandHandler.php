<?php

namespace BaksDev\Auth\Email\Messanger\Restore;

use BaksDev\Auth\Email\Repository\AccountEventNotBlockByEmail\AccountEventNotBlockByEmailInterface;
use BaksDev\Auth\Email\Repository\UserNewByAccountEvent\UserNewByAccountEventInterface;

//use BaksDev\Users\AuthEmail\Account\Type\Email\AccountEmail;
//use BaksDev\Users\AuthEmail\Auth\Repository\UserVerify\UserVerifyInterface;
//use BaksDev\Users\AuthEmail\Auth\Services\EmailVerify\VerifyEmailInterface;
use BaksDev\Auth\Email\Security\UrlTokenGenerator;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RestoreCommandHandler implements MessageHandlerInterface
{
	public const RESET_ROUTE_NAME = 'AuthEmail:user.reset';
	
	private TranslatorInterface $translator;
	
	private UriSigner $uriSigner;
	
	private UrlGeneratorInterface $router;
	
	private MailerInterface $mailer;
	
	private ParameterBagInterface $parameter;
	
	private UrlTokenGenerator $urlTokenGenerator;
	
	private AccountEventNotBlockByEmailInterface $accountEventNotBlockByEmail;
	
	
	public function __construct(
		TranslatorInterface $translator,
		UriSigner $uriSigner,
		UrlGeneratorInterface $router,
		MailerInterface $mailer,
		ParameterBagInterface $parameter,
		UrlTokenGenerator $urlTokenGenerator,
		AccountEventNotBlockByEmailInterface $accountEventNotBlockByEmail,
	)
	{
		$this->translator = $translator;
		$this->uriSigner = $uriSigner;
		$this->router = $router;
		$this->mailer = $mailer;
		$this->parameter = $parameter;
		$this->urlTokenGenerator = $urlTokenGenerator;
		$this->accountEventNotBlockByEmail = $accountEventNotBlockByEmail;
	}
	
	
	public function __invoke(RestoreCommand $command) : bool
	{
		$cache = new ApcuAdapter();
		
		$data = $cache->get('restore.'.$command->getEmail(), function(ItemInterface $item) use ($command) {
			/* ?????????? ???????????????????? 300 = 5 ?????????? */
			$item->expiresAfter(300);
			
			/* ???????????????? ???? Email ????????????????????????, ?????????????? ???? ???????????????????????? */
			$Event = $this->accountEventNotBlockByEmail->get($command->getEmail());
			
			if(!$Event)
			{
				return false;
			}
			
			/* ???????????????????? ?????????? */
			$extraParams['token'] = $this->urlTokenGenerator->createToken($Event->getAccount(), $Event->getId());
			$extraParams['id'] = $Event->getId();
			$extraParams['exp'] = time();
			
			/* ???????????????????? ???????????? ?????? ?????????????????? */
			$uri = $this->router->generate(self::RESET_ROUTE_NAME, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);
			
			$email = (new TemplatedEmail())
				->from(new Address
				(
					$this->parameter->get('PROJECT_NO_REPLY'), /* email ?????????????????????? */
					$this->parameter->get('PROJECT_NAME') /* ?????????????? */
				)
				)
				->to(new Address(new AccountEmail($Event->getEmail())))
				->subject($this->translator->trans('user.subject', domain: 'user.restore'))
				->htmlTemplate('@AuthEmail/default/user/email/restore.html.twig')
				->context([
					'signedUrl' => $this->uriSigner->sign($uri),
					'senderName' => $this->parameter->get('PROJECT_NAME'),
				])
			;
			
			/* ???????????????????? ???????????? ???????????????????????? */
			$this->mailer->send($email);
			
			return true;
		});
		
		return $data;
	}
	
}

