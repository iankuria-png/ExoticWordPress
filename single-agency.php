<?php
if ( have_posts() ) :
  while ( have_posts() ) : the_post();

    $current_page_url = get_permalink();
    global $taxonomy_location_url,
           $taxonomy_profile_name, $taxonomy_agency_name,
           $taxonomy_profile_name_plural,
           $taxonomy_profile_url, $taxonomy_agency_url,
           $payment_duration_a,
           $gender_a, $ethnicity_a, $haircolor_a,
           $hairlength_a, $bustsize_a, $build_a,
           $looks_a, $smoker_a, $availability_a,
           $languagelevel_a, $services_a, $currency_a;

    $current_user = wp_get_current_user();
    $userid       = $current_user->ID;
    $userstatus   = get_option( "escortid{$userid}" );
    $thispostid   = get_the_ID();
    $thisposttitle= get_the_title();

    // — Admin / Agency-owner controls —
    if ( current_user_can('level_10') ) {
      $err = $ok = '';

      // Add escort under this agency
      if ( isset($_POST['agencyid']) ) {
        $admin_adding_escort = "yes";
        include locate_template('register-independent-personal-info-process.php');
      }

      // Edit agency profile
      if ( isset($_POST['agency_post_id']) ) {
        $admin_adding_agency = "yes";
        include locate_template('register-agency-personal-info-process.php');
      } else {
        // Agency details for display
        $agency_post_id = get_the_ID();
        $agency         = get_post($agency_post_id);
        $aboutagency    = do_shortcode( substr(
          stripslashes( wp_kses( str_replace("</p><p>", "\n\n", $agency->post_content), [] ) ),
          0, 5000
        ));
        $agencyemail    = get_the_author_meta('user_email');
        $agencyname     = get_the_author_meta('display_name');
        $phone          = get_post_meta( $agency_post_id, "phone", true );
        $website        = get_the_author_meta('user_url');
      }

      // Handle agency subscription upgrade / expiration
      if ( isset($_POST['action']) && $_POST['action'] === 'agencyupgrade' ) {
        if ( isset($_POST['delexpiration']) ) {
          delete_post_meta( get_the_ID(), 'agency_expire' );
          delete_post_meta( get_the_ID(), 'agency_renew' );
          if ( payment_plans('agreg','price') ) {
            update_post_meta( get_the_ID(), 'needs_payment', "1" );
            wp_update_post([ 'ID'=>get_the_ID(), 'post_status'=>'private' ]);
          }
        }
        if ( isset($_POST['expirationperiod']) ) {
          if ( $_POST['profileduration'] ) {
            $expiration = strtotime( "+" . $payment_duration_a[ $_POST['profileduration'] ][2] );
            $avail_time = get_post_meta(get_the_ID(), 'agency_expire', true);
            if ( $avail_time && $avail_time > time() ) {
              $expiration += ($avail_time - time());
            }
            update_post_meta( get_the_ID(), 'agency_expire', $expiration );
          } else {
            delete_post_meta(get_the_ID(), 'agency_expire');
            delete_post_meta(get_the_ID(), 'agency_renew');
          }
        }
      }

      // Mark profile private, unpaid, activate, etc.
      if ( isset($_POST['action']) && $_POST['action'] === 'needs_payment' ) {
        wp_update_post([ 'ID' => get_the_ID(), 'post_status' => 'private' ]);
        update_post_meta( get_the_ID(), 'needs_payment', '1' );
        wp_redirect( get_permalink() ); exit;
      }
      if ( isset($_POST['action']) && $_POST['action'] === 'activateprivateprofile' ) {
        wp_update_post([ 'ID' => get_the_ID(), 'post_status' => 'publish' ]);
        wp_redirect( get_permalink() ); exit;
      }
      if ( isset($_POST['action']) && $_POST['action'] === 'activateunpaidprofile' ) {
        if ( $_POST['profileduration'] ) {
          $expiration = strtotime( "+" . $payment_duration_a[ $_POST['profileduration'] ][2] );
          $avail_time = get_post_meta(get_the_ID(), 'agency_expire', true);
          if ( $avail_time && $avail_time > time() ) {
            $expiration += ($avail_time - time());
          }
          update_post_meta( get_the_ID(), 'agency_expire', $expiration );
        }
        wp_update_post([ 'ID'=>get_the_ID(), 'post_status'=>'publish' ]);
        delete_post_meta( get_the_ID(), "needs_payment" );

        // Also publish any child profiles that were waiting on agency payment
        $child_q = new WP_Query([
          'post_type'      => $taxonomy_profile_url,
          'posts_per_page' => -1,
          'author'         => get_the_author_meta('ID'),
          'meta_query'     => [
            ['key'=>'needs_ag_payment','value'=>'1','type'=>'numeric','compare'=>'='],
            ['key'=>'needs_payment','value'=>'1','type'=>'numeric','compare'=>'!='],
          ]
        ]);
        if ( $child_q->have_posts() ) {
          while ( $child_q->have_posts() ) {
            $child_q->the_post();
            wp_update_post([ 'ID'=>get_the_ID(), 'post_status'=>'publish' ]);
          }
        }
        wp_reset_postdata();

        wp_redirect( get_permalink(get_the_ID()) ); exit;
      }
    }

    // — Delete agency account —
    if ( is_user_logged_in()
      && isset($_POST['action']) && $_POST['action']==="deleteagency"
      && (
           ( get_the_author_meta('ID')=== $userid && $userstatus===$taxonomy_agency_url )
        || current_user_can('level_10')
         )
    ) {
      delete_agency(get_the_ID());
      wp_redirect( home_url() ); exit;
    }

    // — Contact form —
    if ( isset($_POST['action']) && $_POST['action']==="contactform" ) {
      if ( $_POST['emails'] ) {
        $err .= ".";
      }
      if ( get_option('recaptcha_sitekey')
        && get_option('recaptcha_secretkey')
        && get_option("recaptcha5")
        && !is_user_logged_in()
      ) {
        $err .= verify_recaptcha();
      }
      if ( is_user_logged_in() ) {
        $contactformname  = $current_user->display_name;
        $contactformemail = $current_user->user_email;
      } else {
        $contactformname  = get_option("email_sitename");
        $contactformemail = $_POST['contactformemail'];
        if ( $contactformemail ) {
          if ( !is_email($contactformemail) ) {
            $err .= __('Your email address seems to be wrong', 'escortwp') . "<br />";
          }
        } else {
          $err .= __('Your email is missing', 'escortwp') . "<br />";
        }
      }
      $contactformmess = substr( sanitize_textarea_field($_POST['contactformmess']), 0, 5000 );
      if ( !$contactformmess ) {
        $err .= __('You need to write a message','escortwp') . "<br />";
      }
      if ( !$err ) {
        $body = sprintf(
          __('Hello %1$s,', 'escortwp') . "\n\n" .
          __('Someone sent you a message from %2$s:', 'escortwp') . "\n" .
          '%3$s' . "\n\n" .
          __('Sender information:', 'escortwp') . "\n" .
          __('Name: %4$s', 'escortwp') . "\n" .
          __('Email: %5$s', 'escortwp') . "\n\n" .
          __('Message:', 'escortwp') . "\n" .
          '%6$s',
          get_the_author_meta('display_name'),
          get_option("email_sitename"),
          get_permalink(get_the_ID()),
          $contactformname,
          $contactformemail,
          $contactformmess
        );
        dolce_email(
          $contactformname,
          $contactformemail,
          get_the_author_meta('user_email'),
          sprintf(__('Message from %s','escortwp'), get_option("email_sitename")),
          $body
        );
        $ok = __('Message sent','escortwp');
      }
    }

    // — Reviews (members only) —
    if ( $userstatus==="member" || current_user_can('level_10') ) {
      if ( isset($_POST['action']) && $_POST['action']==='addreview' ) {
        $rateagency = (int) $_POST['rateagency'];
        if ( $rateagency<1 || $rateagency>5 ) {
          $err .= sprintf(
            esc_html__('The %s rating is wrong. Please select again.','escortwp'),
            $taxonomy_agency_name
          ) . "<br />";
          unset($rateagency);
        }
        $reviewtext = substr( stripslashes( wp_kses($_POST['reviewtext'], []) ), 0, 1000 );
        if ( !$reviewtext ) {
          $err .= __('You didn\'t write a review','escortwp') . "<br />";
        }
        if ( !$err ) {
          // Create review post
          $reviewstatus = get_option("manactivag")==="1" ? "draft" : "publish";
          $reviews_cat   = term_exists('Reviews','category');
          if ( !$reviews_cat ) {
            wp_insert_term('Reviews','category',['description'=>'Reviews']);
            $reviews_cat = term_exists('Reviews','category');
          }
          $rid = wp_insert_post([
            'post_title'   => sprintf(__('review for %s','escortwp'), get_the_title()),
            'post_content' => $reviewtext,
            'post_status'  => $reviewstatus,
            'post_author'  => $userid,
            'post_category'=> [ $reviews_cat['term_id'] ],
            'post_type'    => 'review',
            'ping_status'  => 'closed'
          ]);
          update_post_meta( $rid, 'rateagency', $rateagency );
          update_post_meta( $rid, 'agencyid',    get_the_ID() );
          update_post_meta( $rid, 'reviewfor',   'agency' );

          // Notify admin if moderation or immediate
          $email_title = get_option("manactivag")==="1"
            ? sprintf(__('A new review is waiting approval on %s','escortwp'), get_option("email_sitename"))
            : sprintf(__('Someone wrote an %s review on %s','escortwp'), $taxonomy_agency_name, get_option("email_sitename"));

          $admin_url = admin_url("post.php?post={$rid}&action=edit");
          $body = sprintf(
            __('Hello,'."\n\n".'Read/Edit the review here: %s'),
            $admin_url
          );
          if ( get_option("ifemail5")==="1" || get_option("manactivag")==="1" ) {
            dolce_email(null,null, get_bloginfo("admin_email"), $email_title, $body);
          }

          wp_redirect( get_permalink(get_the_ID()) . "?postreview=ok" );
          exit;
        }
      }
    }

    get_header();
