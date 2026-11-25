<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoMailerService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $brevoApiKey,
        private LoggerInterface $logger
    ) {}

    public function sendContactEmail(
        string $title,
        string $description,
        string $senderEmail
    ): void {

        // Diagnostic de la clé API sans révéler sa valeur
        $this->logger->info('Diagnostic authentification Brevo', [
            'key_length' => strlen(trim($this->brevoApiKey)),
            'key_fingerprint' => substr(
                hash('sha256', trim($this->brevoApiKey)),
                0,
                12
            ),
        ]);

        try {

            $response = $this->client->request(
                'POST',
                'https://api.brevo.com/v3/smtp/email',
                [
                    'headers' => [
                        'api-key' => trim($this->brevoApiKey),
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

            // Récupération de la réponse sans déclencher
            // automatiquement une exception HTTP
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode < 200 || $statusCode >= 300) {

                $this->logger->error(
                    'Brevo HTTP ' . $statusCode . ' : ' . substr($content, 0, 1000)
                );

                throw new \RuntimeException(sprintf(
                    'Brevo a retourné HTTP %d : %s',
                    $statusCode,
                    substr($content, 0, 1000)
                ));
            }

            $this->logger->info('Brevo a accepté la demande d’envoi.', [
                'http_status' => $statusCode,
            ]);
        } catch (\Throwable $exception) {

            $this->logger->error('Échec de la requête vers Brevo.', [
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
