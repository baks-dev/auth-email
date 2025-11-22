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

namespace BaksDev\Auth\Email\Security;

use BaksDev\Auth\Email\Controller\Public\Login\LoginController;
use BaksDev\Auth\Email\Repository\AccountEventActiveByEmail\AccountEventActiveByEmailInterface;
use BaksDev\Auth\Email\UseCase\User\Login\LoginDTO;
use BaksDev\Auth\Email\UseCase\User\Login\LoginForm;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\Public\HomepageController;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserEmailAuthenticator extends AbstractAuthenticator
{
    private UrlGeneratorInterface $urlGenerator;

    private FormFactoryInterface $form;

    private TranslatorInterface $translator;

    private AccountEventActiveByEmailInterface $accountEventActiveByEmail;

    private UserPasswordHasherInterface $passwordHasher;

    private GetUserByIdInterface $userById;

    private AppCacheInterface $cache;


    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        FormFactoryInterface $form,
        AccountEventActiveByEmailInterface $accountEventActiveByEmail,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $passwordHasher,
        GetUserByIdInterface $userById,
        AppCacheInterface $cache
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->form = $form;
        $this->translator = $translator;
        $this->accountEventActiveByEmail = $accountEventActiveByEmail;
        $this->passwordHasher = $passwordHasher;
        $this->userById = $userById;
        $this->cache = $cache;
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl() === $request->getPathInfo();
    }


    public function authenticate(Request $request): Passport
    {
        $LoginDTO = new LoginDTO();
        $form = $this->form->create(LoginForm::class, $LoginDTO);
        $form->handleRequest($request);

        /** Получаем паспорт */
        return new SelfValidatingPassport(
            new UserBadge($LoginDTO->getEmail(), function() use ($LoginDTO, $form, $request) {

                if($form->isSubmitted() && $form->isValid())
                {
                    /* Получаем активный аккаунт по Email */
                    $account = $this->accountEventActiveByEmail->getAccountEvent($LoginDTO->getEmail());

                    if($account === null)
                    {
                        return null;
                    }

                    /* Проверяем пароль */
                    $passValid = $this->passwordHasher->isPasswordValid($account, $LoginDTO->getPassword());

                    if($passValid === false)
                    {
                        return null;
                    }


                    /* Сбрасываем кеш ролей пользователя */
                    $cache = $this->cache->init('UserGroup');
                    $cache->clear();

                    /** Удаляем авторизацию доверенности пользователя */
                    $Session = $request->getSession();
                    $Session->remove('Authority');

                    //$Authority = $this->cache->init('Authority');
                    //$Authority->delete((string) $account->getAccount());

                    return $this->userById->get($account->getAccount());
                }

                return null;
            }),

            badges: [
                new CsrfTokenBadge('authenticate', ($request->get('login_form')['_token'] ?? ''))
                //new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /* если форма отправлена AJAX */
        if($request->isXmlHttpRequest())
        {
            // редирект на страницу refresh
            return new JsonResponse(['status' => 302, 'redirect' => '/refresh']);
        }

        if($targetPath = $request->getSession()->get('_security.'.$firewallName.'.target_path'))
        {
            return new RedirectResponse($targetPath);
        }

        /* Редирект на главную страницу после успешной авторизации */
        return new RedirectResponse($this->urlGenerator->generate('core:'.HomepageController::HOMEPAGE));
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if($request->isXmlHttpRequest())
        {
            return new JsonResponse(
                [
                    'header' => $this->translator->trans(
                        'page',
                        domain: 'public.login'
                    ),
                    'message' => $this->translator->trans(
                        'login.error.message',
                        domain: 'public.login'
                    ),
                ]
                , 401
            );
        }

        if($request->hasSession())
        {
            $request->getSession()->getFlashBag()->add(
                $this->translator->trans('login.error.header', domain: 'public.login'),
                $this->translator->trans('login.error.message', domain: 'public.login'),
            );
        }

        return new RedirectResponse($this->getLoginUrl());
    }


    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate('auth-email:'.LoginController::LOGIN_ROUTE);
    }

}