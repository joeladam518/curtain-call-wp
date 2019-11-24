<?php

namespace CurtainCallWP\PostTypes;

use CurtainCallWP\Exceptions\UndefinedPropertyException;
use CurtainCallWP\Exceptions\UnsettableException;
use WP_Post;
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
 * @property-read CurtainCallPostJoin $ccwp_join
 */
abstract class CurtainCallPost implements Arrayable
{
    use HasWordPressPost;
    use HasMeta;
    use HasAttributes;
    
    const POST_TYPE = 'ccwp_post';
    const META_PREFIX = '_ccwp_';
    
    protected static $join_table_name;
    protected static $join_table_name_with_alias;
    
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
        global $wpdb;
        
        if (empty(static::$join_table_name)) {
            static::$join_table_name = $wpdb->prefix . CurtainCallPostJoin::TABLE_NAME;
        }
        
        return static::$join_table_name;
    }
    
    public static function getJoinTableNameWithAlias(): string
    {
        if (empty(static::$join_table_name_with_alias)) {
            static::$join_table_name_with_alias  = '`' . static::getJoinTableName() . '`';
            static::$join_table_name_with_alias .= ' AS `' . CurtainCallPostJoin::TABLE_ALIAS . '`';
        }
        
        return static::$join_table_name_with_alias;
    }
    
    /**
     * @param CurtainCallPostJoin $curtain_call_post_join
     * @return static
     */
    public function setCurtainCallPostJoin(CurtainCallPostJoin $curtain_call_post_join): self
    {
        $this->setAttribute('ccwp_join', $curtain_call_post_join);
        return $this;
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
            return isset($this->meta[$this->getMetaKey($key)]);
        }

        return isset($this->attributes[$key]);
    }
    
    /**
     * @param $key
     */
    public function __unset($key)
    {
        if ($this->isMetaAttribute($key)) {
            unset($this->meta[$this->getMetaKey($key)]);
        }
        
        unset($this->attributes[$key]);
    }
    
    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = isset($this->wp_post) ? $this->wp_post->to_array() : [];
        $data['attributes'] = $this->attributesToArray();
        $data['meta'] = $this->meta;
        
        return $data;
    }
}