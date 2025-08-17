<?php
namespace App\MessageHandler;

use App\Message\ExportProductsMessage;
use App\Service\ProductExporter;
use App\Service\ProductExportMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class ExportProductsMessageHandler
{

    private ProductExporter $productExporter;

    private ProductExportMailer $mailer;

    public function __construct(
        ProductExporter $productExporter,
        ProductExportMailer $mailer
    )
    {
        $this->productExporter = $productExporter;
        $this->mailer = $mailer;
    }


    public function __invoke(ExportProductsMessage $message): void
    {
        // Génération du CSV en mémoire via StreamedResponse
        ob_start();
        $response = $this->productExporter->exportToCsv();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // Envoi de l’email avec la pièce jointe
        $this->mailer->sendExport($message->getEmail(), $csvContent);
    }
}