?>
  <div class="contentwrapper">
    <div class="body agency-page">

      <?php if ( current_user_can('level_10') ) : ?>
        <!-- Admin Add/Edit blocks… -->
        <div class="bodybox girlsingle agency_options_add_profile hide">
          <div class="registerform">
            <?php closebtn(); ?>
            <div class="clear10"></div>
            <?php
              $agencyid = get_the_author_meta('ID');
              $admin_adding_escort = "yes";
              include locate_template('register-independent-personal-information-form.php');
            ?>
          </div>
        </div>

        <div class="bodybox girlsingle agency_options_edit_agency hide">
          <div class="registerform">
            <?php closebtn(); ?>
            <div class="clear10"></div>
            <?php
              $agency_post_id = get_the_ID();
              $admin_editing_agency = "yes";
              include locate_template('register-agency-personal-information-form.php');
            ?>
          </div>
        </div>

        <div class="bodybox girlsingle agency_options_add_logo hide">
          <?php closebtn(); ?>
          <h3><?php printf(esc_html__('Upload/Edit %s Logo','escortwp'), $taxonomy_agency_name); ?></h3>
          <script>
            jQuery(function($){
              $('#file_upload').uploadifive({
                'auto': true,
                'buttonClass': 'pinkbutton rad25',
                'buttonText': '<?php _e('Upload logo','escortwp'); ?>',
                'fileSizeLimit':'<?=get_option("maximguploadsize")?>MB',
                'fileType':'image/*',
                'formData':{ 'folder': '<?php echo get_post_meta(get_the_ID(),"secret",true); ?>' },
                'multi':false,
                'queueID':'upload-queue',
                'removeCompleted':true,
                'uploadScript':'<?php bloginfo("template_url"); ?>/register-agency-upload-logo-process.php',
                'onQueueComplete': function(){ location.reload(); }
              });
              $('.upload_photos_form .profile-img-thumb .button-delete').on('click',function(){
                var id = $(this).closest('.profile-img-thumb').data('id');
                $.get('<?php bloginfo("template_url"); ?>/ajax/delete-agency-logo.php', { id: id }, function(){
                  location.reload();
                });
              });
            });
          </script>
          <div class="upload_photos_form">
            <div class="upload_photos_button">
              <input id="file_upload" name="file_upload" type="file" />
            </div>
            <div id="upload-queue"></div>
            <div id="status-message" class="text-center">
              <?php printf(esc_html__('Click to select your %s logo','escortwp'), $taxonomy_agency_name); ?>
            </div>
            <div class="clear20"></div>
            <h4 class="logo-used"><?php printf(esc_html__('The %s logo you will be using','escortwp'), $taxonomy_agency_name); ?></h4>
            <?php
              $photos = get_children([
                'post_parent'=>get_the_ID(),
                'post_type'=>'attachment',
                'post_mime_type'=>'image',
                'orderby'=>'menu_order ID',
                'order'=>'ASC'
              ]);
              foreach($photos as $photo) {
                $thumb = wp_get_attachment_image_src($photo->ID,'listings-thumb');
                echo '<div class="profile-img-thumb" data-id="'.$photo->ID.'">';
                echo '<img src="'.$thumb[0].'" alt="" />';
                echo '<span class="button-delete">&times;</span>';
                echo '</div>';
              }
            ?>
          </div>
        </div>

        <div class="bodybox girlsingle agency_options_delete hide">
          <div class="registerform text-center">
            <?php closebtn(); ?>
            <form method="post">
              <p><?php printf(__('Are you sure you want to delete this %s account?','escortwp'), $taxonomy_agency_name); ?></p>
              <p><?php _e('All associated profiles will also be deleted.','escortwp'); ?></p>
              <input type="hidden" name="action" value="deleteagency"/>
              <button type="submit" class="redbutton"><?php printf(__('Delete %s','escortwp'), $taxonomy_agency_name); ?></button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="bodybox girlsingle agency-profile" itemscope itemtype="http://schema.org/Brand">
        <script>
          jQuery(function($){
            $('.sendemail').click(function(){
              $('.escortcontact, .sendemail').toggle();
            });
            $('.addreview').click(function(){
              $('.addreviewform, .addreview').slideToggle();
              $('html,body').animate({scrollTop:$('#addreviewsection').offset().top},'slow');
            });
            <?php if ( $_GET['postreview']==='ok' ) : ?>
              $('.addreview').trigger('click');
            <?php endif; ?>
          });
        </script>

        <div class="profile-header">
          <div class="profile-header-name">
            <?php if ( get_post_status()==='private' ) : ?>
              <div class="girlsinglelabels text-center">
                <span class="redbutton"><?php _e('Private profile','escortwp'); ?></span>
              </div>
            <?php endif; ?>
            <h3 itemprop="name"><?php the_title(); ?></h3>
            <?= show_online_label_html(get_the_author_meta('ID')) ?>
          </div>
        </div>

        <?php
          $main_photo = get_children([
            'post_parent'=>get_the_ID(),
            'post_type'=>'attachment',
            'post_mime_type'=>'image',
            'numberposts'=>1
          ]);
          if ( $main_photo ) {
            $photo = reset($main_photo);
            $thumb = wp_get_attachment_image_src($photo->ID,'main-image-thumb');
            echo '<div class="bigimage l"><img src="'.$thumb[0].'" alt="'.get_the_title().'" /></div>';
          }
        ?>
        <div class="agencydetails<?php echo empty($thumb)?' agencydetails-noimg':''; ?>">
          <?php if ( get_the_author_meta('user_url') ) : ?>
            <b><?php _e('Website','escortwp'); ?>:</b>
            <span><a href="<?php echo esc_url(get_the_author_meta('user_url')); ?>" target="_blank" rel="nofollow">
              <?php echo esc_html(str_ireplace(['http://','www.'],'',get_the_author_meta('user_url'))); ?>
            </a></span><br>
          <?php endif; ?>
          <b><?php _e('Phone','escortwp'); ?>:</b>
          <span><?php echo esc_html(get_post_meta(get_the_ID(),'phone',true)); ?></span><br>
          <?php
            $loc = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
            if ($loc) {
              echo '<b>'.__('City','escortwp').':</b><span>'.$loc[0]->name.'</span><br>';
              $state = get_term($loc[0]->parent, $taxonomy_location_url);
              if ($state && !$state->is_wp_error()) {
                echo '<b>'.__('State','escortwp').':</b><span>'.$state->name.'</span><br>';
                $country = get_term($state->parent, $taxonomy_location_url);
                if ($country && !$country->is_wp_error()) {
                  echo '<b>'.__('Country','escortwp').':</b><span>'.$country->name.'</span><br>';
                }
              }
            }
          ?>
          <b><?php echo ucfirst($taxonomy_profile_name_plural); ?>:</b>
          <span><?php echo show_post_count(get_the_author_meta('ID')); ?></span><br>
          <?php if ( get_option("hide1")!=="1" ) : ?>
            <b><?php _e('Rating','escortwp'); ?>:</b>
            <div class="starrating"><div class="starrating_stars star<?php echo get_agency_rating(get_the_ID()); ?>"></div></div><br>
          <?php endif; ?>

          <div class="clear10"></div>
          <?php if ( get_option("hide1")!=="1" ) : ?>
            <div class="addreview pinkbutton"><span class="icon-plus-circled"></span> <?php _e('Add Review','escortwp'); ?></div>
          <?php endif; ?>
          <div class="sendemail pinkbutton"><span class="icon-mail"></span>
            <?php printf(__('Contact this %s','escortwp'), $taxonomy_agency_name); ?>
          </div>
          <?php if ( !empty($err) ) : ?>
            <div class="err"><?php echo wp_kses_post($err); ?></div>
          <?php elseif ( !empty($ok) ) : ?>
            <div class="ok"><?php echo wp_kses_post($ok); ?></div>
          <?php endif; ?>

          <div class="clear10"></div>
          <?php include locate_template('send-email-form.php'); ?>

        </div><!--/.agencydetails-->

        <div class="clear20"></div>
        <div class="agency-desc<?php echo empty($thumb)?' col50':''; ?>">
          <h4><?php printf(__('About the %s','escortwp'), $taxonomy_agency_name); ?>:</h4>
          <div itemprop="description"><?php the_content(); ?></div>
          <?php if ( current_user_can('level_10') ) : ?>
            <?php edit_post_link(__('Edit in WordPress','escortwp')); ?>
          <?php endif; ?>
        </div>

        <div class="clear"></div>
        <?php if ( get_option('hitcounter2') ) echo esc_page_hit_counter(get_the_ID()); ?>

      </div><!--/.agency-profile-->

      <!-- Profiles added by this agency -->
      <div class="bodybox">
        <h3><?php printf(__('%1$s added by this %2$s','escortwp'),
            ucfirst($taxonomy_profile_name_plural),
            $taxonomy_agency_name
          );?></h3>
        <div class="clear10"></div>
        <?php
          $paged = get_query_var('paged') ?: 1;
          $profiles = new WP_Query([
            'author'         => get_the_author_meta('ID'),
            'post_type'      => $taxonomy_profile_url,
            'posts_per_page' => 20,
            'paged'          => $paged,
            'orderby'        => 'ID',
            'order'          => 'DESC',
          ]);
          if ( $profiles->have_posts() ):
            while ( $profiles->have_posts() ) : $profiles->the_post();
              include locate_template('loop-show-profile.php');
            endwhile;
            dolce_pagination(
              $profiles->max_num_pages,
              $paged,
              get_option('permalink_structure') ? 'paged/%#%/' : '&page=%#%/',
              $current_page_url
            );
          else:
            printf(__('No %s here yet','escortwp'), $taxonomy_profile_name_plural);
          endif;
          wp_reset_postdata();
        ?>
      </div>

      <!-- Reviews list & form -->
      <?php if ( get_option("hide1")!=="1" ) : ?>
        <div class="bodybox agency-reviews-bodybox">
          <h4><?php printf(__('%s reviews','escortwp'), ucwords($taxonomy_agency_name)); ?></h4>
          <div class="addreview pinkbutton"><span class="icon-plus-circled"></span><?php _e('Add Review','escortwp'); ?></div>
          <div id="addreviewsection" class="clear10"></div>
          <div class="addreviewform hide registerform">
            <?php
              if ( $_GET['postreview']==='ok' ) {
                echo '<div class="ok">'.__('Thank you for posting.','escortwp').'</div>';
              }
              if ( did_user_post_review($userid, get_the_ID()) ) {
                echo '<div class="err">'.sprintf(
                  __('You can\'t post more than one review for the same %s.','escortwp'),
                  $taxonomy_agency_name
                ).'</div>';
              } elseif ( ($userstatus==="member"||current_user_can('level_10')) && !did_user_post_review($userid, get_the_ID()) ) {
                if ( !empty($err) ) echo '<div class="err">'.wp_kses_post($err).'</div>';
                ?>
                <form action="<?php echo get_permalink()."#addreview";?>" method="post" class="form-styling">
                  <?php closebtn(); ?>
                  <input type="hidden" name="action" value="addreview" />
                  <div class="form-label">
                    <label for="rateagency"><?php printf(__('Rate the %s','escortwp'), $taxonomy_agency_name);?>*</label>
                  </div>
                  <div class="form-input form-input-rating">
                    <?php for($i=5;$i>=1;$i--): ?>
                      <label>
                        <input type="radio" name="rateagency" value="<?php echo $i;?>" <?php checked($rateagency,$i);?> />
                        <?php echo $i;?> – <?php echo esc_html(get_agency_rating_label($i)); ?>
                      </label><div class="clear"></div>
                    <?php endfor;?>
                  </div>
                  <div class="formseparator"></div>
                  <div class="form-label">
                    <label for="reviewtext"><?php _e('Comment','escortwp');?>*</label>
                  </div>
                  <div class="form-input">
                    <textarea name="reviewtext" id="reviewtext" rows="7"><?php echo esc_textarea($reviewtext);?></textarea>
                    <small><?php _e('HTML will be removed','escortwp');?></small>
                    <div class="charcount hides"><div id="barbox"><div id="bar"></div></div><div id="count"></div></div>
                  </div>
                  <div class="formseparator"></div>
                  <div class="text-center">
                    <button type="submit" class="pinkbutton"><?php _e('Add Review','escortwp');?></button>
                  </div>
                </form>
              <?php } else {
                if ( !is_user_logged_in() ) {
                  echo '<div class="err">'.sprintf(
                    __('You need to <a href="%1$s">register</a> or <a href="%2$s">login</a> to post a review.','escortwp'),
                    get_permalink(get_option('main_reg_page_id')),
                    wp_login_url(get_permalink())
                  ).'</div>';
                } else {
                  echo '<div class="err">'.__('Your user type is not allowed to post a review here','escortwp').'</div>';
                }
              }
            ?>
          </div>

          <?php
            $rev_q = new WP_Query([
              'post_type'=>'review',
              'posts_per_page'=>-1,
              'meta_query'=>[ ['key'=>'agencyid','value'=>$thispostid,'compare'=>'='] ]
            ]);
            if ( $rev_q->have_posts() ) {
              while ( $rev_q->have_posts() ) {
                $rev_q->the_post();
                $r = get_post_meta(get_the_ID(),'rateagency',true);
                ?>
                <div class="review-wrapper">
                  <div class="starrating"><div class="starrating_stars star<?php echo $r;?>"></div></div>
                  <i><?php _e('Added by','escortwp');?></i>
                  <b><?php echo esc_html( substr(get_the_author_meta('display_name'),0,2) );?>…</b>
                  <i><?php _e('on','escortwp');?></i>
                  <b><?php the_time("d F Y");?></b>
                  <p><?php the_content();?></p>
                </div>
                <div class="clear30"></div>
                <?php
              }
              wp_reset_postdata();
            } else {
              echo '<div class="text-center">'.__('No reviews yet','escortwp').'</div>';
            }
          ?>
        </div>
      <?php endif; ?>

    </div><!--/.body-->
  </div><!--/.contentwrapper-->

  <?php get_sidebar("left"); ?>
  <?php get_sidebar("right"); ?>
  <div class="clear"></div>
<?php get_footer(); ?>
