<?php
/*
Template Name: Nav Blacklisted Escorts
*/

global $taxonomy_location_url, $taxonomy_profile_name, $taxonomy_profile_name_plural;
$current_user = wp_get_current_user();

if ( isset( $_POST['action'] ) && 'search' === $_POST['action'] ) {
    $search_query = [];

    $yourname = substr( wp_filter_nohtml_kses( $_POST['yourname'] ), 0, 50 );
    if ( $yourname ) {
        $search_query[] = [
            'key'     => 'name',
            'value'   => $yourname,
            'compare' => '=',
        ];
    }

    $phone = substr( wp_filter_nohtml_kses( $_POST['phone'] ), 0, 50 );
    if ( $phone ) {
        $search_query[] = [
            'key'     => 'phone',
            'value'   => $phone,
            'compare' => '=',
        ];
    }

    $escortemail = trim( $_POST['escortemail'] );
    if ( $escortemail ) {
        if ( ! is_email( $escortemail ) ) {
            $err .= sprintf(
                esc_html__( 'The %s email seems to be wrong.', 'escortwp' ),
                $taxonomy_profile_name
            ) . '<br />';
        } else {
            $search_query[] = [
                'key'     => 'email',
                'value'   => $escortemail,
                'compare' => '=',
            ];
        }
    }

    if ( ! empty( $_POST['country'] ) && (int) $_POST['country'] > 0 ) {
        $country = (int) $_POST['country'];
        if ( ! term_exists( $country, $taxonomy_location_url ) ) {
            $err .= __( "The country you selected doesn't exist in our database", 'escortwp' ) . '<br />';
        } else {
            $search_query[] = [
                'key'     => 'country',
                'value'   => $country,
                'compare' => '=',
            ];
        }
    }

    if ( ! empty( $_POST['city'] ) ) {
        $city = substr( wp_filter_nohtml_kses( $_POST['city'] ), 0, 50 );
        $search_query[] = [
            'key'     => 'city',
            'value'   => $city,
            'compare' => '=',
        ];
    }

    if ( ! empty( $_POST['gender'] ) ) {
        $gender = (int) $_POST['gender'];
        if ( empty( $gender_a[ $gender ] ) ) {
            $err .= __( 'Please choose a gender', 'escortwp' ) . '<br />';
        } else {
            $search_query[] = [
                'key'     => 'gender',
                'value'   => $gender,
                'compare' => '=',
            ];
        }
    }

    if ( empty( $search_query ) ) {
        $err = __( 'You have to fill in at least one search field', 'escortwp' );
    }

    unset( $yourname, $phone, $escortemail, $country, $city, $gender );
}

get_header();
?>

<div class="contentwrapper">
  <div class="body">
    <div class="bodybox">

      <script>
      jQuery(function($){
        $('.searchescort').click(function(){
          $('.searchescortform, .show-profiles').slideToggle('slow');
          $(this).slideToggle();
        });
        $('.searchescortform .closebtn').click(function(){
          $(this).parent().slideToggle();
          $('.searchescort, .show-profiles').slideToggle();
        });
      });
      </script>

      <?php if ( empty( $search_query ) ) : ?>
        <h3 class="l">
          <?php printf( esc_html__( 'Blacklisted %s', 'escortwp' ), $taxonomy_profile_name_plural ); ?>
        </h3>
      <?php else : ?>
        <h3 class="l"><?php _e( 'Search results', 'escortwp' ); ?></h3>
      <?php endif; ?>

      <div class="searchescort pinkbutton rad3 r">
        <?php printf( esc_html__( 'Search %s', 'escortwp' ), $taxonomy_profile_name_plural ); ?>
      </div>
      <div class="clear10"></div>

      <div class="searchescortform"<?php if ( ! empty( $err ) && 'search' === $_POST['action'] ) echo ' style="display:block;"'; ?>>
        <?php closebtn(); ?>
        <?php
          $search_page = true;
          if ( ! empty( $err ) && 'search' === $_POST['action'] ) {
            echo '<div class="err rad25">' . $err . '</div>';
          }
          include get_stylesheet_directory() . '/blacklisted-escorts-form.php';
          unset( $search_page );
        ?>
      </div><!-- .searchescortform -->

      <div class="show-profiles">
      <?php if ( ! empty( $search_query ) ) : ?>

        <?php
        $args = [
          'post_type'      => 'b' . $taxonomy_profile_url,
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'orderby'        => 'title',
          'order'          => 'ASC',
          'meta_query'     => $search_query,
        ];
        query_posts( $args );
        ?>

        <?php if ( have_posts() ) : ?>
        <table class="listagencies rad3">
          <tr class="trhead rad3">
            <th class="rad3"><?php _e( 'Name', 'escortwp' ); ?></th>
            <th class="rad3"><?php _e( 'Country', 'escortwp' ); ?></th>
            <th class="rad3"><?php _e( 'City', 'escortwp' ); ?></th>
            <th class="rad3"><?php _e( 'Date added', 'escortwp' ); ?></th>
          </tr>
          <?php
          while ( have_posts() ) : the_post();
            $city_term    = get_term( get_post_meta( get_the_ID(), 'city', true ), $taxonomy_location_url );
            $country_term = get_term( get_post_meta( get_the_ID(), 'country', true ), $taxonomy_location_url );
          ?>
          <tr class="agencytr">
            <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
            <td><?php echo esc_html( $country_term->name ); ?></td>
            <td><?php echo esc_html( $city_term->name ); ?></td>
            <td><?php the_time( 'd F Y' ); ?></td>
          </tr>
          <?php endwhile; ?>
        </table>
        <?php else: ?>
          <?php printf( esc_html__( 'No %s found', 'escortwp' ), $taxonomy_profile_name_plural ); ?>
        <?php endif; ?>
        <?php wp_reset_query(); ?>

      <?php else: ?>

        <?php
        $posts_per_page = 40;
        $args = [
          'post_type'      => 'b' . $taxonomy_profile_url,
          'posts_per_page' => $posts_per_page,
          'orderby'        => 'title',
          'order'          => 'ASC',
        ];
        query_posts( $args );

        if ( have_posts() ) :
          while ( have_posts() ) : the_post();
            include get_stylesheet_directory() . '/loop-show-profile.php';
          endwhile;

          $total = ceil( $wp_query->found_posts / $posts_per_page );
          dolce_pagination( $total, $paged );
        else:
          printf( esc_html__( 'No %s here yet', 'escortwp' ), $taxonomy_profile_name_plural );
        endif;
        wp_reset_query();
        ?>

      <?php endif; ?>
      </div><!-- .show-profiles -->

      <div class="clear"></div>
    </div><!-- .bodybox -->
  </div><!-- .body -->
</div><!-- .contentwrapper -->

<?php get_sidebar( 'left' ); ?>
<?php get_sidebar( 'right' ); ?>
<div class="clear"></div>

<?php get_footer(); ?>
