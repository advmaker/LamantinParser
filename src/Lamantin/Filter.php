<?php namespace Lamantin;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Filter
{
    /**
     * @var ExpressionLanguage
     */
    private $expression;
    
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param ExpressionLanguage $expression
     */
    public function __construct(ExpressionLanguage $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param Collection $collection
     *
     * @return Collection
     */
    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;

        return $collection;
    }

    /**
     * @param string $input
     */
    public function filterTitle($input)
    {
        if (empty($input) || $this->collection->isEmpty()) {
            return null;
        }

        list($operand, $value) = array_values($this->getOperandAndValue($input));

        $this->collection = $this->collection->filter(function (array $entry) use ($operand, $value) {
            return $this->stringComparator($entry['title'], $value, $operand);
        });
    }

    /**
     * @param string $input
     */
    public function filterWeight($input)
    {
        if (empty($input) || $this->collection->isEmpty()) {
            return null;
        }

        list($operand, $value) = array_values($this->getOperandAndValue($input));

        $this->collection = $this->collection->filter(function (array $entry) use ($operand, $value) {
            $weight = array_sum($entry['weight']);

            return $this->expression->evaluate(
                "weight {$operand} value",
                compact('weight', 'value')
            );
        });
    }

    /**
     * @param string $input
     */
    public function filterPrice($input)
    {
        if (empty($input) || $this->collection->isEmpty()) {
            return null;
        }

        list($operand, $value) = array_values($this->getOperandAndValue($input));

        $this->collection = $this->collection->filter(function (array $entry) use ($operand, $value) {
            return $this->expression->evaluate(
                "entry['price'] {$operand} value",
                compact('entry', 'value')
            );
        });
    }

    /**
     * @param string $input
     */
    public function filterCafe($input)
    {
        if (empty($input) || $this->collection->isEmpty()) {
            return null;
        }

        list($operand, $value) = array_values($this->getOperandAndValue($input));

        $this->collection = $this->collection->filter(function (array $entry) use ($operand, $value) {
            return $this->stringComparator($entry['cafe'], $value, $operand);
        });
    }

    /**
     * @param string $input
     */
    public function filterCategory($input)
    {
        if (empty($input) || $this->collection->isEmpty()) {
            return null;
        }

        list($operand, $value) = array_values($this->getOperandAndValue($input));

        $this->collection = $this->collection->filter(function (array $entry) use ($operand, $value) {
            return $this->stringComparator($entry['category'], $value, $operand);
        });
    }

    /**
     * @param string $input
     *
     * @return array
     */
    private function getOperandAndValue($input)
    {
        $input = array_filter(array_map('trim', explode(' ', $input)));
        $operand = '==';

        if (count($input) === 2) {
            list($operand, $value) = $input;
        } else {
            $value = $input[0];
        }

        switch ($operand) {
            case '=':
                $operand = '==';
                break;
            case '==':
            case 'c':
            case 'contains':
            case '===':
            case 'eq':
            case 'equals':
            case 'sw':
            case 'ew':
            case '>=':
            case '<=':
            case '>':
            case '<':
                break;
            default:
                throw new \InvalidArgumentException('Недопустимый оператор');
        }

        return ['operand' => $operand, 'value' => $value];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param string $operand
     *
     * @return bool
     */
    private function stringComparator($haystack, $needle, $operand)
    {
        $haystack = mb_strtolower(trim($haystack));
        $needle = mb_strtolower(trim($needle));
        $result = false;

        switch ($operand) {
            case '==':
            case 'c':
            case 'contains':
                $result = mb_strpos($haystack, $needle) !== false;
                break;
            case 'eq':
            case 'equals':
            case '===':
                $result = strcmp($haystack, $needle) === 0;
                break;
            case 'sw':
            case 'starts-with':
                $result = mb_strpos($haystack, $needle) === 0;
                break;
            case 'ew':
            case 'ends-with':
                $result = ($needle === mb_substr($haystack, -mb_strlen($needle)));
                break;
        }

        return $result;
    }
}
