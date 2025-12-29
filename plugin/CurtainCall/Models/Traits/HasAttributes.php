<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

trait HasAttributes
{
    /** @var array<array-key, mixed> $this->attributes */
    protected array $attributes = [];

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
        return Arr::mapWithKeys($this->attributes, static fn($value, $key) => (
            $value instanceof Arrayable ? [$key => $value->toArray()] : [$key => $value]
        ));
    }
}
