<?php

namespace CurtainCallWP\PostTypes;

use ArrayAccess;
use CurtainCallWP\Exceptions\UndefinedPropertyException;
use CurtainCallWP\Exceptions\UnsettableException;
use \WP_Post;
use CurtainCallWP\PostTypes\Traits\HasWordPressPost;
use CurtainCallWP\PostTypes\Traits\HasMeta;
use CurtainCallWP\PostTypes\Traits\HasAttributes;
use CurtainCallWP\PostTypes\Interfaces\Arrayable;
use Throwable;

/**
 * Class CurtainCallPost
 * @package CurtainCallWP\PostTypes
 * @property-read int $ID
 * @property-read string $post_author
 * @property-read string $post_date
 * @property-read string $post_date_gmt
 * @property-read string $post_content
 * @property-read string $post_title
 * @property-read string $post_excerpt
 * @property-read string $post_status
 * @property-read string $comment_status
 * @property-read string $ping_status
 * @property-read string $post_password
 * @property-read string $post_name
 * @property-read string $to_ping
 * @property-read string $pinged
 * @property-read string $post_modified
 * @property-read string $post_modified_gmt
 * @property-read string $post_content_filtered
 * @property-read int $post_parent
 * @property-read string $guid
 * @property-read int $menu_order
 * @property-read string $post_type
 * @property-read string $post_mime_type
 * @property-read string $comment_count
 * @property-read string $filter
 * @property-read string $ancestors
 * @property-read string $page_template
 * @property-read string $post_category
 * @property-read string $tags_input
 */
abstract class CurtainCallPost implements ArrayAccess, Arrayable
{
    use HasWordPressPost;
    use HasMeta;
    use HasAttributes;
    
    const POST_TYPE = 'ccwp_post';
    const META_PREFIX = '_ccwp_';
    
    protected static $join_table_name;
    protected static $join_table_alias = 'ccwp_join';
    
    /**
     * CurtainCallPost constructor.
     * @param int|WP_Post $post
     * @throws Throwable
     */
    private function __construct($post)
    {
        $this->loadPost($post);
        $this->loadMeta();
    }
    
    /**
     * Get the config array used when creating a WP custom post type
     * @return array
     */
    abstract public static function getConfig(): array;
    
    /**
     * @param int $id
     * @return CurtainCallPost
     * @throws Throwable
     */
    public static function find(int $id): self
    {
        return new static($id);
    }
    
    /**
     * @param WP_Post|null $post
     * @return CurtainCallPost
     * @throws Throwable
     */
    public static function make(WP_Post $post): self
    {
        return new static($post);
    }
    
    /**
     * @return string
     */
    public static function getJoinTableName(): string
    {
        if (empty(static::$join_table_name)) {
            global $wpdb;
            static::$join_table_name = $wpdb->prefix . 'ccwp_castandcrew_production';
        }
        
        return static::$join_table_name;
    }
    
    /**
     * @return string
     */
    public static function getJoinTableAlias(): string
    {
        return static::$join_table_alias;
    }
    
    /**
     * @param  string $key
     * @return mixed
     * @throws UndefinedPropertyException
     */
    public function __get($key)
    {
        if ($this->isWordPressPostAttribute($key)){
            return $this->wp_post->$key;
        }
    
        if ($this->isMetaAttribute($key)) {
            return $this->getMeta($key);
        }
    
        if ($this->isAttribute($key)) {
            return $this->getAttribute($key);
        }
        
        throw new UndefinedPropertyException('Undefined property: '. static::class .'::$'. $key);
    }
    
    /**
     * @param string $key
     * @param mixed  $value
     * @throws UnsettableException;
     */
    public function __set($key, $value)
    {
        if ($key === 'meta') {
            throw new UnsettableException('You can not set the meta property.');
        }
        
        if ($this->isWordPressPostAttribute($key)) {
            throw new UnsettableException('You can not set "'. $key .'" it is a WordPress post attribute.');
        }
        
        if ($this->isMetaAttribute($key)) {
            $this->setMeta($key, $value);
            return;
        }
        
        $this->setAttribute($key, $value);
    }
    
    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        if ($this->isWordPressPostAttribute($key)) {
            return isset($this->wp_post->$key);
        }
        
        if ($this->isMetaAttribute($key)) {
            return isset($this->meta[$key]);
        }

        return isset($this->attributes[$key]);
    }
    
    /**
     * @param $key
     */
    public function __unset($key)
    {
        if ($this->isMetaAttribute($key)) {
            unset($this->meta[$key]);
        }
        
        unset($this->attributes[$key]);
    }
    
    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
    
    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
    
    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }
    
    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
    
    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = isset($this->wp_post) ? $this->wp_post->to_array() : [];
        $data['attributes'] = $this->attributes;
        $data['meta'] = $this->meta;
        
        return $data;
    }
}