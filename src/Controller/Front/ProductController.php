<?php
namespace App\Controller\Front;

use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route("/", name: "home")]
    public function home(): Response
    {
        return $this->redirectToRoute('product_list');
    }

    #[Route("/products", name: "product_list")]
    public function index(): Response
    {
        // Fetch products from the database
        $produits = $this->doctrine->getRepository(Produit::class)->findAll();

        // Pass products to the template
        return $this->render('front/product/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route("/produit/{id}", name: "product_show")]
    public function show(Produit $produit): Response
    {
        return $this->render('front/product/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}
