<?php

	global $_topcollage_config;
	$_topcollage_config = array(
		'width'				=> array(550, 		"the width, in pixels, to make the collage"),
		'height'			=> array(400,		"the height, in pixels to make the collage"),
		'layout'			=> array(0, 		"the layout to use"),
		'size'				=> array('medium',	"the size of image to use.  could be 'thumbnail', 'medium' or 'large"),
		'padding'			=> array(5, 		"the padding, in pixels, around each of the images"),
		'background'		=> array('#000000', "the background for the collage - can be anything accepted by css style background"),
		'frame_border_width'=> array(0,			"the width of the border around the whole collage"),
		'frame_border_style'=> array('solid', 	"the style for the border around the whole collage"),
		'frame_border_color'=> array('white', 	"the color for the border around the whole collage"),
		'image_border_width'=> array(4,			"the width of the border around each image"),
		'image_border_style'=> array('solid', 	"the style for the border around each image"),
		'image_border_color'=> array('#FFFFFF', "the color for the border around each image"),
		'frame_style'		=> array('',		"additional css_styling for the whole frame"),
		'image_style'		=> array('',		"additional css_styling for each image"),
		'image_index_style'	=> array('top:5px;left:5px;',			"additional css_styling for the image index"),
		'randomize'			=> array(false,		"randomize the order of the images"),
		'image_float'		=> array('left',	"you can reverse the layout of the collage by changing this to right"),
		'frame_align'		=> array('left',	"how to place the collage.  Choices are 'left', 'right', 'center', 'float_left', 'float_right'"),
		'link'				=> array('permalink',	"what to link the image to, options are 'permalink' or 'file'"),
		'rel'				=> array('', 		"rel to add for the link.  Useful for creating lightbox_esque effects"),
		'show_indices'		=> array(false, 	"show the image number.  Useful for debugging or referring to photos in the collage."),
	);
	
	global $topcollage_config;
	$topcollage_config = array();
	foreach (array_keys($_topcollage_config) as $option) {
		if (($value = get_option($option)) != ""){
			$topcollage_config[$option] = $value;
		}
		else{
			$topcollage_config[$option] = $_topcollage_config[$option][0];
		}
	}
?>
