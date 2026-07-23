<?php

namespace App\Service;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;
use GuzzleHttp\Client;

class BrevoMailerService
{
    private TransactionalEmailsApi $api;


    public function __construct(
        string $brevoApiKey
    ) {

        $this->api = new TransactionalEmailsApi(
            new Client(),
            [
                'api-key' => $brevoApiKey
            ]
        );
    }


    public function sendContactEmail(
        string $title,
        string $description,
        string $senderEmail
    ): void {

        $email = new SendSmtpEmail();


        $email->setSender(
            new SendSmtpEmailSender([
                'email' => 'pollon.hugo@gmail.com',
                'name' => 'Rapide & Gourmand',
            ])
        );


        $email->setTo([
            new SendSmtpEmailTo([
                'email' => 'pollon.hugo@gmail.com',
            ])
        ]);


        $email->setSubject(
            $title
        );


        $email->setHtmlContent(
            sprintf(
                '
                <h2>%s</h2>

                <p>%s</p>

                <hr>

                <p>Email du client : %s</p>
                ',
                htmlspecialchars($title),
                nl2br(htmlspecialchars($description)),
                htmlspecialchars($senderEmail)
            )
        );


        $this->api->sendTransacEmail($email);
    }
}