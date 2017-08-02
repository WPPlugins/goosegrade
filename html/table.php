<?php
	global $goosegrade_settings;

   $status = 'pending';
   if ( isset( $_GET['state'] ) ) {
      $status = $_GET['state'];
   }

   $items_per_page = '5';
   $current_page = 1;

   if ( isset( $_GET['ggpage'] ) ) {
   	$current_page = $_GET['ggpage'];
   }

   global $goosegrade_api;
   if ( $goosegrade_api ) {
   	$site_id = $goosegrade_settings['site_id'];
      $corrections = $goosegrade_api->get_corrections( $status, $site_id, $items_per_page, $current_page );
   } else {
      $corrections = array();
   }

   if ( $corrections->count == 0 ) {
   	$items_on_this_page = 0;
   } else {
	   if ( ($corrections->actual_page + 1) == $corrections->total_pages ) {
	   		$items_on_this_page =  $corrections->count - ($corrections->total_pages - 1)*$items_per_page;
	   } else {
	   		$items_on_this_page = $items_per_page;
	   }
   }

?>

<div id="goosegrade-settings" class="wrap">

   <div id="accept-menu">

   <h2>Manage Corrections</h2>
      <?php if ( $status == 'pending' ) { ?>Pending<?php } else { ?><a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=pending">Pending</a><?php } ?> |
      <?php if ( $status == 'accepted' ) { ?>Accepted<?php } else { ?><a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=accepted">Accepted</a><?php } ?> |
      <?php if ( $status == 'declined' ) { ?>Declined<?php } else { ?><a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=declined">Declined</a><?php } ?>
   </div>

   <div id="gg_logo">
   	<img src="<?php echo compat_get_plugin_url( 'goosegrade' ); ?>/images/gg-logo.png" alt="Goosegrade Logo" />
   </div>
   <div class="goosegrade-clearer"></div>

<div class="tablenav">


<div>


   <div style="float: right;">
   <table id="gg_profile_box" class="widefat" style="width: 300px; display: none;">
   	<thead>
   		<tr>
   			<th style="width: 100%">User Profile Info</th>
   			<th style="width: 10px"><a href="#" onclick="$j = jQuery.noConflict(); $j('#gg_profile_box').fadeOut(); return false;">[x]</a></th>
   		</tr>
   	</thead>
   	<tbody id="user-info">
   	</tbody>
   </table>
   </div>
</div>

<?php if ( $corrections->count > 0 ) { ?>
<table class="widefat" id="gg_corrections_list">
   <thead>
   <tr>
   <th style="width: 25%;">gooseGrade Username</th><th style="width: 50%;">Correction Information</th><th style="width: 25 %;">Correction Found In</th</tr>
   </thead>
   <tbody>
      <?php for ($i = 0; $i < $items_on_this_page; $i++) { ?>
         	<?php $base = ($current_page - 1)*$items_per_page; ?>
            <?php $name = 'correction_' . ($i + $base); ?>
            <?php eval('$correction = goosegrade_array_to_object( $corrections->' . $name . ' );'); ?>
			<?php $will_work = $goosegrade_api->check_correct( $correction->extra, $correction->original, $correction->value ); ?>    
			  
         <tr<?php if ( !$will_work ) echo (" class='warning'"); ?>>
            <?php $user = $goosegrade_api->get_user_info( $correction->username ); ?>

            <!-- <td><?php echo date('F j, Y', (int)$correction->created); ?></td> -->
            <td><div class="user-photo"><?php if ($user->picture) { ?><img src="<?php echo $user->picture; ?>" alt="" /><?php } else { ?>&nbsp;<?php } ?></div><a href="#" onclick="$j = jQuery.noConflict(); $j.get('<?php echo get_bloginfo('wpurl') . "/?goosegrade=profile&goosegrade_user=" . $correction->username; ?>', function(data, status) { $j = jQuery.noConflict(); $j('#user-info').hide().html(data).fadeIn(); $j('#gg_profile_box').show(); });  return false;"><?php echo $correction->username; ?></a></td>
            <td id="show">
               <?php echo $correction->type; ?> correction posted on <?php echo date('F j, Y', (int)$correction->created); ?> worth <?php echo $correction->points; ?> points<br /><br />
               <div class="correction-area">
                  <div class="left">Original:</div><div class="right"><a href="#" onclick="$j = jQuery.noConflict(); $j('.correction-details').hide(); $j('#correction-<?php echo $i; ?>').fadeIn(); return false;"><?php echo $correction->original; ?></a></div>
                  <div class="goosegrade-clearer"></div>
               </div>
               <div class="correction-area">
                  <div class="left">Correction:</div><div class="right"><a href="#" onclick="$j = jQuery.noConflict(); $j('.correction-details').hide(); $j('#correction-<?php echo $i; ?>').fadeIn(); return false;"><?php echo $correction->value; ?></a></div>
                  <div class="goosegrade-clearer"></div>
               </div>

               <br />
               <?php if ( $status == 'pending' ) { ?>

               	<?php if ( !$will_work ) { ?>
               		<a href="<?php bloginfo('wpurl'); ?>/wp-admin/post.php?action=edit&post=<?php echo $correction->extra; ?>" title="Sorry but we cannot automatically edit this post.  You need to manually edit it and then accept the correction.">Manually Edit</a> | 
               	<?php } ?>

							<?php if ( $will_work ) { ?>
               			<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;page=<?php echo $_GET['page']; ?>&amp;status=accepted&amp;action=correct&amp;id=<?php echo $correction->id; ?>&amp;type=<?php echo $correction->type; ?>&amp;extra=<?php echo $correction->extra; ?>&amp;original=<?php echo urlencode($correction->original); ?>&amp;new=<?php echo urlencode($correction->value); ?>" title="Accept and edit this post.">Accept</a>											
							<?php } else { ?>
			               <a href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;page=<?php echo $_GET['page']; ?>&amp;status=accepted&amp;action=correct&amp;id=<?php echo $correction->id; ?>&amp;type=<?php echo $correction->type; ?>&amp;extra=<?php echo $correction->extra; ?>&amp;original=<?php echo urlencode($correction->original); ?>&amp;new=<?php echo urlencode($correction->value); ?>" title="Accept and edit this post.">Accept</a>	
							<?php } ?>
							               | 
               <a href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;page=<?php echo $_GET['page']; ?>&amp;status=declined&amp;action=correct&amp;id=<?php echo $correction->id; ?>&amp;type=<?php echo $correction->type; ?>" title="Decline this correction.  No edit will be made.">Decline</a>
				<?php if ( !$will_work ) { ?>
					
				<?php } else { ?>
               | <a href="#" onclick="$j = jQuery.noConflict(); $j('.correction-details').hide(); $j('#correction-<?php echo $i; ?>').fadeIn(); return false;" title="Show where this error has been found.">View Context</a>				
				<?php } ?>
	
               <?php } ?>
                           </td>



            <!--
            <td><a href="#" onclick="$j = jQuery.noConflict(); $j('.correction-details').hide(); $j('#correction-<?php echo $i; ?>').fadeIn(); return false;"><?php echo $correction->original; ?></a></td>
            <td><a href="#" onclick="$j = jQuery.noConflict(); $j('.correction-details').hide(); $j('#correction-<?php echo $i; ?>').fadeIn(); return false;"><?php echo $correction->value; ?></a></td> -->
            <?php $query = new WP_Query('showposts=1&p=' . $correction->extra); ?>
            <?php $query->the_post(); ?>
            <td><?php global $post; ?><?php edit_post_link($post->post_title); ?></td>

         </tr>
         <tr class="correction-details" id="correction-<?php echo $i; ?>" style="display: none;">
            <td colspan="3">
               <table border="0" style="width: 100%" cols="2">
						<tr id="gg_border">
        					<td width="50%" style="border:0px;"><h3>Original</h3><?php echo str_replace($correction->original, '<span class="gg_changed">' . $correction->original . '</span>', goosegrade_short_content( $correction->original, get_the_content() ) ); ?></td>
            			<td width="50%" style="border:0px;"><h3>Changed</h3><?php echo str_replace($correction->original, '<span class="gg_changed">' . $correction->value . '</span>', goosegrade_short_content( $correction->original, get_the_content() ) ); ?></td>
            		</tr>
               </table>
            </td>
         </tr>
      <?php } ?>
      <tr>
      	<td colspan="3" id="gg_page_nav">Select Page -
      		<?php for ($i = 1; $i <= $corrections->total_pages; $i++) { ?>
      			<?php if ( $i == $current_page ) { ?>

      				<?php echo $i . ' '; ?>
      			<?php } else { ?>
						<?php if ( $status == 'pending' ) { ?>
							<a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=pending&ggpage=<?php echo $i; ?>">
						<?php } else if ( $status == 'accepted' ) { ?>
							<a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=accepted&ggpage=<?php echo $i; ?>">
						<?php } else { ?>
							<a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=declined&ggpage=<?php echo $i; ?>">
						<?php } ?>
      				<?php echo $i . ' '; ?>
      				</a>
      			<?php } ?>
      		<?php } ?>
      		-
      		<?php if ( $current_page == 1 ) { ?>
      			Previous
      		<?php } else { ?>
       			<a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=<?php echo $status; ?>&ggpage=<?php echo $current_page - 1; ?>">Previous</a>
      		<?php } ?>
				|
      		<?php if ( $current_page == $corrections->total_pages ) { ?>
      			Next
      		<?php } else { ?>
       			<a href="<?php $r = explode( '?', $_SERVER['REQUEST_URI'] ); echo $r[0]; ?>?page=<?php echo $_GET['page'] ?>&state=<?php echo $status; ?>&ggpage=<?php echo $current_page + 1; ?>">Next</a>
      		<?php } ?>


      	</td>
      </tr>
   </tbody>
</table>
<?php } else { ?>
<div id="gg_currently_empty">
There are currently no corrections to display.
</div>
<?php } ?>

</div>

</div>

