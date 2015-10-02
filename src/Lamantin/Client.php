<?php namespace Lamantin;

use Lamantin\Collections\Collection;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client as Guzzle;

class Client
{
    const BASE_LAMANTIN_URL = 'http://lamantin-kafe.ru';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = new Request(new Guzzle());
        $this->parser = new Parser(new Crawler(), new Normalizer());
    }

    public function getMenu()
    {
        $cafe_list = $this->parser->parseCafeList($this->request->request(self::BASE_LAMANTIN_URL)->getContent());
        $menu_map = $this->parseAllCafeMenu($cafe_list);

        return $menu_map;
    }

    public function getFlattenMenu()
    {
        return $this->flattenMenu($this->getMenu());
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
        $cafe_menu = [];

        foreach ($cafeList as $title => $info_url) {
            try {
                $menu_url = $this->parser->parseLinkToMenu($this->request->request($info_url)->getContent());
                $menu = $this->parser->parseCafeMenu($this->request->request($menu_url)->getContent());

                $cafe_menu[$title] = $menu;
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        return $cafe_menu;
    }

    private function flattenMenu($list)
    {
        $flat_menu = [];

        if (count($list) === 0) {
            return $flat_menu;
        }

        foreach ($list as $cafe => $menu) {
            foreach ((array) $menu as $category => $meal_list) {
                foreach ((array) $meal_list as $meal) {
                    $flat_menu[] = array_merge($meal, compact('cafe', 'category'));
                }
            }
        }

        return $flat_menu;
    }
}
