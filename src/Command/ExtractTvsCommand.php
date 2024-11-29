<?php

namespace App\Command;

use App\Service\TvExtractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:extract-tvs',
    description: 'Extract TVs from Son-Video and store them in the database.'
)]
class ExtractTvsCommand extends Command
{
    private TvExtractor $tvExtractor;

    public function __construct(TvExtractor $tvExtractor)
    {
        parent::__construct();
        $this->tvExtractor = $tvExtractor;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting TV extraction...</info>');

        try {
            $urls = [
                'https://www.son-video.com/rayon/television/televiseurs/televiseurs-uhd-4k',
                'https://www.son-video.com/rayon/television/televiseurs/tv-uhd-8k',
                'https://www.son-video.com/rayon/television/televiseurs/televiseurs-oled',
                'https://www.son-video.com/rayon/television/televiseurs/televiseurs-qled',
            ];

            $this->tvExtractor->extractTvsFromPages($urls);
            $output->writeln('<info>TV extraction completed successfully!</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error occurred during TV extraction: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
