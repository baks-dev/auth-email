<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\Services\EmailVerify;

use BaksDev\Auth\Email\Services\EmailVerify\Exception\ExpiredSignatureException;
use BaksDev\Auth\Email\Services\EmailVerify\Exception\InvalidSignatureException;
use BaksDev\Auth\Email\Services\EmailVerify\Exception\WrongEmailVerifyException;
use BaksDev\Auth\Email\Services\EmailVerify\Generator\VerifyEmailTokenGenerator;
use BaksDev\Auth\Email\Services\EmailVerify\Model\VerifyEmailSignatureComponents;
use BaksDev\Auth\Email\Services\EmailVerify\Util\VerifyEmailQueryUtility;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
//use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class VerifyEmail implements VerifyEmailInterface
{

    private int $lifetime;
    private RouterInterface $router;
    private UriSigner $uriSigner;
    private VerifyEmailQueryUtility $queryUtility;
    private VerifyEmailTokenGenerator $tokenGenerator;
    private string $HOST;


    public function __construct(
        #[Autowire(env: 'HOST')] string $HOST,
        RouterInterface $router,
        UriSigner $uriSigner,
        VerifyEmailQueryUtility $queryUtility,
        VerifyEmailTokenGenerator $tokenGenerator,
        int $lifetime = 3600,
    )
    {
        $this->router = $router;
        $this->uriSigner = $uriSigner;
        $this->queryUtility = $queryUtility;
        $this->tokenGenerator = $tokenGenerator;
        $this->lifetime = $lifetime;
        $this->HOST = $HOST;
    }

    public function generateSignature(
        string $routeName,
        UserUid $userId,
        AccountEmail $userEmail,
        array $extraParams = []
    ): VerifyEmailSignatureComponents
    {

        $context = $this->router->getContext();
        $context->setHost($this->HOST);
        $context->setScheme('https');

        $generatedAt = time();
        $expiryTimestamp = $generatedAt + $this->lifetime;

        $extraParams['token'] = $this->tokenGenerator->createToken($userId, $userEmail);
        $extraParams['expires'] = $expiryTimestamp;

        $uri = $this->router->generate($routeName, $extraParams, UrlGeneratorInterface::ABSOLUTE_URL);

        $signature = $this->uriSigner->sign($uri);

        return new VerifyEmailSignatureComponents(
            DateTimeImmutable::createFromFormat('U', (string) $expiryTimestamp),
            $signature,
            $generatedAt
        );
    }

    public function validateEmailConfirmation(
        string $signedUrl,
        UserUid $userId,
        AccountEmail $userEmail
    ): void
    {
        if(!$this->uriSigner->check($signedUrl))
        {
            throw new InvalidSignatureException();
        }

        if($this->queryUtility->getExpiryTimestamp($signedUrl) <= time())
        {
            throw new ExpiredSignatureException();
        }

        $knownToken = $this->tokenGenerator->createToken($userId, $userEmail);
        $userToken = $this->queryUtility->getTokenFromQuery($signedUrl);

        if(!hash_equals($knownToken, $userToken))
        {
            throw new WrongEmailVerifyException();
        }
    }

}
