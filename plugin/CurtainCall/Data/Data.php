<?php

declare(strict_types=1);

namespace CurtainCall\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

abstract class Data implements ArrayAccess, Arrayable
{
    /**
     * @param array<string, mixed> $data
     * @return static
     */
    abstract public static function fromArray(array $data): self;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    public function offsetExists(mixed $offset): bool
    {
        return $offset && property_exists($this, $offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->offsetExists($offset) ? $this->{$offset} : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        // do nothing
    }
}
