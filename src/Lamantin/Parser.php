<?php namespace Lamantin;

use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    const BASE_URL = 'http://lamantin-kafe.ru';

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var array
     */
    private $data;

    /**
     * @param $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function parse()
    {
        $cafe_map = $this->parseCafeList(file_get_contents(self::BASE_URL));
        $menu_map = $this->parseAllCafeMenu($cafe_map);

        $this->data = $menu_map;

        return $this;
    }

    public function flatten()
    {
        $result = [];
        $date = new \DateTime('today');

        if (count($this->data) === 0) {
            return $result;
        }

        foreach ($this->data as $cafe => $menu) {
            foreach ((array) $menu as $category => $meal_list) {
                foreach ((array) $meal_list as $meal) {
                    $result[] = array_merge($meal, compact('cafe', 'category', 'date'));
                }
            }
        }

        $this->data = $result;

        return $this;
    }

    public function get()
    {
        return $this->data;
    }

    /**
     * @param string $html
     */
    private function setHtml($html)
    {
        $this->crawler->clear();
        $this->crawler->addHtmlContent($html);
    }

    /**
     * Парсит меню для всех кафе в массиве
     *
     * @param array $cafeList
     *
     * @return array
     */
    private function parseAllCafeMenu(array $cafeList)
    {
        $cafes_menu = [];

        foreach ($cafeList as $title => $info_url) {
            try {
                $menu_url = $this->parseLinkToMenu(file_get_contents($info_url));
                $menu = $this->parseCafeMenu(file_get_contents($menu_url));

                $cafes_menu[$title] = $menu;
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        return $cafes_menu;
    }

    /**
     * Парсит меню кафе в формате ['категория блюда' => [['название блюда', 'вес', 'цена'], ...]]
     *
     * @param string $html
     *
     * @return array
     */
    private function parseCafeMenu($html)
    {
        $this->setHtml($html);

        $menu = [];
        $category = null;

        $this->crawler
            ->filter('body #content .entry-content table tbody tr')
            ->reduce(function (Crawler $node) {
                return $node->filter('td')->count() === 3;
            })
            ->slice(1)
            ->each(function (Crawler $node) use (&$menu, &$category) {
                $td_nodes = $node->filter('td');
                $title = $this->normaliseTitle($td_nodes->eq(0));
                $weight = $this->normaliseWeight($td_nodes->eq(1));
                $price = $this->normalisePrice($td_nodes->eq(2));

                if ($price === 0.0 && count($weight) === 0) {
                    $category = $this->normaliseCategory($title);
                    return;
                }

                $menu[$category][] = compact('title', 'weight', 'price');
            })
        ;

        return $menu;
    }

    /**
     * Парсит ссылку ведущую на меню кафе
     *
     * @param string $html
     * @return null|string
     */
    private function parseLinkToMenu($html)
    {
        $this->setHtml($html);

        $crawler = $this->crawler->filter('body #content .entry-content');
        try {
            $menu_url = $crawler->selectLink('Меню')->attr('href');
        } catch (\InvalidArgumentException $e) {
            $menu_url = $crawler->selectLink('меню')->attr('href');
        }

        return $menu_url;
    }

    /**
     * Парсит карту кафе в формате ['название кафе' => 'ссылка на информацию о кафе']
     *
     * @param string $html
     *
     * @return array
     */
    private function parseCafeList($html)
    {
        $this->setHtml($html);

        $cafe_map = [];

        $this->crawler
            ->filter('body .xoxo .menu-item > a')
            ->each(function (Crawler $node) use (&$cafe_map) {
                $cafe_map[$node->attr('title')] = $node->attr('href');
            })
        ;

        return $cafe_map;
    }

    /**
     * Нормализует текст категории
     *
     * @param string $category
     *
     * @return string
     */
    private function normaliseCategory($category)
    {
        return trim(explode(',', $category)[0]);
    }

    /**
     * Нормализует название блюда
     *
     * @param Crawler|null $element
     * @return string
     */
    private function normaliseTitle(Crawler $element = null)
    {
        if ($element === null) {
            return '';
        }

        $title = (string) $element->text();

        return up_first(mb_strtolower($title));
    }

    /**
     * Нормализует значение веса блюда
     *
     * @param Crawler|null $element
     * @return string
     */
    private function normaliseWeight(Crawler $element = null)
    {
        if ($element === null) {
            return [];
        }

        $weight = (string) $element->text();

        return array_filter(array_map('trim', explode('/', $weight)));
    }

    /**
     * Нормализует значение цены на блюдо
     *
     * @param Crawler|null $element
     * @return float
     */
    private function normalisePrice(Crawler $element = null)
    {
        if ($element === null) {
            return 0.0;
        }

        $price = (string) $element->text();

        return (float) str_replace(',', '.', $price);
    }
}
