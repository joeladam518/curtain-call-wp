<?php

declare(strict_types=1);

namespace CurtainCall\Models;

use CurtainCall\Exceptions\UndefinedPropertyException;
use CurtainCall\Exceptions\UnsettableException;
use CurtainCall\Models\Traits\HasAttributes;
use CurtainCall\Models\Traits\HasMeta;
use CurtainCall\Models\Traits\HasWordPressPost;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Throwable;
use WP_Post;
use WP_Query;

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
    use HasAttributes;
    use HasMeta;
    use HasWordPressPost;

    public const POST_TYPE = 'ccwp_post';
    public const META_PREFIX = '_ccwp_';

    /** @var array<string, mixed> */
    protected array $attributes = [];
    /** @var list<string> */
    protected array $ccwp_meta = [];
    protected array $image_cache = [];
    protected array $meta = [];
    protected WP_Post $wp_post;
    protected array $wp_post_attributes = [];

    /**
     * @param int|string|WP_Post $post
     * @throws Throwable
     */
    protected function __construct(int|string|WP_Post $post)
    {
        $this->loadPost($post);
        $this->loadMeta();
    }

    /**
     * @param int $id
     * @return $this
     * @throws Throwable
     */
    public static function find(int $id): static
    {
        return new static($id);
    }

    /**
     * @param WP_Post $post
     * @return $this
     * @throws Throwable
     */
    public static function make(WP_Post $post): static
    {
        return new static($post);
    }

    /**
     * Convert an array structure to a collection of CurtainCall posts
     *
     * @param array $data
     * @return array|CurtainCallPost[]
     * @throws Throwable
     */
    public static function toCurtainCallPosts(array $data): array
    {
        $posts = [];
        $pivotFields = CurtainCallPivot::getFields(true);

        foreach ($data as $datum) {
            // Separate CurtainCallPivot data from WP_Post data
            $postData = [];
            $pivotData = [];
            foreach ($datum as $key => $value) {
                if (in_array($key, $pivotFields, true)) {
                    $pivotData[$key] = $value;
                } else {
                    $postData[$key] = $value;
                }
            }

            // Convert $postData to WP_Post
            $post = new WP_Post((object) $postData);

            // Convert $post to CurtainCallPost and add it to the array
            switch ($post->post_type) {
                case Production::POST_TYPE:
                    $posts[] = Production::make($post)->setCurtainCallPostJoin(new CurtainCallPivot($pivotData));
                    break;
                case CastAndCrew::POST_TYPE:
                    $posts[] = CastAndCrew::make($post)->setCurtainCallPostJoin(new CurtainCallPivot($pivotData));
                    break;
            }
        }

        return $posts;
    }

    /**
     * Retrieves an image data to for the attachment.
     *
     * @param string $size
     * @param bool $icon
     * @return array|null
     */
    public function getFeaturedImage(string $size = 'thumbnail', bool $icon = false): ?array
    {
        if (!isset($this->image_cache[$size]) || $this->image_cache[$size] === null) {
            $imageSrc = wp_get_attachment_image_src(get_post_thumbnail_id($this->ID), $size, $icon);

            if ($imageSrc && isset($imageSrc[0])) {
                $this->image_cache[$size] = [
                    'url' => $imageSrc[0],
                    'width' => $imageSrc[1] ?? null,
                    'height' => $imageSrc[2] ?? null,
                ];
            } else {
                $this->image_cache[$size] = null;
            }
        }

        return $this->image_cache[$size];
    }

    /**
     * Set the pivot table attribute
     *
     * @param CurtainCallPivot $curtainCallPivot
     * @return $this
     */
    public function setCurtainCallPostJoin(CurtainCallPivot $curtainCallPivot): static
    {
        $this->setAttribute('ccwp_join', $curtainCallPivot);

        return $this;
    }

    /**
     * Convert the post to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = isset($this->wp_post) ? $this->wp_post->to_array() : [];
        $data['attributes'] = $this->attributesToArray();
        $data['meta'] = $this->meta;

        return $data;
    }

    /**
     * Get a post-property
     *
     * @param  string $key
     * @return mixed|null
     * @throws UndefinedPropertyException
     */
    public function __get(string $key): mixed
    {
        if ($this->isPostAttribute($key)) {
            return $this->wp_post->$key;
        }

        if ($this->isMetaAttribute($key)) {
            return $this->getMeta($key);
        }

        if ($this->isAttribute($key)) {
            return $this->getAttribute($key);
        }

        throw new UndefinedPropertyException('Undefined property: ' . static::class . '::$' . $key);
    }

    /**
     * Set a post-property
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     * @throws UnsettableException;
     */
    public function __set(string $key, mixed $value): void
    {
        if (in_array($key, ['attributes', 'meta', 'ccwp_meta', 'wp_post', 'wp_post_attributes', 'image_cache'], true)) {
            throw new UnsettableException('You can not set the ' . $key . ' property.');
        }

        if ($this->isPostAttribute($key)) {
            $this->wp_post->$key = $value;
            return;
        }

        if ($this->isMetaAttribute($key)) {
            $this->setMeta($key, $value);
            return;
        }

        $this->setAttribute($key, $value);
    }

    /**
     * Check if a post-property is set. This includes wp_post properties,
     * meta-attributes, and attributes.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        if ($this->isPostAttribute($key)) {
            return isset($this->wp_post->$key);
        }

        if ($this->isMetaAttribute($key)) {
            return isset($this->meta[$this->getMetaKey($key)]);
        }

        return isset($this->attributes[$key]);
    }

    /**
     * Unset attribute or meta. You purposely cannot unset a wp_post property.
     *
     * @param string $key
     * @return void
     */
    public function __unset(string $key): void
    {
        if ($this->isMetaAttribute($key)) {
            unset($this->meta[$this->getMetaKey($key)]);
        } else {
            unset($this->attributes[$key]);
        }
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Get the config array used when creating a WP custom post type
     *
     * @return array
     */
    abstract public static function getConfig(): array;

    /**
     * Query for Posts
     *
     * @param array $additionalArgs
     * @return WP_Query
     */
    abstract public static function getPosts(array $additionalArgs = []): WP_Query;
}
