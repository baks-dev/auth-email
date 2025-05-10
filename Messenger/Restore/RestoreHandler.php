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

namespace BaksDev\Auth\Email\Messenger\Restore;

use App\Kernel;
use BaksDev\Auth\Email\Repository\UserAccountEvent\UserAccountEventInterface;
use BaksDev\Auth\Email\Security\UrlTokenGenerator;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Cache\AppCacheInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

//use Symfony\Component\HttpKernel\UriSigner;

#[AsMessageHandler]
final class RestoreHandler
{
    private const RESET_ROUTE_NAME = 'auth-email:public.reset';

    private const TEMPLATE = '@auth-email/user/email/restore.html.twig';

    private TranslatorInterface $translator;

    private UriSigner $uriSigner;

    private UrlGeneratorInterface $router;

    private MailerInterface $mailer;

    private ParameterBagInterface $parameters;

    private UrlTokenGenerator $urlTokenGenerator;

    private LocaleSwitcher $localeSwitcher;

    private UserAccountEventInterface $accountEvent;

    private string $HOST;
    private AppCacheInterface $cache;

    public function __construct(
        #[Autowire(env: 'HOST')] string $HOST,
        TranslatorInterface $translator,
        UriSigner $uriSigner,
        UrlGeneratorInterface $router,
        MailerInterface $mailer,
        ParameterBagInterface $parameters,
        UrlTokenGenerator $urlTokenGenerator,
        UserAccountEventInterface $accountEvent,
        LocaleSwitcher $localeSwitcher,
        AppCacheInterface $cache
    )
    {
        $this->translator = $translator;
        $this->uriSigner = $uriSigner;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->parameters = $parameters;
        $this->urlTokenGenerator = $urlTokenGenerator;
        $this->localeSwitcher = $localeSwitcher;
        $this->accountEvent = $accountEvent;
        $this->HOST = $HOST;
        $this->cache = $cache;
    }

    public function __invoke(RestoreAccountMessage $command): void
    {
        if(Kernel::isTestEnvironment())
        {
            return;
        }

        $AppCache = $this->cache->init('auth-email', 300);
        $cacheItem = $AppCache->getItem('restore.'.$command->getEmail());

        if($cacheItem->get())
        {
            return;
        }

        // Получаем по Email пользователя, который не заблокирован
        $Event = $this->accountEvent->getAccountEventNotBlockByEmail($command->getEmail());

        if(!$Event)
        {
            return;
        }

        $this->localeSwitcher->setLocale($command->getLocal());

        // Генерируем токен
        $extraParams['_token'] = $this->urlTokenGenerator->createToken($Event->getAccount(), $Event->getId());
        $extraParams['expires'] = time();
        $extraParams['id'] = (string) $Event->getId();

        // Генерируем ссылку для активации
        $context = $this->router->getContext();
        $context->setHost($this->HOST);
        $context->setScheme('https');
        $uri = $this->router->generate(self::RESET_ROUTE_NAME, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    $this->parameters->get('PROJECT_NO_REPLY'), // email отправителя
                    $this->parameters->get('PROJECT_NAME') // подпись
                )
            )
            ->to(new Address(new AccountEmail($Event->getEmail())))
            ->subject($this->translator->trans('user.subject', domain: 'public.restore'))
            ->htmlTemplate(self::TEMPLATE)
            ->context([
                'signedUrl' => $this->uriSigner->sign($uri),
                'senderName' => $this->parameters->get('PROJECT_NAME'),
            ]);

        // Отправляем письмо пользователю
        $this->mailer->send($email);

        /** Сохраняем в кеш */
        $cacheItem->set($Event);
        $AppCache->save($cacheItem);

    }
}
