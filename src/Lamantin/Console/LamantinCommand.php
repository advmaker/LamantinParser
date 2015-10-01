<?php namespace Lamantin\Console;

use Lamantin\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Lamantin\Collections\Collection;

class LamantinCommand extends Command
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Initial setup
     */
    protected function configure()
    {
        $this
            ->setName('lamantin:start')
            ->setDescription('Запустить Lamantin')
            ->setHelp('Im help, im helping')
        ;

        $this->client = new Client();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Collection $menu */
        $menu = unserialize(file_get_contents(BASE_DIR . '/menu.file'));

        $filter = $menu->filter(function ($entry) {
            return $entry['price'] <= 50;
        });

        dump($filter);
    }
}