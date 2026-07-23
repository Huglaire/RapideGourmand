<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoMailerService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $brevoApiKey
    ) {
    }


    public function sendContactEmail(
        string $title,
        string $description,
        string $senderEmail
    ): void {

        $response = $this->client->request(
            'POST',
            'https://api.brevo.com/v3/smtp/email',
            [
                'headers' => [
                    'api-key' => $this->brevoApiKey,
                    'Content-Type' => 'application/json',
                ],

                'json' => [

                    'sender' => [
                        'name' => 'Rapide & Gourmand',
                        'email' => 'pollon.hugo@gmail.com',
                    ],

                    'to' => [
                        [
                            'email' => 'pollon.hugo@gmail.com',
                        ]
                    ],

                    'subject' => $title,

                    'htmlContent' => sprintf(
                        '
                        <h2>%s</h2>

                        <p>%s</p>

                        <hr>

                        <p>Email du client : %s</p>
                        ',
                        htmlspecialchars($title),
                        nl2br(htmlspecialchars($description)),
                        htmlspecialchars($senderEmail)
                    ),
                ],
            ]
        );


        $response->getContent();
    }
}