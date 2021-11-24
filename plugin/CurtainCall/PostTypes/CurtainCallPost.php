<?php

namespace CurtainCall\PostTypes;

use Carbon\CarbonImmutable as Carbon;
use CurtainCall\Exceptions\UndefinedPropertyException;
use CurtainCall\Exceptions\UnsettableException;
use WP_Post;
use CurtainCall\PostTypes\Traits\HasWordPressPost;
use CurtainCall\PostTypes\Traits\HasMeta;
use CurtainCall\PostTypes\Traits\HasAttributes;
use CurtainCall\PostTypes\Interfaces\Arrayable;
use Throwable;
use wpdb;

/**
 * @property-read int    $ID
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
 * @property-read int    $post_parent
 * @property-read string $guid
 * @property-read int    $menu_order
 * @property-read string $post_type
 * @property-read string $post_mime_type
 * @property-read string $comment_count
 * @property-read string $filter
 * @property-read string $ancestors
 * @property-read string $page_template
 * @property-read string $post_category
 * @property-read string $tags_input
 * @property-read CurtainCallPivot $ccwp_join
 */
abstract class CurtainCallPost implements Arrayable
{
    use HasWordPressPost;
    use HasMeta;
    use HasAttributes;

    const POST_TYPE = 'ccwp_post';
    const META_PREFIX = '_ccwp_';

    /**
     * The join table name
     * TODO: 2019-12-01: move this to the CurtainCallPostJoin model
     * @var string|null
     */
    protected static ?string $join_table_name;

    /**
     * The join table name with alias
     * TODO: 2019-12-01: move this to the CurtainCallPostJoin model
     * @var string|null
     */
    protected static ?string $join_table_name_with_alias;

    /**
     * The cached current date string
     * @var string|null
     */
    protected static ?string $todays_date;

    /**
     * @param int|WP_Post $post
     * @throws Throwable
     */
    private function __construct($post)
    {
        $this->loadPost($post);
        $this->loadMeta();
    }

    /**
     * @param int $id
     * @return $this
     * @throws Throwable
     */
    public static function find(int $id)
    {
        return new static($id);
    }

    /**
     * @param WP_Post $post
     * @return $this
     * @throws Throwable
     */
    public static function make(WP_Post $post)
    {
        return new static($post);
    }

    /**
     * Get the current date string and cache it for the entire request
     * @return string
     */
    protected static function getTodaysDate(): string
    {
        if (empty(static::$todays_date)) {
            static::$todays_date = Carbon::now()->toDateString();
        }

        return static::$todays_date;
    }

    /**
     * @global wpdb $wpbd
     * @return string
     */
    public static function getJoinTableName(): string
    {
        global $wpdb;

        if (empty(static::$join_table_name)) {
            static::$join_table_name = $wpdb->prefix . CurtainCallPivot::TABLE_NAME;
        }

        return static::$join_table_name;
    }

    public static function getJoinTableNameWithAlias(): string
    {
        if (empty(static::$join_table_name_with_alias)) {
            static::$join_table_name_with_alias  = '`' . static::getJoinTableName() . '`';
            static::$join_table_name_with_alias .= ' AS `' . CurtainCallPivot::TABLE_ALIAS . '`';
        }

        return static::$join_table_name_with_alias;
    }

    /**
     * @param CurtainCallPivot $curtainCallPivot
     * @return $this
     */
    public function setCurtainCallPostJoin(CurtainCallPivot $curtainCallPivot)
    {
        $this->setAttribute('ccwp_join', $curtainCallPivot);

        return $this;
    }

    public function toArray(): array
    {
        $data = isset($this->wp_post) ? $this->wp_post->to_array() : [];
        $data['attributes'] = $this->attributesToArray();
        $data['meta'] = $this->meta;

        return $data;
    }

    /**
     * @param  string $key
     * @return mixed|null
     * @throws UndefinedPropertyException
     */
    public function __get($key)
    {
        if ($this->isWordPressPostAttribute($key)) {
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
     * @return void
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
     * @param string $key
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
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if ($this->isMetaAttribute($key)) {
            unset($this->meta[$this->getMetaKey($key)]);
        } else {
            unset($this->attributes[$key]);
        }
    }

    /**
     * Get the config array used when creating a WP custom post type
     * @return array
     */
    abstract public static function getConfig(): array;
}
