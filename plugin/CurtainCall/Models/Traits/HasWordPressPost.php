<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use CurtainCall\Data\ImageData;
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

    public function getExcerpt(): string
    {
        return get_the_excerpt($this->wp_post) ?: '';
    }

    public function theExcerpt(): void
    {
        /* @mago-ignore lint:no-unescaped-output */
        echo apply_filters('the_excerpt', $this->getExcerpt());
    }

    /**
     * Echoes the post's permalink.
     *
     * @return void
     */
    public function thePermalink(): void
    {
        the_permalink($this->wp_post);
    }

    /**
     * Retrieves the post's permalink.
     *
     * @return string
     */
    public function getPermalink(): string
    {
        return get_permalink($this->wp_post) ?: '';
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
        return get_the_post_thumbnail($this->wp_post, $size, $attr);
    }

    /**
     * Retrieves the post's image data.
     *
     * @param string $size
     * @param bool $icon
     * @return ImageData|null
     */
    public function getImageData(string $size = 'thumbnail', bool $icon = false): ?ImageData
    {
        $imageId = get_post_thumbnail_id($this->wp_post) ?: null;

        if (!$imageId) {
            return null;
        }

        $image = wp_get_attachment_image_src($imageId, $size, $icon);

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
     * Echoes the post's thumbnail.
     *
     * @param string $size
     * @param array|string $attr
     * @return void
     */
    public function theImage(string $size = 'thumbnail', array|string $attr = ''): void
    {
        /* @mago-ignore lint:no-unescaped-output */
        echo $this->getImage($size, $attr);
    }

    /**
     * Checks if the post has an image.
     *
     * @return bool
     */
    public function hasImage(): bool
    {
        return has_post_thumbnail($this->wp_post);
    }

    /**
     * Get the gallery HTML string.
     *
     * @return string
     */
    public function getGallery(): string
    {
        return get_post_gallery($this->wp_post) ?: '';
    }

    /**
     * Echoes the gallery
     *
     * @return void
     */
    public function theGallery(): void
    {
        // @mago-ignore lint:no-unescaped-output
        echo get_post_gallery($this->wp_post);
    }

    /**
     * Get the gallery data.
     *
     * @return array<string, mixed>
     */
    public function getGalleryData(): array
    {
        return get_post_gallery($this->wp_post, false);
    }

    /**
     * Get the post's content
     *
     * @param string|null $moreLinkText
     * @param bool $stripTeaser
     * @return string
     */
    public function getContent(?string $moreLinkText = null, bool $stripTeaser = false): string
    {
        return get_the_content($moreLinkText, $stripTeaser, $this->wp_post) ?: '';
    }

    /**
     * Echo the post's content
     *
     * @return void
     */
    public function theContent(): void
    {
        $content = $this->getContent();
        /** @var string $content */
        $content = apply_filters('the_content', $content);
        /** @var string $content */
        $content = str_replace(']]>', ']]&gt;', $content);

        // @mago-ignore lint:no-unescaped-output
        echo $content;
    }

    /**
     * Echo the post's content without the gallery shortcode
     *
     * @return void
     */
    public function theContentWithoutGallery(): void
    {
        $content = $this->getContent();

        /** @var string[][] $matches */
        $matches = [];
        preg_match_all('~' . get_shortcode_regex() . '~s', $content, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $shortcode) {
                if (count($shortcode) === 3 && $shortcode[2] === 'gallery') {
                    $pos = strpos($content, $shortcode[0]);
                    if ($pos !== false) {
                        $content = substr_replace($content, '', $pos, strlen($shortcode[0]));
                    }
                }
            }
        }

        /** @var string $content */
        $content = apply_filters('the_content', $content);
        /** @var string $content */
        $content = str_replace(']]>', ']]&gt;', $content);

        // @mago-ignore lint:no-unescaped-output
        echo $content;
    }

    /**
     * Set the WordPress post on the CurtainCallPost
     *
     * @param WP_Post $post
     * @return $this
     * @throws InvalidArgumentException
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
