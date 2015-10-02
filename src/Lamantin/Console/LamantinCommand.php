<?php namespace Lamantin\Console;

use Lamantin\Client;
use Lamantin\Filter;
use Lamantin\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class LamantinCommand extends Command
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * Initial setup
     */
    protected function configure()
    {
        $this
            ->setName('lamantin:start')
            ->setDescription('Запустить Lamantin')
            ->setHelp('Im help, im helping')
            ->addOption(
                'title',
                't',
                InputOption::VALUE_REQUIRED,
                'Название блюда'
            )
            ->addOption(
                'weight',
                'w',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Вес блюда'
            )
            ->addOption(
                'price',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Цена блюда'
            )
            ->addOption(
                'category',
                'cat',
                InputOption::VALUE_REQUIRED,
                'Категория блюда'
            )
            ->addOption(
                'cafe',
                'c',
                InputOption::VALUE_REQUIRED,
                'Название кафе'
            )
        ;

        $this->client = new Client();
        $this->filter = new Filter(new ExpressionLanguage());
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
        $menu = Collection::make($this->client->getFlattenMenu());

        $menu = $menu
            ->transform(function ($entry) {
                $cafe = explode(' ', $entry['cafe']);
                $entry['cafe'] = $cafe[count($cafe) - 1];

                return $entry;
            })
        ;

        $menu = $this->filterMenu($menu, $input->getOptions())
            ->transform(function (array $entry) {
                $entry['weight'] = implode('/', $entry['weight']);

                return $entry;
            })
            ->toArray()
        ;

        $this->displayTable($menu, $output);
    }

    /**
     * @param Collection $menu
     * @param array      $options
     *
     * @return Collection
     */
    private function filterMenu(Collection $menu, array $options)
    {
        $this->filter->setCollection($menu);

        foreach ($options as $option => $value) {
            $method = 'filter' . ucfirst($option);
            if (method_exists($this->filter, $method)) {
                $this->filter->{$method}($value);
            }
        }

        return $this->filter->getCollection();
    }

    /**
     * Result info
     * @param array $menu
     * @param OutputInterface $output
     */
    public function displayTable(array $menu, $output)
    {
        if (count($menu) !== 0) {
            $output->writeln('<info>Найдено следующее:</info>');

            $table = $this->getHelper('table');
            $table->setHeaders(['Название блюда', 'Вес', 'Цена', 'Категория', 'Кафе'])->setRows($menu);
            $table->render($output);
        } else {
            $output->writeln('<danger>Ничего не найдено</danger>');
        }
    }
}