<?php


namespace CurtainCallWP\PostTypes;

use CurtainCallWP\PostTypes\Interfaces\Arrayable;

/**
 * Class CurtainCallPostMeta
 * @package CurtainCallWP\PostTypes
 */
class CurtainCallPostMeta implements Arrayable
{
    /**
     * @var int
     */
    protected $post_id;
    
    /**
     * @var string
     */
    protected $post_class;
    
    /**
     * @var string
     */
    private $meta_prefix;
    
    /**
     * @var array
     */
    protected $meta = [];
    
    /**
     * @var array
     */
    protected $ccwp_meta_keys = [];
    
    /**
     * @var string
     */
    protected $ccwp_meta_key_match_pattern;
    
    /**
     * CurtainCallPostMeta constructor.
     * @param CurtainCallPost|string $post_class
     * @param int    $post_id
     * @param array  $ccwp_meta_keys
     */
    private function __construct(string $post_class, int $post_id, array $ccwp_meta_keys = [])
    {
        $this->post_id = $post_id;
        $this->post_class = $post_class;
        $this->meta_prefix = $post_class::META_PREFIX;
        $this->ccwp_meta_keys = $ccwp_meta_keys;
        $this->ccwp_meta_key_match_pattern = "~^{$this->meta_prefix}([_a-zA-Z0-9]+)$~";
        $this->load();
    
        if (empty($this->ccwp_meta_keys)) {
            foreach ($this->meta as $key => $meta) {
                if (preg_match($this->ccwp_meta_key_match_pattern, $key)) {
                    $stripedKey = str_replace($this->meta_prefix, '', $key);
                    $this->ccwp_meta_keys[] = $stripedKey;
                }
            }
        }
    }
    
    /**
     * @param CurtainCallPost|string $post_class
     * @param int    $post_id
     * @param array  $ccwp_meta_keys
     * @return CurtainCallPostMeta
     */
    public static function make(string $post_class, int $post_id, array $ccwp_meta_keys = []): self
    {
        return new static($post_class, $post_id, $ccwp_meta_keys);
    }
    
    /**
     * @param string $key
     * @return boolean
     */
    public function isCCWPMeta(string $key): bool
    {
        return in_array($key, $this->ccwp_meta_keys);
    }
    
    /**
     * @param string $key
     * @param bool   $hidden
     * @return string
     */
    protected function getMetaKey(string $key, $hidden = true): string
    {
        if ($this->isCCWPMeta($key)) {
            return $this->meta_prefix . $key;
        }
        
        if ($hidden) {
            if (substr($key, 0, 1) === '_') {
                return $key;
            }
    
            return '_' . $key;
        }

        return $key;
    }
    
    /**
     * @param string $key
     * @param bool   $hidden
     * @return boolean
     */
    public function has(string $key, $hidden = true): bool
    {
        if ($this->isCCWPMeta($key)) {
            return true;
        }
        
        if (array_key_exists($key, $this->meta)) {
            return true;
        }
        
        return metadata_exists('post', $this->post_id, $this->getMetaKey($key, $hidden));
    }
    
    protected function load(): self
    {
        $this->meta = array_map(function($item){
            return $item[0] ?? null;
        }, $this->fetchAll());
        
        return $this;
    }
    
    /**
     * @param string $key
     * @param bool   $hidden
     * @return false|mixed
     */
    protected function fetch(string $key, $hidden = true)
    {
        return get_post_meta($this->post_id, $this->getMetaKey($key, $hidden), true);
    }
    
    /**
     * @return array
     */
    protected function fetchAll():array
    {
        return get_post_meta($this->post_id);
    }
    
    /**
     * @param boolean $fresh
     * @return array
     */
    public function all($fresh = false): array
    {
        if ($fresh) {
            $this->load();
        }
        
        return $this->meta;
    }
    
    /**
     * @param string $key
     * @param bool   $hidden
     * @return null|mixed
     */
    protected function getMeta(string $key, $hidden = true)
    {
        $meta_value = $this->meta[$this->getMetaKey($key, $hidden)] ?? null;
        
        if (isset($meta_value)) {
            return $meta_value;
        }
        
        $meta_value = $this->fetch($key);
        
        if (empty($meta_value)) {
            return null;
        }
        
        $this->setMeta($key, $meta_value);
        
        return $meta_value;
    }
    
    public function __get($key)
    {
        if (!$this->has($key)) {
            trigger_error('Undefined property: '. $this->post_class .'::$'. $key, E_USER_ERROR);
        }
        
        return $this->getMeta($key);
    }
    
    /**
     * @param  string $key
     * @param  mixed  $value
     * @return CurtainCallPostMeta
     */
    protected function setMeta(string $key, $value): self
    {
        $this->meta[$this->getMetaKey($key)] = $value;
        return $this;
    }
    
    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (!$this->isCCWPMeta($key)) {
            trigger_error('Can not set: '. $this->post_class .'::$'. $key .' as it is not ccwp meta field.', E_USER_WARNING);
            return;
        }
        
        $this->setMeta($key, $value);
    }
    
    /**
     * @param  string $key
     * @param  mixed  $value
     * @return boolean
     */
    public function update(string $key, $value): bool
    {
        if ($result = update_post_meta($this->post_id, $this->getMetaKey($key), $value)) {
            $this->setMeta($key, $value);
        }
        return $result;
    }
    
    /**
     * @return bool
     */
    public function save(): bool
    {
        foreach ($this->meta as $key => $value) {
            // only update ccwp post meta
            if (preg_match($this->ccwp_meta_key_match_pattern, $key)) {
                update_post_meta($this->post_id, $key, sanitize_text_field((string)$value));
            }
        }
    
        return true;
    }
    
    /**
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        if ($result = delete_post_meta($this->post_id, $this->getMetaKey($key))) {
            $this->__unset($key);
        }
        return $result;
    }
    
    /**
     * @return array
     */
    public function toArray(): array
    {
       return $this->meta;
    }
    
    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->meta[$this->getMetaKey($key)]);
    }
    
    /**
     * @param $key
     */
    public function __unset($key)
    {
        if ($this->isCCWPMeta($key)) {
            unset($this->meta[$this->getMetaKey($key)]);
        }
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        $result = json_encode($this->toArray());
        
        if (empty($result)) {
            return '';
        }
        
        return $result;
    }
}