<?php
/*
Template Name: Template Videos
*/

global $post, $taxonomy_location_url, $wpdb;

get_header(); ?>

<style>
    
    .video-outer {
        width: 49%;
        padding-right: 1%;
        float: left;
        margin-bottom: 20px;
    }

    @media(max-width:640px){
       .video-outer {
            width: 100%;
            padding-right: 0%;
            float: left;
            margin-bottom: 20px;
        } 
    }

    .name-style, .name-style a {
        background: #30CE73;
        padding: 4px;
        color: #fff;
        text-align: center;
        font-weight: bold;
    }

</style>

<?php  



?>

<div class="contentwrapper">
    <div class="body">
        <div class="bodybox">
            <h2><?php the_title(); ?></h2>
            
            <?php  
            $table = $wpdb->prefix . "posts";
            $result = $wpdb->get_results("SELECT * FROM " . $table . " WHERE post_mime_type = 'video/mp4'");
               
            if (count($result) > 0) {
                foreach ($result as $video_data) {
                    $post = get_post($video_data->post_parent);
                    ?>
                    <div class="video-outer">
                        <video width="100%" height="240" controls>
                            <source src="<?php echo esc_url($video_data->guid); ?>" type="video/mp4">
                            <?php esc_html_e('Your browser does not support the video tag.', 'escortwp'); ?>
                        </video>
                        <div class="name-style">
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php echo esc_html($post->post_title); ?></a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <p><?php esc_html_e('Videos not found!', 'escortwp'); ?></p>
                <?php
            }
            ?>
        </div>
    </div> <!-- BODY -->

</div> <!-- contentwrapper -->

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>
<?php get_footer(); ?>
