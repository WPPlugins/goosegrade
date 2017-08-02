	<tr>
		<td colspan="2">
		<?php $user->corrections = goosegrade_array_to_object( $user->corrections ); ?>
		<div class="gg_profile_left">
			<img src="<?php echo $user->picture; ?>" alt="<?php echo $user->name; ?>" />
		</div>
		<div class="gg_profile_right">
			<h1><a href="<?php echo $goosegrade_settings['base_url'] ?>profile/<?php echo $user->name; ?>" target="_blank"><?php echo $user->name; ?></a></h1>
			<h2><?php echo $user->corrections->accepted . " out of " . $user->corrections->total; ?> accepted, <br><?php echo sprintf("%0.1f" , $user->corrections->accepted*100/$user->corrections->total); ?>% accuracy.</h2>
		</div>
		<div class="goosegrade-clearer"></div>
		</td>
	</tr>