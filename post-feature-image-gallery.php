<?php
/**
 * Plugin Name: Post Feature Image Gallery
 * Plugin URI: https://wrapcastle.com/plugin/pfig
 * Description: This is our post thumbnail plugin.
 * Version: 1.0
 * Author: wrapcastle
 * Author URI: https://wrapcastle.com
 * License: GPLv2 or later
 * Text Domain: pfig
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pfig_load_textdomain() {
	load_plugin_textdomain('pfig', false, dirname(__FILE__) . '/languages');
}
add_action('plugins_loaded', 'pfig_load_textdomain');

// Required Files
require_once( plugin_dir_path( __FILE__ ) . '/lib/tgm/example.php' );

// Disable srcset
add_filter( 'max_srcset_image_width', create_function( '', 'return 1;' ) );

// Activation Notice
register_activation_hook( __FILE__, 'fx_admin_notice_example_activation_hook' );

// Register Image
add_image_size( 'pfig-square', 300, 200, true );

function fx_admin_notice_example_activation_hook() {
	set_transient( 'fx-admin-notice-example', true, 5 );
}

add_action( 'admin_notices', 'fx_admin_notice_example_notice' );

function fx_admin_notice_example_notice() {
	if ( get_transient( 'fx-admin-notice-example' ) ) {
		?>
        <div class="updated notice is-dismissible">
            <p><?php _e( "Thank you for using this plugin! We'd like to recommend you to regenerate your Post Thumbnail to get nice Gallery. Try", 'pfig' ); ?>
                <strong><a href="//wordpress.org/plugins/regenerate-thumbnails/">Regenerate Thumbnails</a></strong>.
            </p>
        </div>
		<?php
	}
}

add_action( 'wp_enqueue_scripts', 'pfig_assets' );
function pfig_assets() {
	wp_enqueue_style( 'pfig-prettyPhoto', PLUGINS_URL( 'assets/css/prettyPhoto.css', __FILE__ ) );
	wp_enqueue_style( 'pfig-pwtcss', PLUGINS_URL( 'assets/css/pfig.css', __FILE__ ) );

	wp_enqueue_script( 'pfig-prettyPhoto', PLUGINS_URL( 'assets/js/jquery.prettyPhoto.js', __FILE__ ), array(
		'jquery',
	), null, true );
	wp_enqueue_script( 'pfig-pfigjs', PLUGINS_URL( 'assets/js/pfig.js', __FILE__ ), array(
		'jquery',
		'pfig-prettyPhoto',
	), null, true );
}


add_action( 'widgets_init', 'pfig_thumbnail_widget' );

function pfig_thumbnail_widget() {
	register_widget( 'pfig_post_thumbnail' );
}

class pfig_post_thumbnail extends WP_Widget {
	public function __construct() {
		parent::__construct( 'pfig_post_thumbnail', 'Post Feature Image Gallery', array(
			'description' => __( 'Add a gallery from your recet post feature images.', 'pfig' ),
		) );
	}

	public function widget( $args, $instance ) {
		$title     = $instance['title'];
		$columns   = $instance['columns'];
		$total_img = $instance['total_img'];
		$order     = $instance['order'];

		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		?>
        <div class="pfig">
			<?php
			if ( $columns == 1 ) {
				$width = '100%';
			} else if ( $columns == 2 ) {
				$width = '50%';
			} else if ( $columns == 3 ) {
				$width = '33.333333%';
			} else if ( $columns == 4 ) {
				$width = '25%';
			}

			// Exclude Post if has not Feature image
			$thumbs = array(
				'post_type'      => 'post',
				'order'          => $order,
				'posts_per_page' => $total_img,
				'meta_query'     => array(
					array(
						'key' => '_thumbnail_id',
					),
				),
			);

			$pfig_post = new WP_Query( $thumbs );

			while ( $pfig_post->have_posts() ) : $pfig_post->the_post();
				?>
                <div class="pfig-item" style="width: <?php echo $width; ?>;">
                    <a href="<?php the_post_thumbnail_url( 'large' ); ?>" title="<a href='<?php the_permalink(); ?>'><?php the_title(); ?></a>">
						<?php
						// Check our image size
						global $_wp_additional_image_sizes;
						if ( array_key_exists( 'pfig-square', $_wp_additional_image_sizes ) ) {
							the_post_thumbnail( 'pfig-square' );
						} else {
							the_post_thumbnail( 'medium' );
						}
						?>
                    </a>
                </div>
			<?php
			endwhile;
			wp_reset_query();
			?>
        </div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title     = $instance['title'];
		$columns   = $instance['columns'];
		$total_img = $instance['total_img'];
		$order     = $instance['order'];
		?>
        <p>
            <label for=""><?php _e( 'Title:', 'pfig' ); ?></label>
            <input type="text" class="widefat" value="<?php echo $title; ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>">
        </p>
        <p>
            <label for=""><?php _e( 'Columns on Desktop:', 'pfig' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'columns' ); ?>" class="widefat">
                <option value=""><?php _e( 'Select', 'pfig' ); ?></option>
                <option <?php echo $columns == 1 ? 'selected' : ''; ?> value="1">1</option>
                <option <?php echo $columns == 2 ? 'selected' : ''; ?> value="2">2</option>
                <option <?php echo $columns == 3 ? 'selected' : ''; ?> value="3">3</option>
                <option <?php echo $columns == 4 ? 'selected' : ''; ?> value="4">4</option>
            </select>
        </p>
        <p>
            <label for=""><?php _e( 'Order:', 'pfig' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat">
                <option value=""><?php _e( 'Select', 'pfig' ); ?></option>
                <option <?php echo $order == 'DESC' ? 'selected' : ''; ?> value="DESC">DESC</option>
                <option <?php echo $order == 'ASC' ? 'selected' : ''; ?> value="ASC">ASC</option>
            </select>
        </p>
        <p>
            <label for=""><?php _e( 'Total Images:', 'pfig' ); ?></label>
            <input type="number" class="widefat" value="<?php echo $total_img; ?>"
                   name="<?php echo $this->get_field_name( 'total_img' ); ?>">
        </p>
		<?php
	}
}
