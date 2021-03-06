<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\ServiceLocator;

abstract class AbstractReflectionCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $items = [];
    protected $serviceLocator;

    protected function __construct(ServiceLocator $serviceLocator, array $items)
    {
        $this->serviceLocator = $serviceLocator;
        $this->items = $items;
    }

    public function count()
    {
        return count($this->items);
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }

    public static function fromReflections(ServiceLocator $serviceLocator, array $reflections)
    {
        return new static($serviceLocator, $reflections);
    }

    public function merge(AbstractReflectionCollection $collection)
    {
        if (false === $collection instanceof static) {
            throw new \InvalidArgumentException(sprintf(
                'Collection must be instance of "%s"',
                static::class
            ));
        }

        $items = $this->items;

        foreach ($collection as $key => $value) {
            $items[$key] = $value;
        }

        return new static($this->serviceLocator, $items);
    }

    public function get(string $name)
    {
        if (!isset($this->items[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown item "%s", known items: "%s"',
                $name, implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$name];
    }

    public function first()
    {
        return reset($this->items);
    }

    public function last()
    {
        return end($this->items);
    }

    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetSet($name, $value)
    {
        throw new \BadMethodCallException('Collections are immutable');
    }

    public function offsetUnset($name)
    {
        throw new \BadMethodCallException('Collections are immutable');
    }

    public function offsetExists($name)
    {
        return isset($this->items[$name]);
    }
}
