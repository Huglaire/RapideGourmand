<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\BrevoMailerService;
use App\Service\SiteInfosService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route(
        '/contact',
        name: 'contact',
        methods: ['GET', 'POST']
    )]
    public function index(
        Request $request,
        BrevoMailerService $brevoMailerService,
        SiteInfosService $siteInfosService
    ): Response {

        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();


            try {

                $brevoMailerService->sendContactEmail(
                    $data['title'],
                    $data['description'],
                    $data['email']
                );


                $this->addFlash(
                    'success',
                    'Votre message a bien été envoyé.'
                );


            } catch (\Throwable $exception) {

                $this->addFlash(
                    'danger',
                    "Une erreur est survenue lors de l'envoi du message."
                );
            }


            return $this->redirectToRoute('contact');
        }


        return $this->render(
            'front/contact/contact.html.twig',
            [
                'form' => $form,
                'contactEmail' => $siteInfosService->get('contact_email'),
            ]
        );
    }
}