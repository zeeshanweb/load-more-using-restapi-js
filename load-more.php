<?php
/**
 * Plugin Name: WP Load More Using RSET API and Javscript
 *
 * Text Domain:  wp-load-more-rest-api
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package load-more
 */

// Disable direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WP_LOAD_MORE_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

add_action( 'wp_enqueue_scripts', 'my_load_scripts' );
/**
 * Enqueue script for ajax load more using rest API.
 */
function my_load_scripts() {
	global $post;
	$version      = time();
	$endpoint     = get_rest_url( null, 'wp/v2/posts' );
	$post_content = '<div class="wp-grid-item"><a href="[post_url]" target="_blank"><span class="wp-thumbnail-default"><img src="[post_attachment]" alt="[alt_text]" data-pin-nopin="true" /></span><span class="wp-thumbnail-title">[post_title]</span></a></div>';
	wp_enqueue_script( 'load_more_script', WP_LOAD_MORE_PLUGIN_URL . '/js/load_more.js', array(), $version, true );
	// Localize the script with rest url.
	$load_more_data = array(
		'rest_api_url'       => $endpoint,
		'thumbnails_default' => WP_LOAD_MORE_PLUGIN_URL . '/images/no_image.png',
		'posts_per_page'     => 2,
		'post_limit'         => 10,
		'post_container'     => '.wp-posts',
		'post_content'       => $post_content,
	);
	wp_localize_script( 'load_more_script', 'load_more_script', $load_more_data );
}
add_shortcode( 'my_load_more_shortcode', 'my_load_more_shortcode' );
/**
 * Render load more posts.
 *
 * @return string
 */
function my_load_more_shortcode() {
	ob_start();
	?>
	<h3><?php esc_html_e( 'Load More Posts', 'wp-load-more-rest-api' ); ?></h3>
	<div class="wp-posts wp-grid-container"></div>
	<div class="wp-load-more">
		<button style="cursor: pointer;" type="button" class="wp-btn-load-more" id="wp-load-more" onClick="WPLoadMorePosts.wp_load_more_init()"><?php esc_html_e( 'Load More', 'wp-load-more-rest-api' ); ?></button>
	</div>
	<style>
	.wp-btn-load-more {
		position: relative;
		padding: 0 20px;
		font-size: 15px;
		line-height: 1;
		border: none;
		width: auto;
		height: 43px;
	}
	.wp-btn-load-more.loading {
		cursor: wait;
		outline: 0;
		padding-left: 44px;
	}
	.wp-btn-load-more.loading:before {
		background: #fff url('<?php echo WP_LOAD_MORE_PLUGIN_URL; ?>/images/wp_load_spin.svg') no-repeat center center;
		width: 30px;
		height: 31px;
		margin: 6px;	
	}
	.wp-btn-load-more.loading:before {
		left: 0;
		top: 0;
		border-radius: 3px;
		display: inline-block;
		z-index: 0;
		content: '';
		position: absolute;
	}
	.wp-grid-container {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 10px;
	grid-auto-rows: minmax(100px, auto);
	}
	.wp-grid-item {
	padding: 10px;
	}
	</style>
	<?php
	return ob_get_clean();
}
