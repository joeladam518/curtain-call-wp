<?php

namespace CurtainCallWP\PostTypes\Traits;

trait HasAttributes
{
    protected $attributes = [];
    
    /**
     * @param string $key
     * @return mixed|null
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
    protected function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        
        return $this;
    }
}