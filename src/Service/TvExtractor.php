<?php
namespace App\Service;

use App\Entity\Categorie;
use App\Entity\Log;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;

class TvExtractor
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function extractTvsFromPages(array $urls): void
    {
        $chromeDriverPath = realpath(__DIR__ . '/../../drivers/chromedriver.exe');
        if (!file_exists($chromeDriverPath)) {
            $this->logToDatabase('error', "ChromeDriver not found at: $chromeDriverPath");
            throw new \RuntimeException("ChromeDriver not found at: $chromeDriverPath");
        }

        $this->logToDatabase('info', "Starting TV extraction using ChromeDriver at: $chromeDriverPath.");
        $client = Client::createChromeClient($chromeDriverPath);

        foreach ($urls as $url) {
            $this->logToDatabase('info', "Extracting TVs from URL: $url.");
            $client->request('GET', $url);
            $crawler = $client->waitFor('div[data-context="line-item"]');

            $crawler->filter('div[data-context="line-item"]')->each(function ($productCrawler) use ($url) {
                try {
                    $title = $productCrawler->filter('strong[data-context="article-name"]')->text('');
                    $description = $productCrawler->filter('span')->eq(0)->text('');
                    $imageLink = $productCrawler->filter('div.SVDv3_rayon_listingProduits_photo img')->attr('src');
                    $productLink = $this->normalizeProductLink($productCrawler->filter('a[aria-label]')->attr('href'));
                    $price = $this->normalizePrice($productCrawler->filter('.SVDv3_zonePrix_prix')->text(''));
                    $salePrice = $this->normalizePrice($productCrawler->filter('.reference-price-crossed .line-through')->text(''));

                    $brand = $this->extractBrand($title);
                    $categoryName = $this->determineCategoryFromUrl($url);
                    $categorie = $this->handleCategory($categoryName);

                    $existingProduct = $this->em->getRepository(Produit::class)->findOneBy(['title' => $title]);
                    if ($existingProduct) {
                        $this->logToDatabase('info', "Product '$title' already exists. Skipping.");
                        return;
                    }

                    $produit = new Produit();
                    $produit->setTitle($title);
                    $produit->setDescription($description);
                    $produit->setCategorie($categorie);
                    $produit->setPrice($price);
                    $produit->setSalePrice($salePrice);
                    $produit->setBrand($brand);
                    $produit->setImageLink($imageLink);
                    $produit->setLink($productLink);

                    $this->em->persist($produit);
                    $this->logToDatabase('info', "Added product '$title'.");
                } catch (\Exception $e) {
                    $this->logToDatabase('error', "Error extracting product: " . $e->getMessage());
                }
            });

            $this->em->flush();
        }

        $this->logToDatabase('info', "TV extraction completed successfully.");
    }

    private function normalizeProductLink(string $productLink): string
    {
        if (!preg_match('/^http/', $productLink)) {
            $productLink = 'https://www.son-video.com' . $productLink;
        }
        return $productLink;
    }

    private function normalizePrice(?string $price): ?float
    {
        if (!$price) {
            return null;
        }
        return (float)str_replace(['€', ',', ' '], ['', '.', ''], $price);
    }

    private function extractBrand(string $title): ?string
    {
        $parts = explode(' ', $title);
        return $parts[0] ?? null;
    }

    private function determineCategoryFromUrl(string $url): string
    {
        if (strpos($url, 'televiseurs-uhd-4k') !== false) {
            return 'télévision>tv>tv led';
        } elseif (strpos($url, 'televiseurs-uhd-8k') !== false) {
            return 'télévision>tv>tv led';
        } elseif (strpos($url, 'televiseurs-oled') !== false) {
            return 'télévision>tv>tv oled';
        } elseif (strpos($url, 'televiseurs-qled') !== false) {
            return 'télévision>tv>tv qled';
        }

        return 'télévision>tv>autre';
    }

    private function handleCategory(string $categoryName): Categorie
    {
        $existingCategory = $this->em->getRepository(Categorie::class)->findOneBy(['nom' => $categoryName]);

        if ($existingCategory) {
            return $existingCategory;
        }

        $newCategory = new Categorie();
        $newCategory->setNom($categoryName);
        $this->em->persist($newCategory);
        $this->em->flush();

        return $newCategory;
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
