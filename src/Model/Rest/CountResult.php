<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 11/14/18
 * Time: 10:51 AM
 */

namespace AE\SalesforceRestSdk\Model\Rest;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class CountResult
 *
 * @package AE\SalesforceRestSdk\Model\Rest
 */
class CountResult implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var array|Count[]
     * @Serializer\Type("array<AE\SalesforceRestSdk\Model\Rest\Count>")
     */
    private $sObjects = [];

    /**
     * @return array|Count[]
     */
    public function getSObjects(): array
    {
        return $this->sObjects;
    }

    /**
     * @param array|Count[] $sObjects
     *
     * @return CountResult
     */
    public function setSObjects(array $sObjects): CountResult
    {
        $this->sObjects = $sObjects;

        return $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->sObjects[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->sObjects[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->sObjects[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->sObjects[$offset]);
    }

    public function current(): mixed
    {
        return current($this->sObjects);
    }

    public function next(): void
    {
        next($this->sObjects);
    }

    public function key(): mixed
    {
        return key($this->sObjects);
    }

    public function valid(): bool
    {
        return false !== current($this->sObjects);
    }

    public function rewind(): void
    {
        reset($this->sObjects);
    }

    public function count(): int
    {
        return count($this->sObjects);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->sObjects);
    }
}
