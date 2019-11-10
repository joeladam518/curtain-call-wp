<?php

namespace CurtainCallWP\PostTypes\Traits;

trait HasAttributes
{
    protected $attributes = [];
    
    protected function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
    
    protected function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        
        return $this;
    }
}