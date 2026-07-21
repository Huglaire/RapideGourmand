<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\SiteInfosService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
        MailerInterface $mailer,
        SiteInfosService $siteInfosService
    ): Response {

        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = (new TemplatedEmail())

                ->from(
                    new Address(
                        'pollon.hugo@gmail.com',
                        'Rapide & Gourmand'
                    )
                )

                ->replyTo(
                    $data['email']
                )

                ->to(
                    $siteInfosService->get('contact_email')
                )

                ->subject(
                    $data['title']
                )

                ->htmlTemplate(
                    'emails/contact.html.twig'
                )

                ->context([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'senderEmail' => $data['email'],
                ]);

            try {

                $mailer->send($email);

                $this->addFlash(
                    'success',
                    'Votre message a bien été envoyé.'
                );
            } catch (TransportExceptionInterface) {

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
            ]
        );
    }
}
