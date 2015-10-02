<?php namespace Lamantin;

use Lamantin\Collections\Collection;
use Symfony\Component\DomCrawler\Crawler;

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
     * @var Storage
     */
    private $storage;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->parser = new Parser(new Crawler(), new Normalizer());
        $this->storage = new Storage(getenv('HOME') . '/.lamantin');
    }

    /**
     * Возвращает структурированный массив меню
     *
     * @return array
     */
    public function getMenu()
    {
        $file_name = 'menu_' . date('Y-m-d');
        if ($this->storage->has($file_name)) {
            return $this->storage->get($file_name);
        }

        $cafe_list = $this->parser->parseCafeList($this->request->request(self::BASE_LAMANTIN_URL)->getContent());
        $menu_map = $this->parseAllCafeMenu($cafe_list);
        $this->storage->put($file_name, $menu_map);

        return $menu_map;
    }

    /**
     * Возвращает одномерный массив меню
     *
     * @return array
     */
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

    /**
     * Плющит меню в одномерный массив
     *
     * @param array $list
     *
     * @return array
     */
    private function flattenMenu(array $list)
    {
        $flat_menu = [];

        if (count($list) === 0) {
            return $flat_menu;
        }

        foreach ($list as $cafe => $menu) {
            foreach ((array) $menu as $category => $meal_list) {
                foreach ((array) $meal_list as $meal) {
                    $flat_menu[] = array_merge($meal, compact('category', 'cafe'));
                }
            }
        }

        return $flat_menu;
    }
}
