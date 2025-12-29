<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use CurtainCall\Exceptions\PostNotFoundException;
use InvalidArgumentException;
use WP_Post;

trait HasWordPressPost
{
    protected WP_Post $wp_post;
    /** @var list<string> */
    protected array $wp_post_attributes = [];

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
     * Fetch the WordPress Post
     *
     * @param int $postId
     * @return WP_Post
     * @throws PostNotFoundException
     */
    protected function fetchPost(int $postId): WP_Post
    {
        $wpdb = ccwp_get_wpdb();

        $query = "SELECT * FROM {$wpdb->posts} WHERE `ID` = %d AND `post_type` = %s LIMIT 1";
        $sql = $wpdb->prepare($query, $postId, static::POST_TYPE);
        $post = $wpdb->get_row($sql);

        if (!$post) {
            throw new PostNotFoundException("Failed to fetch post. id #{$postId} post_type: " . static::POST_TYPE);
        }

        $post = sanitize_post($post, 'raw');

        return new WP_Post($post);
    }

    /**
     * Determine if the key is a wp post attribute
     *
     * @param string $key
     * @return bool
     */
    protected function isPostAttribute(string $key): bool
    {
        return in_array($key, $this->wp_post_attributes, true);
    }

    /**
     * Load the WordPress Post
     *
     * @param WP_Post|string|int $post
     * @return void
     * @throws PostNotFoundException|InvalidArgumentException
     */
    protected function loadPost(int|string|WP_Post $post): void
    {
        if (is_numeric($post) && (int) $post > 0) {
            $post = $this->fetchPost((int) $post);
        }

        if (!$post instanceof WP_Post) {
            throw new InvalidArgumentException('Can not load $post it must be an int or an instance of WP_Post.');
        }

        $this->setPost($post);
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

        $this->wp_post_attributes = array_keys(get_object_vars($this->wp_post));

        return $this;
    }
}
