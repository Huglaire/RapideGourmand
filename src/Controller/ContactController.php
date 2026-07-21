<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    #[Route(
        '/contact',
        name: 'contact',
        methods: ['GET', 'POST']
    )]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        // L'envoi du mail sera ajouté à l'étape suivante.
        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'success',
                'Votre demande a bien été enregistrée.'
            );

            return $this->redirectToRoute('contact');
        }

        return $this->render(
            'front/contact/contact.html.twig',
            [
                'form' => $form,
            ]
        );
    }
}
