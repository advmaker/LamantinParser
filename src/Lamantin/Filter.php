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

    public function getCollection()
    {
        return $this->collection;
    }

    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;

        return $collection;
    }

    public function filterTitle($input)
    {
        if (empty($input)) {
            return null;
        }

        $this->collection = $this->collection->filter(function (array $entry) use ($input) {
            return mb_stripos($entry['title'], $input) !== false;
        });
    }

    public function filterWeight($input)
    {
        if (empty($input)) {
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

    public function filterPrice($input)
    {
        if (empty($input)) {
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

    public function filterCafe($input)
    {
        if (empty($input)) {
            return null;
        }

        $this->collection = $this->collection->filter(function (array $entry) use ($input) {
            return mb_stripos($entry['cafe'], $input) !== false;
        });
    }

    public function filterCategory($input)
    {
        if (empty($input)) {
            return null;
        }

        dump($input);

        $this->collection = $this->collection->filter(function (array $entry) use ($input) {
            return mb_stripos($entry['category'], $input) !== false;
        });
    }

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
            case '===':
            case 'eq':
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
}
