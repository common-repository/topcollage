<?php
/*
Plugin Name: topCollage
Plugin URI: http://www.topquarkproductions.ca/
Description: topCollage makes a collage out of photos added to your post.  
Version: 0.5.1
Author: Trevor Mills
Author URI: http://www.topquarkproductions.ca/

=============================================================
Change Log
=============================================================
Version 0.5.0 - May 7, 2010
---------------------------
- initial check-in

Version 0.5.1 - July 15, 2010
---------------------------
- Fixed problem that had all attached images appearing in feed as opposed 
  to just showing the collage.  Unfortunately, feeds can't be guaranteed to
  use styles, so instead of just outputing all images, we'll display only
  the first image in each collage
*/

define('TOPCOLLAGE_VERSION', '0.5.0');

add_shortcode('topcollage','create_topcollage');

include_once('topcollage-config.php');
include_once('topcollage-options.php');

function create_topcollage($attr,$content=null,$code=""){
	global $post,$topcollage_config;

	static $topcollage_instance = -1;
	$topcollage_instance++;
	
	
	$backend_defaults = array(
		'id'         		=> $post->ID, 	// the id of the post to look in for attached images
		'start_with' 		=> 0, 			// 0-based image to start with.  start_with = 4 will begin with the 5th attached image
		'layout'			=> 0, 			// the index within the layout array to use for the output
		'images'			=> '',			// as an alternative to specifying the layout, you can specify the number of images that you want to show
		'size'				=> 'medium',	// the size of image to use
		'image_float'		=> 'left',		// you can reverse the layout of the collage by changing this to right
		'use_human_index'	=> true, 		// if true, starts the index at 1 instead of 0
		'link'				=> 'permalink',	// what to link the image to, other options include 'file'
		'rel'				=> '', 			// rel to add for the link.  Useful for creating lightbox_esque effects
		'nudge_vertical'	=> '()',		// you can pass in an array of nudges (in pixels, positive or negative) to nudge images down or up.  e.g. (0,-3,4,0,0) will nudge image 1 up 3px and image 2 down 4px.  Useful for fixing cut_off image.  Default is an empty array.
		'nudge_horizontal'	=> '()'			// you can pass in an array of nudges (in pixels, positive or negative) to nudge images left or right.  e.g. (0,-3,4,0,0) will nudge image 1 left 3px and image 2 right 4px. Default is an empty array.
	);
	extract(shortcode_atts(array_merge($backend_defaults,$topcollage_config), $attr));
	
	$image_count = $images; // $images is a variable used further down the function.  
	
	if ($use_human_index and !isset($attr['start_with'])){
		$start_with = 1;
	}
	
	/*************************************
	* The layout array is what holds the different layouts available for the collage
	* Image sizes are defined as percentages of the whole.  
	* Warning: some care must be taken as not all layouts will automatically work
	* The images get floated, so some understanding of float is required.  
	* If you're having trouble with a layout, you can try changing image-float.  That might fix it
	*
	* The Layout Array is defined as percentage widths and heights of an arbitrary number of images.  
	* Again, be careful with the math.  
	*************************************/
	$layouts = get_topcollage_layouts();
	
	$output = "";
	
	if ($image_count !== '' and $attr['layout'] == ""){
		// Let's see if there's a layout with the desired number of images
		foreach ($layouts as $_l => $_layout){
			if (count($_layout) == $image_count){
				$layout = $_l;
				break;
			}
		}
	}
	else{
		// some sanity checks
		if (!array_key_exists($layout,$layouts) or !is_array($layouts[$layout])){
			$output.= "<!-- Could not find layout $layout.  Using default layout -->\n"; 
			$layout = 0; // default to the first layout
		}
	}

	// Allow other plugins to create the images array, following the format above
	// Follow the format: 
	// 		$images[] = array('src' => {url_to_image}, 'href' => {what_to_open_on_click}, 'width' => {$width_of_the_image}, 'height' => {height_of_the_image});
	// In order to help with putting the appropriate sized image into the collage (resizing degrades the quality)
	// the $images[] array can actually be an array of arrays following the format above.  If that is the case, then topcollage will 
	// search through them to find the smallest one that is larger than the required dimensions.  This isn't totally necessary, but 
	// seems to be a nice feature (especially for those who don't like bad quality images)
	
	$required_images = is_feed() ? 1 : count($layouts[$layout]);
	$images = array();
	$images = apply_filters('topcollage_images',$images,$required_images); 
	
	if (!is_array($images) or count($images) < $required_images){
		// Try the old standby of looking for attached images

		$id = intval($id);

		// Step 1 - Get the images
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );

		if ( empty($attachments) )
			return '';

		/*
		All images were appearing in the feed.  Should have caught this earlier
		if ( is_feed() ) {
			$output.= "\n";
			foreach ( $attachments as $att_id => $attachment )
				$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
			return $output;
		}
		*/


		// In order to allow other plugins to call the collage maker with their own images, we're going to standardize
		// an input into a separate function.  Here's where we create that input.  See the next function for notes on the format
		if (!is_array($images)){
			$images = array(); 
		}
		foreach ($attachments as $attachment){
			if (isset($link) && 'permalink' == $link){
				$_link = get_attachment_link($attachment->ID);
			}
			else{
				$_link = $attachment->guid;
			}
			$meta = wp_get_attachment_metadata($attachment->ID);
			$original_url = wp_get_attachment_url($attachment->ID);
			$original = image_downsize($attachment->ID,'full');
			if (is_array($meta) and array_key_exists('sizes',$meta)){
				$_images = array();
				foreach ($meta['sizes'] as $_size => $info){
					$_images[] = array('src' => str_replace(basename($original_url),$info['file'],$original_url),'href' => $_link, 'width' => $info['width'], 'height' => $info['height']);
				}
				
				// Add in the original size
				$_images[] = array('src' => $original[0],'href' => $_link, 'width' => $original[1], 'height' => $original[2]);
				$images[] = $_images;
				
			}
			else{
				$images[] = array('src' => $original[0],'href' => $_link, 'width' => $original[1], 'height' => $original[2]);
			}
		}
	}	
	
	// Get the number of attachments that we need
	$_start_with = ($use_human_index == true ? $start_with - 1 : $start_with);
	$images = array_slice($images,$_start_with,$required_images);
	
	if ($randomize){
		shuffle($images);
	}
	
	// Set up the nudge arrays here.  First, default to the values passed in the arrays
	if (preg_match("/(array)?(\([0-9,\- ]*\))/i",$nudge_vertical,$matches)){
		$nudge_vertical = eval("return array{$matches[2]};");
	}
	else{
		$nudge_vertical = array();
	}
	if (preg_match("/(array)?(\([0-9,\- ]*\))/i",$nudge_horizontal,$matches)){
		$nudge_horizontal = eval("return array{$matches[2]};");
	}
	else{
		$nudge_horizontal = array();
	}
	// Pad them up to the number of images
	while(count($nudge_vertical) < count($images)){
		$nudge_vertical[] = 0;
	}
	while(count($nudge_horizontal) < count($images)){
		$nudge_horizontal[] = 0;
	}
	
	// You can also pass in 'nudge-n-vertical' or 'nudge-n-horizontal' or 'nudge-n-up', etc. to nudge the nth image vertically/horizontally
	// Now allow override by specific values passed in
	
	if (is_array($attr)){
		for($n = 0; $n < count($images); $n++){
			$_n = ($use_human_index == true ? $n + 1 : $n);
			if (array_key_exists("nudge_{$_n}_vertical",$attr)){
				$nudge_vertical[$n] = $attr["nudge_{$_n}_vertical"];
			}
			elseif (array_key_exists("nudge_{$_n}_up",$attr)){
				$nudge_vertical[$n] = -1 * intval($attr["nudge_{$_n}_up"]);
			}
			elseif (array_key_exists("nudge_{$_n}_down",$attr)){
				$nudge_vertical[$n] = $attr["nudge_{$_n}_down"];
			}
			if (array_key_exists("nudge_{$_n}_horizontal",$attr)){
				$nudge_horizontal[$n] = $attr["nudge_{$_n}_horizontal"];
			}
			elseif (array_key_exists("nudge_{$_n}_left",$attr)){
				$nudge_horizontal[$n] = -1 * intval($attr["nudge_{$_n}_left"]);
			}
			elseif (array_key_exists("nudge_{$_n}_right",$attr)){
				$nudge_horizontal[$n] = $attr["nudge_{$_n}_right"];
			}
		}
	}
	
	// Okay, here's the good stuff.
	// First, output the beginning div tag for the collage.  Included is a generic class, a post specific class and an instance specific id
	switch ($frame_align){
	case 'float_left':
		$float = "float:left;\n";
		break;
	case 'float_right':
	case 'right':
		$float = "float:right;\n";
		break;
	case 'left':
	default:
		break;
	}
	$output.= <<<DIV_TAG
		<style type="text/css">
		<!--
		#topcollage-{$topcollage_instance}{
			width:{$width}px;
			height:{$height}px;
			padding:0px;
			background:{$background};
			border:{$frame_border_width}px {$frame_border_style} {$frame_border_color};
			{$float}
		}
		#topcollage-{$topcollage_instance} .topcollage-image{
			float:{$image_float};
			position:relative;
			margin:0px;
			margin-top:{$padding}px;
			margin-{$image_float}:{$padding}px;
			overflow:hidden;
			display:block;
			padding:0px;
			border:{$image_border_width}px {$image_border_style} {$image_border_color};
		}
		//-->
		</style>
		<div id="topcollage-{$topcollage_instance}" class="topcollage topcollage-{$id}" style="{$frame_style}">
