<?php
namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ProductExportMailer
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    public function sendExport(string $email, string $csvContent): void
    {
        $emailMessage = (new Email())
            ->from("test-technique@gmail.com")
            ->to($email)
            ->subject("Export de vos produits")
            ->text("Voici le fichier contenant vos produits")
            ->attach($csvContent, "export.csv", "text/csv");

        $this->mailer->send($emailMessage);
    }
}