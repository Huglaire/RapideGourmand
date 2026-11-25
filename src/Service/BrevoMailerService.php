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
    ) {
    }

    public function sendContactEmail(
        string $title,
        string $description,
        string $senderEmail
    ): void {
        try {
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
                            '<h2>%s</h2><p>%s</p><hr><p>Email du client : %s</p>',
                            htmlspecialchars($title),
                            nl2br(htmlspecialchars($description)),
                            htmlspecialchars($senderEmail)
                        ),
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode < 200 || $statusCode >= 300) {
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