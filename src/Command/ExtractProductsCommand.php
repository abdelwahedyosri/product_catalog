<?php

namespace App\Command;

use App\Service\ProductExtractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:extract-products',
    description: 'Extract products from the vendor catalog and save them to the database',
)]
class ExtractProductsCommand extends Command
{
    private ProductExtractor $productExtractor;

    public function __construct(ProductExtractor $productExtractor)
    {
        $this->productExtractor = $productExtractor;
        parent::__construct();
    }

    protected function configure(): void
    {
        // No arguments or options needed for this command
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $url = 'https://export.beezup.com/Son_Videocom/Affilae_FRA/ec84d19f-30c0-5102-958d-720856077d4e';
        $io->title('Starting product extraction...');
        try {
            $this->productExtractor->extractProductsFromUrl($url);
            $io->success('Products and offers extracted successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred during product extraction: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
