<?php

namespace CurtainCall\PostTypes;

use CurtainCall\PostTypes\Traits\HasProductions;
use CurtainCall\Support\Date;
use WP_Query;

/**
 * @property string $name_first'
 * @property string $name_last'
 * @property string $self_title'
 * @property string $birthday'
 * @property string $hometown'
 * @property string $website_link'
 * @property string $facebook_link'
 * @property string $twitter_link'
 * @property string $instagram_link'
 * @property string $fun_fact
 */
class CastAndCrew extends CurtainCallPost
{
    use HasProductions;

    const POST_TYPE = 'ccwp_cast_and_crew';
    const META_PREFIX = '_ccwp_cast_crew_';

    /** @var array|string[] */
    protected $ccwp_meta_keys = [
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
        $alpha_indexes = [];

        if (!$query->have_posts()) {
            return $alpha_indexes;
        }

        while ($query->have_posts()) {
            $query->the_post();
            $name_last = getCustomField('_ccwp_cast_crew_name_last');
            if (!empty($name_last)) {
                $alpha_indexes[] = strtoupper(substr($name_last, 0, 1));
            }
        }

        wp_reset_postdata();

        return array_unique($alpha_indexes);
    }

    /**
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'description'   => 'The Cast and Crew for you productions',
            'labels'        => [
                'name'               => _x('Cast and Crew', 'post type general name'),
                'singular_name'      => _x('Cast or Crew', 'post type singular name'),
                'add_new'            => _x('Add New', 'Cast or Crew'),
                'add_new_item'       => __('Add New cast or crew'),
                'edit_item'          => __('Edit cast or crew'),
                'new_item'           => __('New cast or crew'),
                'all_items'          => __('All cast and crew'),
                'view_item'          => __('View cast and crew'),
                'search_items'       => __('Search cast and crew'),
                'not_found'          => __('No cast or crew found'),
                'not_found_in_trash' => __('No cast or crew found in the Trash'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Cast and Crew',
            ],
            'public'        => true,
            'menu_position' => 6,
            'show_in_nav_menus' => true,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            'taxonomies'    => [
                'ccwp_cast_crew_productions',
            ],
            'has_archive'   => true,
            'rewrite'       => [
                'slug' => 'cast-and-crew',
                'with_front' => true,
                'feeds' => false,
            ],
        ];
    }

    /**
     * @return WP_Query
     */
    public static function getPosts(): WP_Query
    {
        return new WP_Query([
            'post_type' => [
                'ccwp_cast_and_crew',
                'post',
            ],
            'post_status' => 'publish',
            'meta_key' => '_ccwp_cast_crew_name_last',
            'orderby' => 'meta_value',
            'order'   => 'ASC',
            'nopaging' => true,
        ]);
    }

    /**
     * @param array $productions
     * @return array
     */
    public static function rolesByProductionId(array $productions): array
    {
        $roles_by_id = [];
        /** @var Production $production */
        foreach ($productions as $production) {
            if (!isset($roles_by_id[$production->ID])) {
                $roles_by_id[$production->ID] = [];
            }
            $roles_by_id[$production->ID][] = $production->ccwp_join->role;
        }

        return $roles_by_id;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        if (isset($this->name_last)) {
            return "{$this->name_first} {$this->name_last}";
        }

        return $this->name_first;
    }

    /**
     * @return string
     */
    public function getBirthPlace(): string
    {
        $birthplace = '';
        if (isset($this->birthday)) {
            $birthday = Date::toCarbon($this->birthday);
            $birthday = $birthday ? $birthday->toFormattedDateString() : '';
            $birthplace .= "Born on {$birthday}";
            if (isset($this->hometown)) {
                $birthplace .= ' in ' . $this->hometown;
            }
            $birthplace .= '.';
        } else if (isset($this->hometown)) {
            $birthplace .= 'Born in ' . $this->hometown . '.';
        }

        return $birthplace;
    }

    /**
     * @return bool
     */
    public function hasSocialMedia(): bool
    {
        return isset($this->facebook_link) || isset($this->instagram_link) || isset($this->twitter_link);
    }
}
