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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Auth\Email\BaksDevAuthEmailBundle;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Email\AccountEmailType;
use BaksDev\Auth\Email\Type\Event\AccountEventType;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\Type\Settings\AccountSettingsIdentifier;
use BaksDev\Auth\Email\Type\Settings\AccountSettingsType;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatusType;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine) {


    $doctrine->dbal()->type(AccountEventUid::TYPE)->class(AccountEventType::class);
    $doctrine->dbal()->type(AccountEmail::TYPE)->class(AccountEmailType::class);
    $doctrine->dbal()->type(EmailStatus::TYPE)->class(EmailStatusType::class);
    $doctrine->dbal()->type(AccountSettingsIdentifier::TYPE)->class(AccountSettingsType::class);


    /** Резолверы */
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AccountEventUid::class)->class(AccountEventUid::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $emDefault->mapping('auth-email')
        ->type('attribute')
        ->dir(BaksDevAuthEmailBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix('BaksDev\Auth\Email\Entity')
        ->alias('auth-email');
};