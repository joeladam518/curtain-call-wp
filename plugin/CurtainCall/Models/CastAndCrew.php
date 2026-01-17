<?php

declare(strict_types=1);

namespace CurtainCall\Models;

use CurtainCall\Models\Traits\HasProductions;
use CurtainCall\Support\Date;
use CurtainCall\Support\Str;
use WP_Query;

/**
 * @property string $name_first
 * @property string $name_last
 * @property string $self_title
 * @property string $birthday
 * @property string $hometown
 * @property string $website_link
 * @property string $facebook_link
 * @property string $twitter_link
 * @property string $instagram_link
 * @property string $fun_fact
 */
class CastAndCrew extends CurtainCallPost
{
    use HasProductions;

    public const POST_TYPE = 'ccwp_cast_and_crew';
    public const META_PREFIX = '_ccwp_cast_crew_';

    /**
     * @var list<string>
     */
    protected array $ccwp_meta = [
        'name_first',
        'name_last',
        'self_title',
        'birthday',
        'hometown',
        'website_link',
        'facebook_link',
        'twitter_link',
        'instagram_link',
        'fun_fact',
    ];

    /**
     * @param WP_Query $query
     * @return array
     */
    public static function getAlphaIndexes(WP_Query $query): array
    {
        /** @var string[] $alphaIndexes */
        $alphaIndexes = [];

        if (!$query->have_posts()) {
            return $alphaIndexes;
        }

        while ($query->have_posts()) {
            $query->the_post();

            /** @var string|null $lastName */
            $lastName = ccwp_get_custom_field('_ccwp_cast_crew_name_last');

            if ($lastName) {
                $alphaIndexes[] = Str::firstLetter($lastName, 'upper');
            }
        }

        wp_reset_postdata();

        return array_unique($alphaIndexes);
    }

    /**
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'description' => __('The Cast and Crew for your productions', CCWP_TEXT_DOMAIN),
            'labels' => [
                'name' => _x('Cast and Crew', 'post type general name', CCWP_TEXT_DOMAIN),
                'singular_name' => _x('Cast or Crew', 'post type singular name', CCWP_TEXT_DOMAIN),
                'add_new' => _x('Add New', 'Cast or Crew', CCWP_TEXT_DOMAIN),
                'add_new_item' => __('Add New cast or crew', CCWP_TEXT_DOMAIN),
                'edit_item' => __('Edit cast or crew', CCWP_TEXT_DOMAIN),
                'new_item' => __('New cast or crew', CCWP_TEXT_DOMAIN),
                'all_items' => __('All cast and crew', CCWP_TEXT_DOMAIN),
                'view_item' => __('View cast or crew', CCWP_TEXT_DOMAIN),
                'search_items' => __('Search cast and crew', CCWP_TEXT_DOMAIN),
                'not_found' => __('No cast or crew found', CCWP_TEXT_DOMAIN),
                'not_found_in_trash' => __('No cast or crew found in the Trash', CCWP_TEXT_DOMAIN),
                'parent_item_colon' => '',
                'menu_name' => __('Cast and Crew', CCWP_TEXT_DOMAIN),
            ],
            'public' => true,
            'show_in_rest' => true,
            'menu_position' => 6,
            'show_in_nav_menus' => true,
            'has_archive' => true,
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'custom-fields',
            ],
            'taxonomies' => [
                'ccwp_cast_crew_productions',
            ],
            'rewrite' => [
                'slug' => 'cast-and-crew',
                'with_front' => true,
                'feeds' => false,
            ],
        ];
    }

    /**
     * Query for castcrew posts
     *
     * @param array $additionalArgs
     * @return WP_Query
     */
    public static function getPosts(array $additionalArgs = []): WP_Query
    {
        return new WP_Query(array_merge([
            'post_type' => [
                'ccwp_cast_and_crew',
                'post',
            ],
            'post_status' => 'publish',
            'meta_key' => '_ccwp_cast_crew_name_last',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'nopaging' => true,
        ], $additionalArgs));
    }

    /**
     * @param Production[] $productions
     * @return array<int, string[]>
     */
    public static function rolesByProductionId(array $productions): array
    {
        /** @var array<int, string[]> $rolesById */
        $rolesById = [];

        foreach ($productions as $production) {
            $id = $production->ID;

            if (!$id) {
                continue;
            }

            $role = $production->getJoinRole();

            if (!$role) {
                continue;
            }

            if (!isset($rolesById[$id])) {
                $rolesById[$id] = [];
            }

            $rolesById[$id][] = $role;
        }

        return $rolesById;
    }

    /**
     * @return string
     */
    public function getBirthPlace(): string
    {
        if (isset($this->birthday)) {
            $birthday = Date::toCarbon($this->birthday);
            $birthday = $birthday ? $birthday->toFormattedDateString() : '';
            return isset($this->hometown)
                ? sprintf(__('Born on %1$s in %2$s.', CCWP_TEXT_DOMAIN), $birthday, $this->hometown)
                : sprintf(__('Born on %s.', CCWP_TEXT_DOMAIN), $birthday);
        }

        if (isset($this->hometown)) {
            return sprintf(__('Born in %s.', CCWP_TEXT_DOMAIN), $this->hometown);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return trim("{$this->name_first} {$this->name_last}");
    }

    /**
     * @return bool
     */
    public function hasSocialMedia(): bool
    {
        return $this->facebook_link || $this->instagram_link || $this->twitter_link;
    }
}
