<?php
if ( !class_exists('Crop_Image_Position') ):

	class Crop_Image_Position {

		const version = '1.0';
		static $plugin;
		static $plugin_url;
		static $language_domain;
		static $default_position;

		//Init
		function __construct( $plugin = null ) {

			if ( !$plugin ){ return; }
			self::$plugin     = $plugin;
			self::$plugin_url = plugin_dir_url($plugin);
			self::$language_domain = 'crop_image_position';
			self::$default_position = 4;

			load_plugin_textdomain( self::$language_domain, false, dirname( plugin_basename( $plugin ) ) . '/languages/' );

			// Action and filter hooks
			global $pagenow;
			$cip_upload_controls_action=$pagenow == 'media-new.php' ? 'post-upload-ui' : 'pre-upload-ui';
			add_action( $cip_upload_controls_action , array( $this, 'cip_upload_controls' ) );
			add_action( 'pre-plupload-upload-ui', array( $this, 'cip_scripts_styles' ) );
			add_action( 'wp_ajax_cip', array( $this, 'cip_ajax_callback' ) );
			add_filter( 'image_resize_dimensions', array( $this, 'cip_image_resize_dimensions' ), 10, 6 );

		}

		function cip_scripts_styles() {
			wp_enqueue_style( 'crop_imageposition', self::$plugin_url.'assets/css/styles.css', false, self::version, 'all' );
			wp_enqueue_script( 'crop_imageposition_js', self::$plugin_url.'assets/js/scripts.js', array('jquery'), self::version, true );
			wp_localize_script( 'crop_imageposition_js', 'cipL10n', array('_wpnonce' => wp_create_nonce('cip_update_position')) );
		}

		// ajax callback
		function cip_ajax_callback() {
			if ( empty($_REQUEST['_wpnonce']) || !check_ajax_referer( 'cip_update_position', '_wpnonce', false ) || !current_user_can('upload_files') ) {
				echo self::$default_position;
				die();
			}
			$crop_position = is_numeric($_POST['cip_position_option']) ? min( 8, (int)$_POST['cip_position_option']) : self::$default_position;
			update_option( 'crop_image_position_option', array( 'position' => $crop_position ) );
			echo $crop_position;
			die();
		}

		// get the crop image position option
		function cip_get_position() {
			$options = get_option( 'crop_image_position_option' );
			return is_array($options) && isset($options['position']) && is_numeric($options['position']) ? min( 8, (int)$options['position'] ) : self::$default_position;
		}

		// reset crop image position option
		function cip_reset_position() {
			delete_option( 'crop_image_position_option' );
			return true;
		}

		// write upload controls
		function cip_upload_controls() {
			$crop_position = self::cip_get_position();
			?><div class="crop--image--position">
				<h2><?php _e( 'Select Crop Image Position:', self::$language_domain );?></h2>
				<div class="crop--image--position__controls">

					<img src='//lorempixel.com/210/210/people/' />

					<span class="button button-cip<?php echo $crop_position == 0 ? ' button-primary' : ''; ?>" data-cip="0"></span>
					<span class="button button-cip<?php echo $crop_position == 1 ? ' button-primary' : ''; ?>" data-cip="1"></span>
					<span class="button button-cip<?php echo $crop_position == 2 ? ' button-primary' : ''; ?>" data-cip="2"></span>

					<span class="button button-cip<?php echo $crop_position == 3 ? ' button-primary' : ''; ?>" data-cip="3"></span>
					<span class="button button-cip<?php echo $crop_position == 4 ? ' button-primary' : ''; ?>" data-cip="4"></span>
					<span class="button button-cip<?php echo $crop_position == 5 ? ' button-primary' : ''; ?>" data-cip="5"></span>

					<span class="button button-cip<?php echo $crop_position == 6 ? ' button-primary' : ''; ?>" data-cip="6"></span>
					<span class="button button-cip<?php echo $crop_position == 7 ? ' button-primary' : ''; ?>" data-cip="7"></span>
					<span class="button button-cip<?php echo $crop_position == 8 ? ' button-primary' : ''; ?>" data-cip="8>"></span>

				</div>
			</div>
		<?php
		}

		/*
		* Since version 3.4, the image_resize_dimensions filter is used to filter
		* the thumbnail and alternative sizes dimensions of image assets during
		* resizing operations. This enables the override of WordPress default
		* behavior on image resizing, including the thumbnail cropping.
		** reference: http://codex.wordpress.org/Plugin_API/Filter_Reference/image_resize_dimensions
		*/
		function cip_image_resize_dimensions( $payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop) {

			// Change this to a conditional that decides whether you
			// want to override the defaults for this image or not.;
			if( false ){
				return $payload;
			}

			// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
			if ( $crop ) {
				$aspect_ratio = $orig_w / $orig_h;
				$new_w = min($dest_w, $orig_w);
				$new_h = min($dest_h, $orig_h);

				$new_w = $new_w ? $new_w : intval($new_h * $aspect_ratio);
				$new_h = $new_h ? $new_h : intval($new_w / $aspect_ratio);

				$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

				$crop_w = round($new_w / $size_ratio);
				$crop_h = round($new_h / $size_ratio);

				// positions
				$__x_left = 0;
				$__x_center = floor( ($orig_w - $crop_w) / 2 );
				$__x_right = floor( ($orig_w - $crop_w) );

				$__y_top = 0;
				$__y_center = floor( ($orig_h - $crop_h) / 2 );
				$__y_bottom = floor( ($orig_h - $crop_h) );

				$crop_position = self::cip_get_position();

				if ( $crop_position == 0 ) {
					$s_x = $__x_left;
					$s_y = $__y_top;
				} elseif ( $crop_position == 1 ) {
					$s_x = $__x_center;
					$s_y = $__y_top;
				} elseif ( $crop_position == 2 ) {
					$s_x = $__x_right;
					$s_y = $__y_top;
				} elseif ( $crop_position == 3 ) {
					$s_x = $__x_left;
					$s_y = $__y_center;
				} elseif ( $crop_position == 4 ) {
					$s_x = $__x_center;
					$s_y = $__y_center;
				} elseif ( $crop_position == 5 ) {
					$s_x = $__x_right;
					$s_y = $__y_center;
				} elseif ( $crop_position == 6 ) {
					$s_x = $__x_left;
					$s_y = $__y_bottom;
				} elseif ( $crop_position == 7 ) {
					$s_x = $__x_center;
					$s_y = $__y_bottom;
				} elseif ( $crop_position == 8 ) {
					$s_x = $__x_right;
					$s_y = $__y_bottom;
				} else {
					$s_x = $__x_center;
					$s_y = $__y_center;
				}

				// self::cip_reset_position();

			// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
			} else {
				$crop_w = $orig_w;
				$crop_h = $orig_h;

				$s_x = 0;
				$s_y = 0;

				list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
			}

			// if the resulting image would be the same size or larger we don't want to resize it
			if ( $new_w >= $orig_w && $new_h >= $orig_h )
				return false;

			// the return array matches the parameters to imagecopyresampled()
			// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
			return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

		}

	}

endif;
