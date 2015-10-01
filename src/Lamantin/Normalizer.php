<?php namespace Lamantin;

use Symfony\Component\DomCrawler\Crawler;

class Normalizer
{
    /**
     * Нормализует текст категории
     *
     * @param string $category
     *
     * @return string
     */
    public function normaliseCategory($category)
    {
        return trim(explode(',', $category)[0]);
    }

    /**
     * Нормализует название блюда
     *
     * @param Crawler|null $element
     * @return string
     */
    public function normaliseTitle(Crawler $element = null)
    {
        if ($element === null) {
            return '';
        }

        $title = (string) $element->text();

        return Helpers::upFirst(mb_strtolower($title));
    }

    /**
     * Нормализует значение веса блюда
     *
     * @param Crawler|null $element
     * @return string
     */
    public function normaliseWeight(Crawler $element = null)
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
    public function normalisePrice(Crawler $element = null)
    {
        if ($element === null) {
            return 0.0;
        }

        $price = (string) $element->text();

        return (float) str_replace(',', '.', $price);
    }
}
