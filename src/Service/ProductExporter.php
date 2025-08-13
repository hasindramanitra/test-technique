<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExporter
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getExportStats(): array
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $totalProducts = count($products);
        $outOfStock = 0;
        $lowStock = 0;

        foreach ($products as $product) {
            if ($product->getStock() == 0) {
                $outOfStock++;
            } elseif ($product->getStock() <= 5) {
                $lowStock++;
            }
        }

        return [
            'total_products' => $totalProducts,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock
        ];
    }

    private function sanitizeDescription(?string $desc): string
    {
        if (!$desc) return 'Aucune description';
        $desc = str_replace(["\n", "\r", "\t"], ' ', $desc);
        return strlen($desc) > 100 ? substr($desc, 0, 97) . '...' : $desc;
    }

    private function formatPrice(float $price): string
    {
        return number_format($price, 2, ',', ' ') . ' €';
    }

    private function getStockStatus(int $stock): string
    {
        return $stock === 0 ? 'Rupture' : ($stock <= 5 ? 'Stock faible' : ($stock <= 10 ? 'Stock moyen' : 'Stock élevé'));
    }


    public function exportToCsv(): StreamedResponse
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        $response = new StreamedResponse(function () use ($products) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->setDelimiter(';');

            // En-tête
            $csv->insertOne(['ID', 'Nom', 'Description', 'Prix', 'Stock', 'Statut Stock']);

            // Données
            foreach ($products as $product) {
                $csv->insertOne([
                    $product->getId(),
                    $product->getName(),
                    $this->sanitizeDescription($product->getDescription()),
                    $this->formatPrice($product->getPrice()),
                    $product->getStock(),
                    $this->getStockStatus($product->getStock()),
                ]);
            }

            $csv->output();
        });

        $filename = 'products_export_' . date('Y_m_d_H_i_s') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    public function getExportsList(): array
    {
        $exportDir = __DIR__ . '/../../public/exports/';
        $files = [];

        if (!is_dir($exportDir)) return $files;

        foreach (scandir($exportDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                $filepath = $exportDir . $file;
                $files[] = [
                    'name' => $file,
                    'size' => filesize($filepath),
                    'date' => date('d/m/Y H:i:s', filemtime($filepath)),
                ];
            }
        }

        return $files;
    }

    
}
