<?php

namespace App\Controller\Product;

use App\Service\ProductExporter;
use App\Message\ExportProductsMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportProductsController extends AbstractController
{
    #[Route('/products/export', name: 'product_export', methods: ['GET'])]
    public function __invoke(MessageBusInterface $bus): Response
    {
        // $user = $this->getUser();
        // if (!$user || !$user->getEmail()) {
        //     throw $this->createAccessDeniedException("Aucun email associé à votre compte.");
        // }

        $userEmailFake = "test@example.com";

        // Dispatch du message au bus
        $bus->dispatch(new ExportProductsMessage($userEmailFake));

        // Feedback utilisateur
        $this->addFlash('success', 'L’export sera envoyé à votre adresse email sous peu.');

        return $this->redirectToRoute('product_list');
    }
}
