=== Plugin Name ===
Contributors: topquarkproductions
Tags: collage, images, photo
Requires at least: 2.7
Tested up to: 2.9
Stable tag: 0.5.1

Automatically creates css-based collages using the images attached to the post.  The images are clickable or lightbox-able. 

== Description ==

This plugin will take images that have been attached to your post and create a css-based collage out of them.  The images within the collage are clickable.  

There are many options available to style your collage and you can change these settings globally for the whole site, or on a collage-by-collage basis. 

Usage - insert the `[topcollage]` shortcode into your post or page

*	[topcollage] - will layout a collage of 5 photos (that's the default setting)
*	[topcollage images=n] - will layout a collage of n photos (as shipped, n can be any number from 1 to 6 - code ninjas can add more layouts via a filters)
*	[topcollage images=n start_with=s] - will layout a collage with n photos starting with the sth photo in the gallery (you can reorder images in the gallery by dragging and dropping)
*	[topcollage layout=x] - will create a collage with layout number x (as shipped, x can be any number from 0 to 6).  To see examples of each layout, you can visit the settings page.

== Installation ==

1. Install and activate the plugin
2. Visit the Settings -> topCollage page to match the look to your site

== Screenshots ==

1. Screenshot Collage using `images=5`
2. Screenshot Collage using `images=4`
3. Screenshot Collage using `images=6`
4. Screenshot Collage using `images=3`

== Frequently Asked Questions ==

= How do I include a collage in my post =

1. Upload 1 or more images to your post
2. Add the shortcode `[topcollage]` to your post
3. See Usage, under Description for more examples

= What are the options =

* `width` - the width, in pixels, to make the collage
* `height` - the height, in pixels to make the collage
* `images` - the number of images to use in the collage
* `layout` - the layout to use. this takes precedence over 'images'
* `padding` - the padding, in pixels, around each of the images
* `background` - the background for the collage - can be anything accepted by css style background
* `frame_border_width` - the width of the border around the whole collage
* `frame_border_style` - the style for the border around the whole collage
* `frame_border_color` - the color for the border around the whole collage
* `image_border_width` - the width of the border around each image
* `image_border_style` - the style for the border around each image
* `image_border_color` - the color for the border around each image
* `frame_style` - additional css_styling for the whole frame
* `image_style` - additional css_styling for each image
* `image_index_style` - additional css_styling for the image index
* `randomize` - randomize the order of the images
* `image_float` - you can reverse the layout of the collage by changing this to right
* `frame_align` - how to place the collage.  Choices are 'left', 'right', 'center', 'float_left', 'float_right'
* `link` - what to link the image to, options are 'permalink' or 'file'
* `rel` - rel to add for the link.  Useful for creating lightbox_esque effects
* `show_indices` - show the image number.  Useful for debugging or referring to photos in the collage.

= What layouts are available =

When you click on Settings -> topCollage, you will see sample layouts.  A programmer can add more layouts by using the filter `topcollage_layouts`.

There are seven layouts included.  See the screen shots for examples of some or visit the Settings page (after the plugin has been installed to see more examples). 

= I have more than one collage on my post, how do I make it so that different photos show up in the second one?

Use the shortcode variable `start_with` to specify the image number to start the collage with.  For example, `start_with=6` will start the collage with the 6th photo in your post's gallery.

You can reorder your photos by dragging and dropping them within the Gallery (click on the Upload/Insert image button)

== Changelog ==

= 0.5 =
* Initial Checkin
= 0.5.1 =
* Fixed collage in feeds issue

== Upgrade Notice ==
