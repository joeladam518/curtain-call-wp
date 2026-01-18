<?php

declare(strict_types=1);

namespace CurtainCall\Data;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements ArrayAccess<string, mixed>
 * @implements Arrayable<string, mixed>
 */
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
        return is_string($offset) && property_exists($this, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_string($offset)) {
            return null;
        }

        return $this->offsetExists($offset) ? $this->{$offset} : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_string($offset)) {
            return;
        }

        if ($this->offsetExists($offset)) {
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        // do nothing
    }
}
