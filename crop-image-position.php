<?php
/*
Plugin Name: Crop Image Position
Plugin URI: https://github.com/mikiamomik/wp-crop-image-position/
Description: Wordpress Plugin that allows you to select the crop position of your thumbnails before upload it.
Author: Bernardo Picaro
Text Domain: crop_image_position
Domain Path: /languages
Version: 1.0.1
*/


add_action('admin_init', 'crop_image_position_init');
function crop_image_position_init() {
	if ( !class_exists('Crop_Image_Position') ){
		include( plugin_dir_path(__FILE__).'lib/class-crop-image-position.php' );
		new Crop_Image_Position( __FILE__ );
	}
}
