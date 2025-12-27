<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @property array<string, mixed> $attributes
 */
trait HasAttributes
{
    /**
     * @param string $key
     * @return bool
     */
    protected function isAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    protected function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    protected function attributesToArray(): array
    {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            if ($value instanceof Arrayable) {
                $attributes[$key] = $value->toArray();
            } else {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }
}
