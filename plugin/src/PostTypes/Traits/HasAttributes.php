<?php

namespace CurtainCallWP\PostTypes\Traits;

trait HasAttributes
{
    /**
     * @var array
     */
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
        return $this->attributes[$key];
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