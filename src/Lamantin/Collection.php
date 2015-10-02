<?php namespace Lamantin;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection.
     *
     * @param  mixed $items
     * @return void
     */
    public function __construct($items = [])
    {
        $items = (null === $items ? [] : $this->getArrayableItems($items));

        $this->items = (array) $items;
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  Collection|array $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        return $items;
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  mixed $items
     * @return Collection
     */
    public static function make($items = null)
    {
        return new static($items);
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable $callback
     * @return Collection
     */
    public function filter(callable $callback)
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Transform each item in the collection using a callback.
     *
     * @param  callable $callback
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value;
        }, $this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }
}
