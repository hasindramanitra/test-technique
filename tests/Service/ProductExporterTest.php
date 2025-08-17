<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\ProductExporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExporterTest extends TestCase
{
    /**
     * Teste le calcul des statistiques sur les produits.
     */
    public function testGetExportStats()
    {
        // Produits fictifs
        $product1 = $this->createMock(Product::class);
        $product1->method('getStock')->willReturn(0); // rupture

        $product2 = $this->createMock(Product::class);
        $product2->method('getStock')->willReturn(3); // stock faible

        $product3 = $this->createMock(Product::class);
        $product3->method('getStock')->willReturn(12); // stock élevé

        $products = [$product1, $product2, $product3];

        // Mock du repository
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findAll')->willReturn($products);

        // Mock de l'EntityManager
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Product::class)->willReturn($repo);

        $exporter = new ProductExporter($em);
        $stats = $exporter->getExportStats();

        // Vérification des stats
        $this->assertEquals(3, $stats['total_products']);   
        $this->assertEquals(1, $stats['out_of_stock']);    
        $this->assertEquals(1, $stats['low_stock']);      
    }

    /**
     * Teste la génération du CSV et la réponse StreamedResponse.
     */
    public function testExportToCsvReturnsStreamedResponse()
    {
        // Produit classique
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Produit Test');
        $product->method('getDescription')->willReturn("Une description\navec retour à la ligne");
        $product->method('getPrice')->willReturn(123.45);
        $product->method('getStock')->willReturn(0);

        // Produit avec stock élevé
        $productHighStock = $this->createMock(Product::class);
        $productHighStock->method('getId')->willReturn(2);
        $productHighStock->method('getName')->willReturn('Produit HighStock');
        $productHighStock->method('getDescription')->willReturn('Produit avec stock élevé');
        $productHighStock->method('getPrice')->willReturn(200.0);
        $productHighStock->method('getStock')->willReturn(15);

        // Produit sans description (null)
        $productNoDesc = $this->createMock(Product::class);
        $productNoDesc->method('getId')->willReturn(3);
        $productNoDesc->method('getName')->willReturn('Produit NoDesc');
        $productNoDesc->method('getDescription')->willReturn(null);
        $productNoDesc->method('getPrice')->willReturn(50.0);
        $productNoDesc->method('getStock')->willReturn(5);

        $products = [$product, $productHighStock, $productNoDesc];

        // Mock repository
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findAll')->willReturn($products);

        // Mock EntityManager
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Product::class)->willReturn($repo);

        $exporter = new ProductExporter($em);
        $response = $exporter->exportToCsv();

        // Vérifie que c'est bien un StreamedResponse
        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Capture le contenu CSV
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // Vérifie la présence des en-têtes
        $this->assertStringContainsString('ID;', $csvContent);
        $this->assertStringContainsString('Nom;', $csvContent);
        $this->assertStringContainsString('Description;', $csvContent);
        $this->assertStringContainsString('Prix;', $csvContent);
        $this->assertStringContainsString('Stock;', $csvContent);
        $this->assertStringContainsString('Statut Stock', $csvContent);

        // Vérifie le produit classique
        $this->assertStringContainsString('Produit Test', $csvContent);
        $this->assertStringContainsString('Une description avec retour à la ligne', $csvContent);
        $this->assertStringContainsString('123,45 €', $csvContent);
        $this->assertStringContainsString('0;', $csvContent);
        $this->assertStringContainsString('Rupture', $csvContent);

        // Vérifie le produit à stock élevé
        $this->assertStringContainsString('Produit HighStock', $csvContent);
        $this->assertStringContainsString('Stock élevé', $csvContent);

        // Vérifie le produit sans description
        $this->assertStringContainsString('Produit NoDesc', $csvContent);
        $this->assertStringContainsString('Aucune description', $csvContent); // sanitizeDescription gère null
    }
}
