<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe/site-infos')]
class EmployeeSiteInfosController extends AbstractController
{
    #[Route('', name: 'employee_site_infos', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render(
            'employee/site-infos/siteInfos.html.twig'
        );
    }
}