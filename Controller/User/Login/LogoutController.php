<?php

namespace BaksDev\Auth\Email\Controller\User\Login;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsController]
final class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'user.logout')]
    public function logout(
        Request $request,
        Security $security,
        TokenStorageInterface $tokenStorage,
        GetUserByIdInterface $getUserById,
        AppCacheInterface $cache
    ): Response
    {
        $authority = $this->getUsr()?->getUserIdentifier();

        if($authority)
        {
            /** Удаляем авторизацию пользователя */
            $AppCache = $cache->init('Authority');
            $AppCache->delete($authority);
        }

        if($SwitchUser = $request->getSession()->get('_switch_user'))
        {
            $CurrentUser = $getUserById->get(new UserUid($SwitchUser));

            if($CurrentUser)
            {
                // Олицетворение запрошенного пользователя
                $impersonationToken = new  UsernamePasswordToken(
                    $CurrentUser,
                    "user",
                    $CurrentUser->getRoles()
                );

                $tokenStorage->setToken($impersonationToken);
                $request->getSession()->remove('_switch_user');

               return $this->redirectToReferer();
            }
        }


        $security->logout(false);

        return $this->redirectToRoute('core:user.homepage');
    }

}