DIV_TAG;

	$width -= $padding;
	$height -= $padding;
	foreach($images as $i => $image){
		$target_width = floor($width*$layouts[$layout][$i][0]/100);
		$target_height = floor($height*$layouts[$layout][$i][1]/100);
		if($layouts[$layout][$i][2] === true){
			$flip_float = ($image_float == "right" ? "left" : "right"); 
			$flip_float = "float:$flip_float;margin-{$image_float}:0px;margin-{$flip_float}:{$padding}px";
		}
		else{
			$flip_float = "";
		}
		
		$target_width-= $padding; // account for padding - left side only
		$target_height-= $padding; // account for padding - top side only
		$target_width-= 2*$image_border_width; // account for border width, left & right side
		$target_height-= 2*$image_border_width; // account for border width, top & bottom
		
		if (is_array(current($image))){
			$use_this_one = '';
			foreach ($image as $_i => $_image){
				if ($_image['width'] >= $target_width and $_image['height'] >= $target_height){
					if ($use_this_one === "" or ($_image['width'] < $image[$use_this_one]['width'] and $_image['height'] < $image[$use_this_one]['height'])){
						$use_this_one = $_i;
					}
				}
			}
			if ($use_this_one !== ''){
				$image = $image[$use_this_one];
			}
			else{
				reset($image);
				if (is_array(current($image))){
					$image = end($image);
				}
			}
		}
		
		if ($target_width/$image['width'] > $target_height/$image['height']){
			$limiter = "width=\"$target_width\"";
		}
		else{
			$limiter = "height=\"$target_height\"";
		}
		
		if ($show_indices and !is_feed()){
			$_i = ($use_human_index == true ? $i + 1 : $i);
			$index = <<<INDEX_CODE
				<div class="tc_image_index" style="position:absolute;{$image_index_style}">$_i</div>
INDEX_CODE;
		}
		else{
			$index = "";
		}
		
		if ($topcollage_instance > 0){
			$instance_class = "topcollage-collage-{$topcollage_instance} topcollage-collage-{$topcollage_instance}-image-{$_i}";
		}
		else{
			$instance_class = "topcollage-image-{$_i}";
		}
		if ($rel != ""){
			$relcode = "rel=\"{$rel}\"";
		}
		$output.= <<<IMAGE_CODE
			<div class="topcollage-image {$instance_class}" style="width:{$target_width}px;height:{$target_height}px;{$image_style};{$flip_float}">
				$index<a href="{$image['href']}" $relcode><img src="{$image['src']}" $limiter style="margin-top:{$nudge_vertical[$i]}px;margin-left:{$nudge_horizontal[$i]}px"></a>
			</div>
IMAGE_CODE;
	}
	
	$output.= <<<CLOSE_DIV_TAG
			<br style="clear:both"/>
		</div>
