<?php
namespace App\Service;

use App\Entity\Categorie;
use App\Entity\Log;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class ProductExtractor
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function extractProductsFromUrl(string $url): void
    {
        $this->logToDatabase('info', "Starting product extraction from URL: $url");
        $httpClient = HttpClient::create(['timeout' => 120.0]);

        try {
            // Fetch response from the URL
            $response = $httpClient->request('GET', $url);
            $rawContent = $response->getContent(false);

            // Parse CSV content
            $lines = array_filter(explode("\n", $rawContent)); // Remove empty lines
            $headers = str_getcsv(array_shift($lines), ';');

            if (empty($headers)) {
                $this->logToDatabase('error', 'CSV headers are missing or improperly formatted.');
                throw new \Exception('CSV headers are missing or improperly formatted.');
            }

            // Define relevant categories
            $relevantCategories = ['télévision', 'barres de son', 'vidéoprojecteurs'];

            foreach ($lines as $lineNumber => $line) {
                $row = str_getcsv($line, ';');
                if (count($headers) !== count($row)) {
                    $this->logToDatabase('warning', "Skipping line $lineNumber due to mismatched column count.");
                    continue;
                }

                $productData = array_combine($headers, $row);

                // Ensure mandatory fields are present
                if (empty($productData['category']) || empty($productData['title'])) {
                    $this->logToDatabase('warning', "Skipping line $lineNumber due to missing mandatory fields.");
                    continue;
                }

                // Normalize category name
                $categoryName = trim(mb_strtolower($productData['category']));

                // Check if the category contains relevant keywords
                $isRelevantCategory = false;
                foreach ($relevantCategories as $relevant) {
                    if (stripos($categoryName, $relevant) !== false) {
                        $isRelevantCategory = true;
                        break;
                    }
                }

                if (!$isRelevantCategory) {
                    $this->logToDatabase('info', "Skipping product '{$productData['title']}' due to irrelevant category: $categoryName.");
                    continue;
                }

                // Handle `Categorie`
                $categorie = $this->em->getRepository(Categorie::class)
                    ->findOneBy(['nom' => $categoryName]);

                if (!$categorie) {
                    $categorie = new Categorie();
                    $categorie->setNom($categoryName);
                    $this->em->persist($categorie);
                    $this->em->flush(); // Avoid duplicates
                    $this->logToDatabase('info', "Created new category: $categoryName.");
                }

                // Check if the product already exists
                $existingProduct = $this->em->getRepository(Produit::class)->findOneBy(['title' => $productData['title']]);
                if ($existingProduct) {
                    $this->logToDatabase('info', "Product '{$productData['title']}' already exists. Skipping.");
                    continue;
                }

                // Create and populate `Produit`
                $produit = new Produit();
                $produit->setTitle($productData['title']);
                $produit->setDescription($productData['description'] ?? null);
                $produit->setCategorie($categorie);
                $produit->setLink($productData['link'] ?? null);
                $produit->setImageLink($productData['image link'] ?? null);
                $produit->setAdditionalImageLink($productData['additional image link'] ?? null);
                $produit->setProductCondition($productData['condition'] ?? null);
                $produit->setAvailability($productData['availability'] ?? null);
                $produit->setPrice(isset($productData['price']) ? floatval($productData['price']) : null);
                $produit->setSalePrice(isset($productData['sale price']) ? floatval($productData['sale price']) : null);
                $produit->setBrand($productData['brand'] ?? null);
                $produit->setEan($productData['ean'] ?? null);
                $produit->setMpn($productData['mpn'] ?? null);

                $this->em->persist($produit);
                $this->logToDatabase('info', "Added product '{$productData['title']}'.");
            }

            // Persist all changes
            $this->em->flush();
            $this->logToDatabase('info', "Product extraction completed successfully.");

        } catch (\Exception $e) {
            $this->logToDatabase('error', "Error during product extraction: " . $e->getMessage());
            throw new \Exception('Error during product extraction: ' . $e->getMessage());
        }
    }

    private function logToDatabase(string $status, string $message): void
    {
        $log = new Log();
        $log->setStatus($status);
        $log->setMessage($message);
        $log->setCreatedAt(new \DateTime());

        $this->em->persist($log);
        $this->em->flush();
    }
}
