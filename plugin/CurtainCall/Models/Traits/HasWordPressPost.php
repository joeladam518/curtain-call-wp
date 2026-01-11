<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use InvalidArgumentException;
use WP_Post;

trait HasWordPressPost
{
    /** @var list<string> */
    protected static array $wp_post_attributes = [];
    protected WP_Post $wp_post;

    public static function setPostAttributes(): void
    {
        if (!static::$wp_post_attributes) {
            static::$wp_post_attributes = array_keys(get_object_vars(new WP_Post((object) [])));
        }
    }

    /**
     * Determine if the key is a wp post attribute
     *
     * @param string $key
     * @return bool
     */
    protected static function isPostAttribute(string $key): bool
    {
        return in_array($key, static::$wp_post_attributes, true);
    }

    /**
     * Get the WordPress Post
     *
     * @return WP_Post
     */
    public function getPost(): WP_Post
    {
        return $this->wp_post;
    }

    /**
     * Set the WordPress post on the CurtainCallPost
     *
     * @param WP_Post $post
     * @return $this
     */
    protected function setPost(WP_Post $post): static
    {
        if ($post->post_type === static::POST_TYPE) {
            $this->wp_post = $post;
        } else {
            throw new InvalidArgumentException(sprintf(
                "Can't set wp_post. \"%s\" is the wrong post type for %s.",
                $post->post_type,
                static::class,
            ));
        }

        return $this;
    }
}
