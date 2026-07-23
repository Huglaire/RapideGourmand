<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }


    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): Response {

        $data = json_decode(
            $request->getContent(),
            true
        );


        $email = $data['email'] ?? null;


        if ($email) {

            $user = $this->userRepository->findOneBy([
                'email' => $email
            ]);


            if ($user && !$user->isActive()) {

                return new JsonResponse(
                    [
                        'code' => 'ACCOUNT_DISABLED',
                        'message' => 'Votre compte est désactivé.',
                        'canRestore' => true
                    ],
                    Response::HTTP_UNAUTHORIZED
                );

            }

        }


        return new JsonResponse(
            [
                'message' => 'Identifiants invalides.'
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}