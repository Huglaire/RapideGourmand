<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test-env', name: 'test_env')]
    public function testEnv(): JsonResponse
    {
        return new JsonResponse([
            'JWT_PRIVATE_KEY' => getenv('JWT_PRIVATE_KEY') ? 'OK' : 'MISSING',
            'JWT_PUBLIC_KEY_CONTENT' => getenv('JWT_PUBLIC_KEY_CONTENT') ? 'OK' : 'MISSING',
            'JWT_PASSPHRASE' => getenv('JWT_PASSPHRASE') ? 'OK' : 'MISSING',
        ]);
    }
}