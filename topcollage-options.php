<?php
// create custom plugin settings menu
add_action('admin_menu', 'topcollage_menu');
add_action( 'admin_init', 'topcollage_register_settings' );


function topcollage_menu() {
  add_options_page('topCollage Options', 'topCollage', 'administrator', 'topcollage', 'topcollage_options');
}

function topcollage_options() {
	topcollage_settings_page();
}

function topcollage_register_settings() {
	global $topcollage_config;
	foreach ($topcollage_config as $option => $value){
		//register our settings
		register_setting( 'topcollage-settings', $option );
	}
}

function create_pretty_name($option){
	return ucwords(str_replace('_',' ',$option));
}

function topcollage_settings_page() {
?>
<div class="wrap">
<h2>topCollage</h2>
<p>These are the default settings for all instances of topCollage on your website.  For specific collages, you can override any of these settings by copying
	the shortcode example code beside each option and then pasting it within the [topcollage] shortcode of your post changing the value.

<form method="post" action="options.php">
    <?php settings_fields( 'topcollage-settings' ); ?>
    <table class="form-table">
	<tr>
		<th scope="column">Option</th>
		<th scope="column">Current Value</th>
		<th scope="column">Shortcode Example</th>
	</tr>
<?php
	global $_topcollage_config;
	global $topcollage_config;
	foreach ($_topcollage_config as $option => $option_info) :
		list($default_value,$description) = $option_info;
		$value = $topcollage_config[$option];
?>
    <tr valign="top">
    <th scope="row"><?php echo create_pretty_name($option); ?>:</th>
    <td width="40%">
		<?php if (is_bool($default_value)) : ?>
			<label for="<?php echo $option; ?>"><input name="<?php echo $option; ?>" type="checkbox" id="<?php echo $option; ?>" value="1" <?php checked('1', $value); ?> /> <?php echo $description; ?></label>
		<?php elseif ($option == 'layout') : 
			// Let's show some examples
			add_filter('topcollage_images','topcollage_spoof_images',10,2);
			$layouts = get_topcollage_layouts();
			foreach ($layouts as $l => $layout){
				$config = $topcollage_config;
				$config['layout'] = $l;
				$config['width'] = 200;
				$config['height'] = floor(200 / $topcollage_config['width'] * $topcollage_config['height']);
				$config['frame_align'] = 'float_left';
				$config['show_indices'] = true;
				$config['image_index_style'] = 'top:2px;left:2px;color:white;background:black;width:10px;text-align:center';
				echo create_topcollage($config); ?>
				<label><input name="<?php echo $option; ?>" type="radio" value="<?php echo $l; ?>" class="tog" <?php checked($l, $value); ?> />layout=<?php echo $l; ?></label>
				<div style="clear:both;margin-bottom:10px;"></div>
				<?php
			}
		?>
		<?php else : ?>
			<input type="text" name="<?php echo $option; ?>" value="<?php echo $value; ?>" /><br/><?php echo $description; ?>
		<?php endif; ?>
	</td>
    <td width="40%"><?php echo "$option=$value"; ?></td>
    </tr>
<?php	
	endforeach;
?>	
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>
