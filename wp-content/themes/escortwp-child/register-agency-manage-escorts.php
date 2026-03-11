<?php
/*
Template Name: Agency - Manage Escorts
*/

global $taxonomy_profile_url, $taxonomy_agency_url, $taxonomy_profile_name, $taxonomy_profile_name_plural;
$current_user = wp_get_current_user();
if ( get_option( "escortid{$current_user->ID}" ) != $taxonomy_agency_url ) {
    wp_redirect( get_bloginfo( "url" ) );
    exit;
}

if ( isset( $_POST['action'] ) && $_POST['action'] === 'register' ) {
    include get_stylesheet_directory() . '/register-independent-personal-info-process.php';
}

get_header(); ?>

<div class="contentwrapper">
  <div class="body">
    <div class="bodybox">
      <script type="text/javascript">
      jQuery(document).ready(function($) {
        $('.addescort-button').on('click', function(){
          $('.addnewescortform').slideToggle("slow");
          $('.addescort-button').slideToggle();
        });
        $('.addnewescortform .closebtn').on('click', function(){
          $(this).parent().slideToggle("slow");
          $('.addescort-button').slideToggle();
        });
      });
      </script>

      <h3 class="l"><?php printf( esc_html__( 'Manage my %s', 'escortwp' ), $taxonomy_profile_name_plural ); ?></h3>
      <div class="addescort-button pinkbutton rad25 r<?php if ( $agencyid ) { echo ' hide'; } ?>">
        <?php printf( esc_html__( 'Add a new %s', 'escortwp' ), $taxonomy_profile_name ); ?>
      </div>
      <div class="clear"></div>

      <div class="addnewescortform registerform"<?php if ( $agencyid ) { echo ' style="display: block;"'; } ?>>
        <?php closebtn(); ?>
        <?php
          // when admin adds an escort, $agencyid holds the agency's user ID
          $agencyid = $current_user->ID;
          include get_stylesheet_directory() . '/register-independent-personal-information-form.php';
        ?>
      </div><!-- ADD NEW ESCORT FORM -->
      <div class="clear"></div>
    </div><!-- BODY BOX -->

    <div class="clear"></div>

    <div class="bodybox manage_escorts_page">
      <h3 class="l"><?php printf( esc_html__( '%s you added', 'escortwp' ), ucfirst( $taxonomy_profile_name_plural ) ); ?></h3>
      <div class="r">
        <small>
          <?php printf( esc_html__( 'To edit or delete this %s go to the profile page', 'escortwp' ), $taxonomy_profile_name ); ?>.
        </small>
      </div>
      <div class="clear10"></div>

      <img src="<?php bloginfo('template_url'); ?>/i/icon-active.png" alt="" />
      <?php printf( esc_html__( '%s is active (anyone can see it)', 'escortwp' ), $taxonomy_profile_name ); ?><br/>

      <img src="<?php bloginfo('template_url'); ?>/i/icon-inactive.png" alt="" />
      <?php printf( esc_html__( '%s is private (only you can see it)', 'escortwp' ), $taxonomy_profile_name ); ?><br/>

      <?php _e( 'Click icon to change status', 'escortwp' ); ?>
      <div class="clear10"></div>

      <script type="text/javascript">
      jQuery(document).ready(function($) {
        $('.thumb').on('click', '.girlactive', function() {
          var escortid = this.id.replace('girl', '');
          $(this).removeClass('girlactive').addClass('girlinactive');
          $.get("<?php bloginfo('template_url'); ?>/ajax/set-escort-active-inactive.php", { id: escortid });
        });
        $('.thumb').on('click', '.girlinactive', function() {
          var escortid = this.id.replace('girl', '');
          $(this).removeClass('girlinactive').addClass('girlactive');
          $.get("<?php bloginfo('template_url'); ?>/ajax/set-escort-active-inactive.php", { id: escortid });
        });
      });
      </script>

      <?php
      $agency_manage_escorts_page = "yes";
      query_posts( "post_type={$taxonomy_profile_url}&author={$agencyid}&posts_per_page=20&paged={$paged}" );

      if ( have_posts() ) :
        while ( have_posts() ) : the_post();
          $status = get_post_status( get_the_ID() );
          $class = ( $status === 'publish' ) ? 'active' : 'inactive';

          if ( get_post_meta( get_the_ID(), 'notactive', true ) !== '1'
            && get_post_meta( get_the_ID(), 'needs_payment', true ) !== '1'
          ) {
            $agency_manage_escort_buttons = '<i class="rad3 girl' . $class . '" id="girl' . get_the_ID() . '"></i>';
          } else {
            $agency_manage_escort_buttons = '';
          }

          // **Use child-compatible include here**:
          include get_theme_file_path( '/loop-show-profile.php' );
        endwhile;

        $total = ceil( $wp_query->found_posts / 20 );
        dolce_pagination( $total, $paged );
      else :
        printf( esc_html__( 'No %s here yet', 'escortwp' ), $taxonomy_profile_name_plural );
      endif;

      wp_reset_query();
      ?>
      <div class="clear"></div>
    </div><!-- BODY BOX -->
  </div><!-- BODY -->
</div><!-- contentwrapper -->

<?php get_sidebar( "left" ); ?>
<?php get_sidebar( "right" ); ?>
<div class="clear"></div>
<?php get_footer(); ?>
