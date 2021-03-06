<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\EntityConverter;
use App\Entity\User;
use App\Enum\NormalizationGroup;
use App\Manager\UserManager;
use App\Utils\StringHelper;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityController extends Controller
{
    /**
     * @Route("/api/user_status", name="api_user_status", methods={"GET"}, options={"expose":true})
     *
     * @throws \ReflectionException
     */
    public function loginStatus() : JsonResponse
    {
        /** @var User $user */
        if ($user = $this->getCurrentUser()) {
            return $this->jsonSuccess(
                $this->entitySerializer->normalize($user, 'array', [
                    'groups' => NormalizationGroup::UserData
                ])
            );
        }

        return $this->jsonError();
    }

    /**
     * @Route("/api/login_check", name="api_login_check", methods={"POST"}, options={"expose":true})
     *
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function loginCheck(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        UserManager $userManager
    ): JsonResponse {
        try {
            $username = $request->get('username');
            $password = $request->get('password');

            /** @var User $user */
            if ($user = $user = $userManager->getByUsernameOrEmail($username)) {
                if ($encoder->isPasswordValid($user, $password)) {
                    $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
                    $event = new InteractiveLoginEvent($request, $token);

                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

                    $csrfToken = $this->get('security.csrf.token_manager')->getToken($user->getTokenId())->getValue();

                    $userManager->afterLogin($user, $csrfToken);

                    return $this->jsonSuccess([
                        'user' => $this->entitySerializer->normalize($user, 'array', [
                            'groups' => NormalizationGroup::UserData
                        ])
                    ]);
                } else {
                    return $this->jsonError(
                        $this->translate('label.error.login.invalid_password')
                    );
                }
            } else {
                return $this->jsonError(
                    $this->translate('label.error.login.user_not_found')
                );
            }
        } catch (\Exception $e) {
            return $this->jsonError(
                $e->getMessage()
            );
        }
    }

    /**
     * @Route("/api/logout_user", name="api_logout", methods={"GET"}, options={"expose":true})
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $manager = $this->get(UserManager::class);
            $user = $this->getCurrentUser();

            $user->setApiToken(StringHelper::randomStr(64));
            $manager->save($user);

            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();

            return $this->jsonSuccess();
        } catch (\Exception $e) {
            return $this->jsonError(
                $e->getMessage()
            );
        }
    }
}