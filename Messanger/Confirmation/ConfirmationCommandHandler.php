<?php

namespace BaksDev\Auth\Email\Messanger\Confirmation;

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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ConfirmationCommandHandler implements MessageHandlerInterface
{

    public const VERIFY_ROUTE_NAME = 'AuthEmail:user.verify.email';

    private UserNewByAccountEventInterface $userNewByAccountEvent;
    private TranslatorInterface $translator;
    private UriSigner $uriSigner;
    private UrlGeneratorInterface $router;
    private MailerInterface $mailer;
    private ParameterBagInterface $parameter;
    private UrlTokenGenerator $urlTokenGenerator;

    public function __construct(
        UserNewByAccountEventInterface $userNewByAccountEvent,
        TranslatorInterface            $translator,
        UriSigner                      $uriSigner,
        UrlGeneratorInterface          $router,
        MailerInterface $mailer,
        ParameterBagInterface $parameter,
        UrlTokenGenerator $urlTokenGenerator
    )
    {
        $this->userNewByAccountEvent = $userNewByAccountEvent;
        $this->translator = $translator;
        $this->uriSigner = $uriSigner;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->parameter = $parameter;
        $this->urlTokenGenerator = $urlTokenGenerator;
    }

    public function __invoke(ConfirmationCommand $command): bool
    {
        /* Получаем UserUid пользователя для верификации по событию со статусом NEW */
        $UserUid = $this->userNewByAccountEvent->get($command->getEvent());

        if (!$UserUid) {
            return false;
        }

        /* Генерируем токен */
        $extraParams['token'] = $this->urlTokenGenerator->createToken($UserUid, $command->getEvent());
        $extraParams['id'] = $command->getEvent();
        $extraParams['exp'] = time();

        /* Генерируем ссылку для активации */
        $uri = $this->router->generate(self::VERIFY_ROUTE_NAME, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from(new Address
            (
                $this->parameter->get('PROJECT_NO_REPLY'), /* email отправителя */
                $this->parameter->get('PROJECT_NAME') /* подпись */
            ))
            ->to(new Address(new AccountEmail($UserUid->getName())))
            ->subject($this->translator->trans('user.subject', domain: 'user.confirmation'))
            ->htmlTemplate('@AuthEmail/default/user/email/confirmation.html.twig')
            ->context([
                'signedUrl' => $this->uriSigner->sign($uri),
                'senderName' => $this->parameter->get('PROJECT_NAME')
            ]);

        /* Отправляем письмо пользователю */
        $this->mailer->send($email);

        return true;
    }



}

