<?php

namespace App\Command;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:add-test-data')]
class AddTestDataCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categorie = new Categorie();
        $categorie->setNom('Télévision');
        $this->em->persist($categorie);

        $produit = new Produit();
        $produit->setNom('Test Product');
        $produit->setDescription('This is a test product');
        $produit->setCategorie($categorie);
        $produit->setTailleEcran(55.0);
        $produit->setTechnologie('OLED');
        $produit->setIs4k(true);
        $produit->setIs8k(false);
        $this->em->persist($produit);

        $this->em->flush();

        $output->writeln('Test data inserted successfully!');
        return Command::SUCCESS;
    }
}