CLOSE_DIV_TAG;

	$output = apply_filters('topcollage-output',$output,$topcollage_instance);
	
	return $output;
}

function topcollage_spoof_images($images,$required_images){
	$images = array();
	$samples = glob(dirname(__FILE__).'/images/*.jpg');
	while($required_images > 0){
		$image = current($samples);
		$src = get_bloginfo('wpurl').'/wp-content/plugins/topcollage/images/'.basename($image);
		$info = getimagesize($image);
		$images[] = array('src' => $src,'href' => $src, 'width' => $info[0], 'height' => $info[1]);
		$required_images--;
		if (!next($samples)){
			reset($samples);
		}
	}
	return $images;
}

function get_topcollage_layouts(){
	$layouts = array();
	
	/*******************************************
	* 	Layout Example
	*		$layouts[] = array(
	*			array(30,30), // Image 0 - 30% width, 30% height
	*			array(70,30), // Image 1 - 70% width, 30% height
	*			array(70,70), // Image 2 - 70% width, 70% height
	*			array(30,35), // Image 3 - 30% width, 35% height
	*			array(30,35) // Image 4 - 30% width, 35% height 
	*		);
	******************************************/


	// Layout 0
	$layouts[] = array(
		array(30,30), // Image 0 - 30% width, 30% height
		array(70,30), // Image 1 - 70% width, 30% height
		array(70,70), // Image 2 - 70% width, 70% height
		array(30,35), // Image 3 - 30% width, 35% height
		array(30,35) // Image 4 - 30% width, 35% height
	);

	// Layout 1 - simple square
	$layouts[] = array(
		array(50,50), 
		array(50,50), 
		array(50,50), 
		array(50,50)  
	);

	// Layout 2 
	$layouts[] = array(
		array(25,60), 
		array(25,60,true),
		array(50,30), 
		array(50,30), 
		array(60,40),
		array(40,40)
	);

	// Layout 3 - just a single image
	$layouts[] = array(
		array(100,100) 
	);

	// Layout 4
	$layouts[] = array(
		array(33,100), 
		array(34,100),
		array(33,100)
	);

	// Layout 5
	$layouts[] = array(
		array(50,100), 
		array(50,100)
	);

	    // Layout 6
	$layouts[] = array(
		array(100,50), 
		array(100,50)
	);


	return apply_filters('topcollage_layouts',$layouts);
}


