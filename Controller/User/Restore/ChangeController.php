<?php

namespace BaksDev\Auth\Email\Controller\User\Restore;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordDTO;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordForm;
use BaksDev\Auth\Email\UseCase\User\Change\ChangePasswordHandler;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[AsController]
final class ChangeController extends AbstractController
{
    /** Страница для сброса пароля пользователя */
    #[Route('/change/{event}', name: 'user.change', methods: ['GET', 'POST'])]
    public function reset(
        Request $request,
        ChangePasswordHandler $handler,
        AppCacheInterface $cache,
        string $event
    ): Response
    {
        // Если пользователь авторизован - редирект
        if ($this->getUsr()) {
            throw new RouteNotFoundException('Page Not Found');
        }

        // Получаем идентификатор события пользователя
        $AppCache = $cache->init($event);
        $AccountEvent = ($AppCache->getItem($event))->get();

        if (null === $AccountEvent) {
            throw new RouteNotFoundException('Page Not Found');
        }

        $ChangePasswordDTO = new ChangePasswordDTO(new AccountEventUid($AccountEvent));
        $form = $this->createForm(ChangePasswordForm::class, $ChangePasswordDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('change'))
        {
            $this->refreshTokenForm($form);

            // Сбрасываем сессию после смены пароля
            $AppCache->delete($event);

            $Account = $handler->handle($ChangePasswordDTO);

            if ($Account instanceof Account) {

                // Редирект на страницу после активации аккаунта
                $this->addFlash('success', 'user.success.change', 'user.reset');

                return $this->redirectToRoute('auth-email:user.login');
            }

            $this->addFlash('danger', 'user.danger.change', 'user.reset', $Account);

            return $this->redirectToRoute('auth-email:user.login');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
