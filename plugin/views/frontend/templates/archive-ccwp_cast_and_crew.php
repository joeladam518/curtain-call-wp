<?php if (!defined('ABSPATH')) die;

$args = [
    'post_type' => [
        'ccwp_cast_and_crew', 
        'post',
    ],
    'post_status' => 'publish',
    'meta_key' => '_ccwp_cast_crew_name_last',
    'orderby' => 'meta_value',
    'order'   => 'ASC',
    'nopaging' => true,
];

$result = new WP_Query($args);

$full_alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$cast_alpha_indexes = array();
$current_cast_alpha_index = null;
$prev_cast_alpha_index = null;

if ($result->have_posts()) {
    while ($result->have_posts()) {
        $result->the_post();
        $cast_crew_name_first = getCustomField('_ccwp_cast_crew_name_first');
        $cast_crew_name_last = getCustomField('_ccwp_cast_crew_name_last');
        $cast_crew_self_title = getCustomField('_ccwp_cast_crew_self_title');
        if (!empty($cast_crew_name_first) && !empty($cast_crew_name_last) && !empty($cast_crew_self_title)) {
            $cast_alpha_indexes[] = strtoupper(substr($cast_crew_name_last, 0, 1));
        }
    }
}

?>

<?php get_header(); ?>

<div id="content" class="content talent-content-container" role="main">	

    <?php if (!$result->have_posts()) : ?>
        <h2>Sorry!</h2>
        <p>There are currently no cast or crew members in our directory. Please check back soon!</p>
    <?php else: ?>
    
    <h1>Cast and Crew</h1>  
    
    <div class="talent-alpha-index-menu">
        <?php foreach($full_alphabet as $letter) : ?>
            <?php if(in_array($letter, $cast_alpha_indexes)) : ?>
                <a href="#<?php echo $letter ?>"><?php echo $letter ?></a>
            <?php else: ?>
                <span><?php echo $letter ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <div class="talent-directory-container">
        
        <?php while ($result->have_posts()) : $result->the_post(); ?>
    
            <?php  
                //$current_post = get_post();
                //$current_post_custom_fields = get_post_meta($current_post->ID);
                $cast_crew_name_first = getCustomField('_ccwp_cast_crew_name_first');
                $cast_crew_name_last = getCustomField('_ccwp_cast_crew_name_last');
                $cast_crew_self_title = getCustomField('_ccwp_cast_crew_self_title');
            ?>
            
            <?php if (get_post_status() == 'publish' && !empty($cast_crew_name_first) && !empty($cast_crew_name_last) && !empty($cast_crew_self_title)): ?>
            
                <?php $current_cast_alpha_index = strtoupper(substr($cast_crew_name_last, 0, 1)); ?>
            
                <?php if ($current_cast_alpha_index != $prev_cast_alpha_index) : ?>
                    </div>
                    <h3 class="talent-alpha-index-header" id="<?php echo $current_cast_alpha_index; ?>">
                        <?php echo $current_cast_alpha_index; ?>
                    </h3>
                    <div class="talent-directory-row">
                <?php endif; ?>
                    
                    <div class="talent-directory-member">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="talent-profile-image">
                                <a href="<?php echo get_post_permalink(); ?>">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="talent-profile-details">
                            <h3 class="talent-name">
                                <a href="<?php echo get_post_permalink(); ?>">
                                    <?php echo $cast_crew_name_first . ' ' . $cast_crew_name_last; ?>
                                </a>
                            </h3>
                            <h5 class="talent-role"><?php echo $cast_crew_self_title; ?></h5>
                        </div>
                    </div>
                
                <?php $prev_cast_alpha_index = $current_cast_alpha_index; ?>
            
            <?php endif; // content is visible ?>

        <?php endwhile; // while loop ?>

    </div>

<?php endif; // if / else is there content?? ?>

<?php get_footer(); ?>
