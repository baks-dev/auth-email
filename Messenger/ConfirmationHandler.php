<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Auth\Email\Messenger;

use App\Kernel;
use BaksDev\Auth\Email\Repository\UserNew\UserNewInterface;
use BaksDev\Auth\Email\Services\EmailVerify\VerifyEmailInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class ConfirmationHandler
{
    public const VERIFY_ROUTE_NAME = 'auth-email:public.verify.email';

    public const TEMPLATE = '@auth-email/user/email/confirmation.html.twig';

    private UserNewInterface $userVerify;

    private VerifyEmailInterface $emailVerify;

    private ParameterBagInterface $parameters;

    private TranslatorInterface $translator;

    private MailerInterface $mailer;

    //private EntityManagerInterface $entityManager;

    public function __construct(
        UserNewInterface $userVerify,
        VerifyEmailInterface $emailVerify,
        ParameterBagInterface $parameters,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        //EntityManagerInterface $entityManager

    )
    {
        $this->userVerify = $userVerify;
        $this->emailVerify = $emailVerify;
        $this->parameters = $parameters;
        $this->translator = $translator;
        $this->mailer = $mailer;
        //$this->entityManager = $entityManager;
    }

    /** Если пользователь новый или пользователь изменил свой Email - отправляем на указанный Email ссылку для подтверждения */
    public function __invoke(AccountMessage $command): bool
    {
        if(Kernel::isTestEnvironment())
        {
            return false;
        }

        // Получаем UserUid пользователя для верификации по событию со статусом NEW
        $UserUid = $this->userVerify->getNewUserByAccountEvent($command->getEvent());

        if(!$UserUid)
        {
            return false;
        }

        // Создаем письмо для отправки пользователю
        $templatedEmail = new TemplatedEmail();
        $templatedEmail
            ->from(new Address(
                $this->parameters->get('PROJECT_NO_REPLY'),
                $this->parameters->get('PROJECT_NAME')
            ))
            ->to($UserUid->getOption())
            ->subject($this->translator->trans('user.confirm.subject', domain: 'public.confirmation'))
            ->htmlTemplate(self::TEMPLATE);

        $context = $templatedEmail->getContext();

        // Сигнатура письма
        $signature = $this->emailVerify->generateSignature(
            self::VERIFY_ROUTE_NAME,
            $UserUid,
            new AccountEmail($UserUid->getOption()),
            ['id' => $UserUid->getValue()]
        );

        $context['signedUrl'] = $signature->getSignedUrl();
        $context['expiresAtMessageKey'] = $signature->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signature->getExpirationMessageData();

        // Подписи
        $context['senderName'] = $this->parameters->get('PROJECT_NAME');
        $context['senderHomepage'] = $this->parameters->get('PROJECT_HOMEPAGE');

        $templatedEmail->context($context);

        $this->mailer->send($templatedEmail); // отправляем письмо пользователю

        return true;
    }
}
