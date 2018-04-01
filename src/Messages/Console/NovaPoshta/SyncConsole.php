<?php

namespace App\Messages\Console\NovaPoshta;


use Symfony\Component\Console\Output\OutputInterface;

class SyncConsole
{

    /** @var string $dateFormat */
    private $dateFormat = 'H:i:s d.m.Y';

    /** @var OutputInterface */
    private $output;

    public function init($output)
    {
        $this->output = $output;

        return $this;
    }

    public function syncLoadData(): void
    {
        $this->output->writeln('Loading data from api...');
    }

    public function syncStart(): void
    {
        $this->output->writeln('<fg=green>Synchronization started at</> ' . date($this->dateFormat));
    }

    public function syncComplete(): void
    {
        $this->output->writeln('<fg=green>Synchronization finished at</> ' . date($this->dateFormat));
    }

    public function syncCheckDifferenceCities()
    {
        $this->output->writeln('Checking count for remove cities...');
    }

    public function syncCheckDifferenceWarehouses()
    {
        $this->output->writeln('Checking count for remove warehouses...');
    }

    public function syncFailed(): void
    {
        $this->output->writeln('<fg=red>Synchronization failed at</> ' . date($this->dateFormat));
    }

    public function syncCitiesStart(): void
    {
        $this->output->writeln('Sync cities...');
    }

    public function synCitiesComplete(): void
    {
        $this->output->writeln('<fg=green>Sync cities complete...</>');
    }

    public function syncWarehousesStart(): void
    {
        $this->output->writeln('Sync warehouses...');
    }

    public function syncWarehousesComplete(): void
    {
        $this->output->writeln('<fg=green>Sync warehouses complete...</>');
    }

    public function customMessage($message)
    {
        $this->output->writeln($message);
    }
}
