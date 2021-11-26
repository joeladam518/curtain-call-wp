<?php

namespace CurtainCall\PostTypes\Traits;

use CurtainCall\PostTypes\Interfaces\Arrayable;

trait HasAttributes
{
    /** @var array */
    protected $attributes = [];

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
    protected function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    protected function setAttribute(string $key, $value)
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
