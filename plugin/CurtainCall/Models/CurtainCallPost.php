<?php

declare(strict_types=1);

namespace CurtainCall\Models;

use CurtainCall\Data\ImageData;
use CurtainCall\Data\ProductionData;
use CurtainCall\Exceptions\PostNotFoundException;
use CurtainCall\Exceptions\UndefinedPropertyException;
use CurtainCall\Exceptions\UnsettableException;
use CurtainCall\Models\Traits\HasAttributes;
use CurtainCall\Models\Traits\HasMeta;
use CurtainCall\Models\Traits\HasWordPressPost;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use WP_Post;
use WP_Query;

/**
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
 * @property-read CurtainCallPivot|null $ccwp_join
 * @implements Arrayable<string, mixed>
 */
abstract class CurtainCallPost implements Arrayable
{
    use HasAttributes;
    use HasMeta;
    use HasWordPressPost;

    public const POST_TYPE = 'ccwp_post';
    public const META_PREFIX = '_ccwp_';

    /**
     * @param WP_Post $post
     */
    final protected function __construct(WP_Post $post)
    {
        $this->setPost($post);
        $this->loadMeta();
    }

    /**
     * @param int|string $id - post id (int or numeric string)
     * @return static
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    public static function find(int|string $id): static
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException('Post id must be numeric');
        }

        $query = new WP_Query([
            'post_type' => static::POST_TYPE,
            'p' => (int) $id,
            'posts_per_page' => 1,
        ]);

        $post = $query->have_posts() ? $query->posts[0] : null;

        if (!($post instanceof WP_Post)) {
            throw new PostNotFoundException("Failed to fetch post. (id: {$id}, type: " . static::POST_TYPE . ')');
        }

        return new static($post);
    }

    /**
     * @param array<string, mixed> $data
     * @return static
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): static
    {
        $postData = [];
        $pivotData = [];
        foreach ($data as $key => $value) {
            if (static::isPostAttribute($key)) {
                $postData[$key] = $value;
                continue;
            }
            $strippedKey = CurtainCallPivot::stripPrefix($key);
            if (CurtainCallPivot::isField($strippedKey)) {
                $pivotData[$strippedKey] = $value;
            }
        }

        if (!$postData) {
            throw new InvalidArgumentException('Post data cannot be empty');
        }

        if (!isset($postData['ID'])) {
            throw new InvalidArgumentException('Post data must contain an ID');
        }

        if (!isset($postData['post_type']) || $postData['post_type'] !== static::POST_TYPE) {
            throw new InvalidArgumentException('Post data must be of type ' . static::POST_TYPE);
        }

        $model = static::make(new WP_Post((object) $postData));

        if ($pivotData) {
            $model->setPivot(new CurtainCallPivot($pivotData));
        }

        return $model;
    }

    /**
     * @param WP_Post $post
     * @return static
     */
    public static function make(WP_Post $post): static
    {
        return new static($post);
    }

    /**
     * @param list<WP_Post|object|array<string, mixed>> $posts
     * @return CurtainCallPost[]
     */
    public static function toCurtainCallPosts(array $posts): array
    {
        /** @var CurtainCallPost[] $models */
        $models = collect($posts)
            ->map(static function (array|object $post): ?CurtainCallPost {
                /** @var array<string, mixed> $data */
                $data = is_object($post) ? get_object_vars($post) : $post;
                return match ($data['post_type']) {
                    CastAndCrew::POST_TYPE => CastAndCrew::fromArray($data),
                    Production::POST_TYPE => Production::fromArray($data),
                    default => null,
                };
            })
            ->filter()
            ->values()
            ->all();

        return $models;
    }

    /**
     * Retrieves the post's image data.
     *
     * @param string $size
     * @param bool $icon
     * @return ImageData|null
     */
    public function getImageSource(string $size = 'thumbnail', bool $icon = false): ?ImageData
    {
        $image_id = get_post_thumbnail_id($this->ID) ?: null;

        if (!$image_id) {
            return null;
        }

        $image = wp_get_attachment_image_src($image_id, $size, $icon);

        if (!is_array($image)) {
            return null;
        }

        /** @var string $src */
        $src = $image[0];
        /** @var int|null $width */
        $width = $image[1] ?? null;
        /** @var int|null $height */
        $height = $image[2] ?? null;

        return new ImageData($src, $width, $height);
    }

    /**
     * Retrieves the post's thumbnail.
     *
     * @param string $size
     * @param array|string $attr
     * @return string
     */
    public function getImage(string $size = 'thumbnail', array|string $attr = ''): string
    {
        return get_the_post_thumbnail($this->ID, $size, $attr);
    }

    /**
     * Set the pivot table attribute
     *
     * @param CurtainCallPivot $curtainCallPivot
     * @return $this
     */
    public function setPivot(CurtainCallPivot $curtainCallPivot): static
    {
        $this->setAttribute('ccwp_join', $curtainCallPivot);

        return $this;
    }

    /**
     * Convert the post to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $data */
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
     * @throws UnsettableException
     */
    public function __set(string $key, mixed $value): void
    {
        if (in_array($key, ['attributes', 'meta', 'ccwp_meta', 'wp_post'], true)) {
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
