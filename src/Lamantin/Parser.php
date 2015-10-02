<?php namespace Lamantin;

use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler, Normalizer $normalizer)
    {
        $this->crawler = $crawler;
        $this->normalizer = $normalizer;
    }

    /**
     * Парсит меню кафе в формате ['категория блюда' => [['название блюда', 'вес', 'цена'], ...]]
     *
     * @param string $html
     *
     * @return array
     */
    public function parseCafeMenu($html)
    {
        $this->setHtml($html);

        $menu = [];
        $category = null;

        $this->crawler
            ->filterXPath('//body//div[@id="content"]//div[@class="entry-content"]//table/tbody/tr')
            ->reduce(function (Crawler $node) {
                return $node->filterXPath('//td')->count() === 3;
            })
            ->slice(1)
            ->each(function (Crawler $node) use (&$menu, &$category) {
                $td_nodes = $node->filterXPath('//td');
                $title = $this->normalizer->normaliseTitle($td_nodes->eq(0));
                $weight = $this->normalizer->normaliseWeight($td_nodes->eq(1));
                $price = $this->normalizer->normalisePrice($td_nodes->eq(2));

                if ($price === 0.0 && count($weight) === 0) {
                    $category = $this->normalizer->normaliseCategory($title);
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
    public function parseLinkToMenu($html)
    {
        $this->setHtml($html);

        $crawler = $this->crawler->filterXPath('//body//div[@id="content"]//div[@class="entry-content"]');
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
    public function parseCafeList($html)
    {
        $this->setHtml($html);

        $cafe_map = [];

        $this->crawler
            ->filterXPath('//body//ul[@class="xoxo"]//li/a')
            ->each(function (Crawler $node) use (&$cafe_map) {
                $cafe_map[$node->attr('title')] = $node->attr('href');
            })
        ;

        return $cafe_map;
    }

    /**
     * @param string $html
     */
    private function setHtml($html)
    {
        $this->crawler->clear();
        $this->crawler->addHtmlContent($html);
    }
}
