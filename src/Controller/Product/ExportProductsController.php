<?php

namespace App\Controller\Product;

use App\Service\ProductExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExportProductsController extends AbstractController
{
    #[Route('/products/export', name: 'product_export', methods: ['GET'])]
    public function __invoke(ProductExporter $productExporter): Response
    {
        try {
            // Récupérer les statistiques pour les flash messages
            $stats = $productExporter->getExportStats();

            $this->addFlash('success', sprintf(
                'Export réussi ! %d produits exportés',
                $stats['total_products']
            ));

            if ($stats['out_of_stock'] > 0) {
                $this->addFlash('warning', sprintf(
                    '%d produit(s) en rupture de stock détecté(s)',
                    $stats['out_of_stock']
                ));
            }

            if ($stats['low_stock'] > 0) {
                $this->addFlash('info', sprintf(
                    '%d produit(s) en stock faible détecté(s)',
                    $stats['low_stock']
                ));
            }

            // Télécharger le CSV
            return $productExporter->exportToCsv();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'export : ' . $e->getMessage());
            return $this->redirectToRoute('product_list');
        }
    }
}
