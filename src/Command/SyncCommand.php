<?php

declare(strict_types = 1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Services\Sync;

class SyncCommand extends Command
{
    /** @var Sync */
    private $sync;

    public function __construct(Sync $sync)
    {
        $this->sync = $sync;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:sync')
            ->setDescription('Synchronize cities and offices of New Mail.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->sync->run($output);
    }
}
