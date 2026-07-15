<?php

namespace App\Controller\Front;

use App\Service\Front\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SecurityController extends AbstractController
{
    #[Route('/signin', name: 'app_signin', methods: ['GET', 'POST'])]
    public function signin(
        Request $request,
        AuthService $authService,
        SessionInterface $session,
    ): Response {

        if ($request->isMethod('POST')) {

            $result = $authService->login(
                $request->request->get('email'),
                $request->request->get('password')
            );
            dd($result);

            if ($result['success']) {

                $session->set('jwt', $result['token']);

                return $this->redirectToRoute('home');
            }

            $this->addFlash(
                'danger',
                $result['message']
            );
        }

        return $this->render('security/signin.html.twig');
    }
}