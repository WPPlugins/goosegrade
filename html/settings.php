   <?php global $goosegrade_settings; ?>
   <?php global $goosegrade_is_wpmu; ?>
   <?php global $goosegrade_api; ?>

   <div id="goosegrade-settings" class="wrap">
   <h2>gooseGrade</h2>

   <?php if (isset($_POST['submit'])) { ?>
   <?php // we need to update the settings here ?>

      <?php $goosegrade_error = false; ?>
      <?php if ($_POST['token1'] != $_POST['token2']) { ?>
         <div class="goosegrade-error">
            <?php $goosegrade_error = true; ?>
            <?php _e( 'Sorry, the two tokens/passwords you have provided do not match.', 'goosegrade' ); ?>
         </div>
      <?php } else if (!isset($_POST['username']) || (strlen($_POST['username']) == 0)) { ?>
         <div class="goosegrade-error">
            <?php $goosegrade_error = true; ?>
            <?php _e( 'Sorry, you must provide a valid username.', 'goosegrade' ); ?>
         </div>
      <?php } else if (strlen($_POST['token1']) == 0) { ?>
         <div class="goosegrade-error">
            <?php $goosegrade_error = true; ?>
            <?php _e( 'Sorry, you must provide a token or a password to use the gooseGrade plugin.', 'goosegrade' ); ?>
         </div>
      <?php } else { ?>
      	<?php $test_api = new goosegrade( $_POST['username'], $_POST['token1'] ); ?>
      	<?php $user_info = $test_api->get_user_info( $_POST['username'] ); ?>
      	<?php if ( $user_info->status == 1 ) { ?>
            <?php $goosegrade_error = true; ?>
             <div class="goosegrade-error">
            <?php _e( 'Sorry, the credentials you\'ve supplied are not correct. Please reenter them below.', 'goosegrade' ); ?>    
            </div>
          
      <?php } ?>
      <?php } ?>

      <?php

         // try to figure out site id
         $goosegrade = new goosegrade( $_POST['username'], $_POST['token1'] );
         $sites = $goosegrade->get_sites();
         $site_id = 0;
         if ($sites !== false && $sites->status == 0 && $sites->count > 0) {
            for ($i = 1; $i <= $sites->count; $i++) {
               eval( '$x = goosegrade_array_to_object( $sites->site_' . $i . '); $url = $x->url;' );
               if (strtolower($url) == strtolower(get_bloginfo('home'))) {
                  eval( '$site_id = (integer)$x->id;' );
                  break;
               }
            }
         }

         if ($site_id == 0 && !$goosegrade_error) {
            $goosegrade_error = false;
            echo( '<div class="goosegrade-error">' . __('You\'re in!  Click <a href="'.$goosegrade_settings['base_url'].'addsites" target="_blank">here to add ' . get_bloginfo('home') . ' to your gooseGrade account</a>.', 'goosegrade' ) . '</div>' );
         }

      ?>

      <?php if ( !$goosegrade_error ) { ?>

         <div class="goosegrade-success">
            <?php _e( 'Settings have been updated', 'goosegrade' ); ?>
         </div>

         <?php
            $goosegrade_settings['username'] = $_POST['username'];
            $goosegrade_settings['password'] = $_POST['token1'];
            $goosegrade_settings['position'] = $_POST['position'];
            $goosegrade_settings['base_url'] = $_POST['siteurl'];
            $goosegrade_settings['js_url'] = $_POST['jsurl'];
            if ( isset($_POST['show_on_blog']) ) {
               $goosegrade_settings['show_on_blog'] = 1;
            } else {
               $goosegrade_settings['show_on_blog'] = 0;
            }

            //$goosegrade_settings['color'] = $_POST['color'];
            $goosegrade_settings['site_id'] = $site_id;

            if ( get_option( 'goosegrade_settings', false ) !== false ) {
               update_option( 'goosegrade_settings', serialize($goosegrade_settings) );
            } else {
               add_option( 'goosegrade_settings', serialize($goosegrade_settings) );
            }

         ?>
      <?php } ?>
   <?php } ?>

   <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_GET['page']; ?>">

   <div class="settings-wrapper">
      <h3>Personal Information</h3>
      <div class="left-settings">

         <?php _e( 'Please enter your gooseGrade account info.', 'goosegrade' ); ?><br /><br />
         <?php _e( 'or', 'goosegrade' ); ?><br /><br />
         <?php _e( '<a href="'.$goosegrade_settings['base_url'].'user/register" target="_blank">create a gooseGrade account for free</a>', 'goosegrade' ); ?>
      </div>
      <div class="right-settings">
         <ul>
            <li class="form-item">
               <label for="username"><?php _e( 'Username' , 'goosegrade' ); ?>:</label><input type="text" id="username" name="username" value="<?php echo $goosegrade_settings['username']; ?>" />
            </li>
            <li class="form-item">
               <label for="token1"><?php _e( 'Password', 'goosegrade' ); ?>:</label><input type="password" id="token1" name="token1" value="<?php echo $goosegrade_settings['password']; ?>" />
            </li>
            <li class="form-item">
               <label for="token2"><?php _e( 'Password (Repeat)', 'goosegrade' ); ?>:</label><input type="password" id="token2" name="token2" value="<?php echo $goosegrade_settings['password']; ?>" />
            </li>
         </ul>
      </div>
      <div class="goosegrade-clearer"></div>

   </div>

   <div class="settings-wrapper">
      <h3>Customization Options</h3>

      <div class="left-settings">
         <?php _e( 'These options can be used to customize the look and feel of the gooseGrade integration on the main page.', 'goosegrade' ); ?><br /><br />
      </div>

      <div class="right-settings">
         <ul>
            <li class="form-item">
               <label for="show_on_blog"><?php _e( 'Show Widget On Main Blog', 'goosegrade' ); ?></label>
               <input type="checkbox" name="show_on_blog" <?php if ( $goosegrade_settings['show_on_blog'] ) echo ('checked'); ?>/>

               </label>
            </li>
            <li class="form-item">
               <label for="position"><?php _e( 'Widget Position' , 'goosegrade' ); ?>:</label>
                  <select id="position" name="position">
                     <option value="topleft"<?php if ($goosegrade_settings['position'] == 'topleft') echo ' selected'; ?>><?php _e('Top-Left'); ?></option>
                     <option value="topright"<?php if ($goosegrade_settings['position'] == 'topright') echo ' selected'; ?>><?php _e('Top-Right'); ?></option>
                     <option value="botleft"<?php if ($goosegrade_settings['position'] == 'botleft') echo ' selected'; ?>><?php _e('Bottom-Left'); ?></option>
                     <option value="botright"<?php if ($goosegrade_settings['position'] == 'botright') echo ' selected'; ?>><?php _e('Bottom-Right'); ?></option>
                     <option value="custom"<?php if ($goosegrade_settings['position'] == 'custom') echo ' selected'; ?> onfocus="$j = jQuery.noConflict(); $j('#gg-custom-info').show();" onblur="$j = jQuery.noConflict(); $j('#gg-custom-info').show();"><?php _e('Custom'); ?></option>
                  </select>
            </li>

            <!--
            <li class="form-item">
               <label for="color"><?php _e( 'Widget Color' , 'goosegrade' ); ?>:</label>
                  <select id="color" name="color">
                     <option value="red"<?php if ($goosegrade_settings['color'] == 'red') echo ' selected'; ?>><?php _e('Red'); ?></option>
                     <option value="green"<?php if ($goosegrade_settings['color'] == 'green') echo ' selected'; ?>><?php _e('Green'); ?></option>
                     <option value="blue"<?php if ($goosegrade_settings['color'] == 'blue') echo ' selected'; ?>><?php _e('Blue'); ?></option>
                     <option value="gray"<?php if ($goosegrade_settings['color'] == 'gray') echo ' selected'; ?>><?php _e('Gray'); ?></option>
                  </select>
            </li>
            -->

         </ul>
         <div id="gg-custom-info">
         <br />To insert custom widget code into the blog, select <b>"Custom"</b> in the above list and
				insert <div><b><span style="color:red">&lt;?php</span> <span style="color:blue">echo</span> <span style="color:green">goosegrade_insert_widget();</span> <span style="color:red">?&gt;</span></b></div> wherever you want the widget to display.<br/><br/>

        	<?php if ( isset( $goosegrade_settings['site_id'] ) ) { ?>
        		You can also <a href="<?php echo $goosegrade_settings['base_url'] ?>sitespanel/<?php echo $goosegrade_settings['site_id']; ?>" target="_blank">change the widget size or color here.</a>
        	<?php } ?>
         </div>
      </div>

      <div class="goosegrade-clearer"></div>
   </div>

   <div class="settings-wrapper">
      <h3>Enterprise Options</h3>
      <div class="left-settings">

         <?php _e( 'Change these information ONLY if your company is using the enterprise version.', 'goosegrade' ); ?><br /><br />
      </div>
      <div class="right-settings">
         <ul>
            <li class="form-item">
               <label for="siteurl"><?php _e( 'Site URL' , 'goosegrade' ); ?>:</label><input type="text" id="siteurl" name="siteurl" value="<?php echo $goosegrade_settings['base_url']; ?>" />
            </li>
            <li class="form-item">
               <label for="jsurl"><?php _e( 'Site JS URL', 'goosegrade' ); ?>:</label><input type="text" id="jsurl" name="jsurl" value="<?php echo $goosegrade_settings['js_url']; ?>" />
            </li>
         </ul>
      </div>
      <div class="goosegrade-clearer"></div>

   </div>


   <p class="submit"><input type="submit" name="submit" id="submit" value="<?php _e( 'Update Settings', 'goosegrade' ); ?>" /></p>
   </form>
</div>
