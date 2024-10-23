<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

//use Symfony\Component\HttpKernel\UriSigner;

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
