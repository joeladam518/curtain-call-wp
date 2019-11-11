<?php

namespace CurtainCallWP\PostTypes;

use \WP_Post;
use CurtainCallWP\PostTypes\Traits\HasAttributes;
use CurtainCallWP\PostTypes\Traits\HasWordPressPost;
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
abstract class CurtainCallPost implements Arrayable
{
    use HasAttributes;
    use HasWordPressPost;
    
    const JOIN_TABLE = 'ccwp_castandcrew_production';
    const POST_TYPE = 'ccwp_post';
    const META_PREFIX = '_ccwp_';
    
    /**
     * @var null|CurtainCallPostMeta
     */
    protected $meta;
    
    /**
     * @var array
     */
    protected $ccwp_meta_keys = [];
    
    /**
     * CurtainCallPost constructor.
     * @param null $post
     * @throws Throwable
     */
    private function __construct($post = null)
    {
        if (isset($post)) {
            if ($post instanceof WP_Post) {
                $this->setPost($post);
            } else if (intval($post) > 0) {
                $post = $this->fetchPost(intval($post));
                $this->setPost($post);
            } else {
                throw new \InvalidArgumentException('$post must be an int or an instance of WP_Post.');
            }
        
            $this->setPostProperties();
        }
    
        $this->setPostMeta();
    }
    
    abstract public static function getConfig(): array;
    
    /**
     * @param int $id
     * @return static
     * @throws Throwable
     */
    public static function find(int $id): self
    {
        return new static($id);
    }
    
    /**
     * @param WP_Post|null $post
     * @return static
     * @throws Throwable
     */
    public static function make(WP_Post $post = null): self
    {
        return new static($post);
    }
    
    /**
     * @return string
     */
    public static function getJoinTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::JOIN_TABLE;
    }
    
    /**
     * @return $this
     */
    protected function setPostMeta(): self
    {
        if (empty($this->wp_post->ID)) {
            $this->meta = null;
        }
        
        $this->meta = CurtainCallPostMeta::make(static::class, $this->wp_post->ID, $this->ccwp_meta_keys);
        
        return $this;
    }
    
    /**
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, $this->wp_post_properties)){
            return $this->wp_post->$key;
        }
    
        if (isset($this->meta->$key)) {
            return $this->meta->$key;
        }
    
        if (array_key_exists($key, $this->attributes)) {
            return $this->getAttribute($key);
        }
    
        trigger_error('Undefined property: '. static::class .'::$'. $key, E_USER_ERROR);
    }
    
    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        if ($key === 'meta') {
            return;
        }
        
        if (in_array($key, $this->wp_post_properties)) {
            return;
        }
        
        if ($this->meta->has($key)) {
            $this->meta->$key = $value;
            return;
        }
        
        $this->setAttribute($key, $value);
    }

    /**
     * Restricted to only updating ccwp postmeta
     *
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function updateMeta(string $key, $value)
    {
        if (!in_array($key, $this->ccwp_meta_keys)) {
            return false;
        }
        
        return $this->meta->update($key, $value);
    }
    
    /**
     * Restricted to only deleting ccwp postmeta
     *
     * @param string $key
     * @return bool
     */
    public function deleteMeta(string $key)
    {
        if (!in_array($key, $this->ccwp_meta_keys)) {
            return false;
        }
        
        return $this->meta->delete($key);
    }
    
    /**
     * Save everything attached to this post
     * 
     * @return bool
     */
    public function save(): bool
    {
        return $this->meta->save();
    }
    
    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return (isset($this->meta) && isset($this->meta->$key))
               || isset($this->attributes[$key])
               || isset($this->wp_post->$key);
    }
    
    /**
     * @param $key
     */
    public function __unset($key)
    {
        if (isset($this->meta->$key)) {
            unset($this->meta->$key);
        }
        
        if (isset($this->attributes[$key])) {
            unset($this->attributes[$key]);
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
    
    /**
     * @return array
     */
    public function toArray(): array
    {
        $ccwp_post = isset($this->wp_post) ? $this->wp_post->to_array() : [];
        $ccwp_post['meta'] = isset($this->meta) ? $this->meta->toArray() : [];
        $ccwp_post['ccwp_attributes'] = $this->attributes;
        
        return $ccwp_post;
    }
}