<?php
/**
 * Plugin Name: WP Icon API
 * Plugin URI: https://github.com/draganescu/wp-icon-api
 * Description: A plugin that registers icons and makes them available for use in the block editor.
 * Version: 0.1.0
 * Author: Andrei Draganescu
 * Author URI: https://www.andreidraganescu.info/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-icon-api
 * Domain Path: /languages
 */
 
 defined( 'ABSPATH' ) || exit;

 /**
 * Load all translations for our plugin from the MO file.
 */
function wp_icon_api_load_textdomain() {
	load_plugin_textdomain( 'wp-icon-api', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'init', 'wp_icon_api_load_textdomain' );

/**
 * Registers an icon for use in the block editor.
 *
 * @package WP_Icon_API
 * @since 0.1.0
 * @param array $options {
 *     An array of options for registering an icon.
 *     @type string $name        The name of the icon.
 *     @type string $src         The URL of the icon SVG file.
 *     @type string $label       The label of the icon.
 *     @type string $description The description of the icon.
 *     @type array  $keywords    The keywords of the icon.
 *     @type string $category    The category of the icon.
 * }
 * @return bool True on success, false on failure.
 */
function wp_icon_api_register_icon( $options ) {
	// Validate required fields
	if ( ! isset( $options['name'] ) || ! isset( $options['src'] ) ) {
		return false;
	}

	// Sanitize options
	$sanitized_options = array(
		'name'        => sanitize_text_field( $options['name'] ),
		'src'         => esc_url_raw( $options['src'] ),
		'label'       => isset( $options['label'] ) ? sanitize_text_field( $options['label'] ) : '',
		'description' => isset( $options['description'] ) ? sanitize_textarea_field( $options['description'] ) : '',
		'keywords'    => isset( $options['keywords'] ) && is_array( $options['keywords'] ) 
			? array_map( 'sanitize_text_field', $options['keywords'] ) 
			: array(),
		'category'    => isset( $options['category'] ) ? sanitize_text_field( $options['category'] ) : 'general',
	);

	// Add icons to the registry
	return wp_icon_api_registry()->register_icon( $sanitized_options );
}


/**
 * A registry of icons registered through the wp_icon_api_register_icon function.
 *
 * @package WP_Icon_API
 * @since 0.1.0
 */
class WP_Icon_API_Registry {

	/**
	 * An array of icons registered through the wp_icon_api_register_icon function.
	 *
	 * @var array
	 */
	private $icons = array();

	/**
	 * Cache for icons to improve performance.
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Registers an icon for use in the block editor.
	 *
	 * @since 0.1.0
	 * @param array $options {
	 *     An array of options for registering an icon.
	 *     @type string $name        The name of the icon.
	 *     @type string $src         The URL of the icon SVG file.
	 *     @type string $label       The label of the icon.
	 *     @type string $description The description of the icon.
	 *     @type array  $keywords    The keywords of the icon.
	 *     @type string $category    The category of the icon.
	 * }
	 * @return bool True on success, false on failure.
	 */
	public function register_icon( $options ) {
		if ( ! $this->validate_icon_options( $options ) ) {
			return false;
		}

		$this->icons[] = $options;
		$this->clear_cache();
		return true;
	}

	/**
	 * Validates icon options.
	 *
	 * @since 0.1.0
	 * @param array $options Icon options to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private function validate_icon_options( $options ) {
		return isset( $options['name'] ) && 
			   isset( $options['src'] ) && 
			   ! empty( $options['name'] ) && 
			   ! empty( $options['src'] );
	}

	/**
	 * Returns an array of icons registered through the wp_icon_api_register_icon function.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_icons() {
		if ( ! isset( $this->cache['all_icons'] ) ) {
			$this->cache['all_icons'] = $this->icons;
		}
		return $this->cache['all_icons'];
	}

	/**
	 * Searches icons by term.
	 *
	 * @since 0.1.0
	 * @param string $search_term Search term.
	 * @return array Filtered icons.
	 */
	public function search_icons( $search_term ) {
		$search_term = sanitize_text_field( $search_term );
		if ( empty( $search_term ) ) {
			return $this->get_icons();
		}

		$cache_key = 'search_' . md5( $search_term );
		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		$icons = $this->get_icons();
		$filtered_icons = array_filter( $icons, function ( $icon ) use ( $search_term ) {
			$match_name        = false !== stripos( $icon['name'], $search_term );
			$match_label       = isset( $icon['label'] ) && false !== stripos( $icon['label'], $search_term );
			$match_description = isset( $icon['description'] ) && false !== stripos( $icon['description'], $search_term );
			$match_keywords    = isset( $icon['keywords'] ) && is_array( $icon['keywords'] ) && 
								  in_array( strtolower( $search_term ), array_map( 'strtolower', $icon['keywords'] ) );
			$match_category    = isset( $icon['category'] ) && false !== stripos( $icon['category'], $search_term );

			return $match_name || $match_label || $match_description || $match_keywords || $match_category;
		});

		$this->cache[ $cache_key ] = array_values( $filtered_icons );
		return $this->cache[ $cache_key ];
	}

	/**
	 * Clears the registry cache.
	 *
	 * @since 0.1.0
	 */
	private function clear_cache() {
		$this->cache = array();
	}

}

/**
 * Returns an instance of the WP_Icon_API_Registry class.
 *
 * @package WP_Icon_API
 * @since 0.1.0
 * @return WP_Icon_API_Registry
 */
function wp_icon_api_registry() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new WP_Icon_API_Registry();
	}
	return $instance;
}

/**
 * Register REST API endpoints.
 *
 * @since 0.1.0
 */
function wp_icon_api_register_rest_routes() {
	// Register the icons endpoint
	register_rest_route(
		'wp-icon-api/v1',
		'/icons',
		array(
			'methods'             => 'GET',
			'callback'            => 'wp_icon_api_get_icons',
			'permission_callback' => 'wp_icon_api_check_permissions',
		)
	);

	// Register a rest route that searches for icons
	register_rest_route(
		'wp-icon-api/v1',
		'/icons/search',
		array(
			'methods'             => 'GET',
			'callback'            => 'wp_icon_api_search_icons',
			'permission_callback' => 'wp_icon_api_check_permissions',
			'args'                => array(
				'search' => array(
					'description' => 'Search term for filtering icons.',
					'type'        => 'string',
					'required'    => false,
				),
				'category' => array(
					'description' => 'Category to filter icons by.',
					'type'        => 'string',
					'required'    => false,
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'wp_icon_api_register_rest_routes' );

/**
 * Check if user has permission to access icons.
 *
 * @since 0.1.0
 * @return bool True if user has permission, false otherwise.
 */
function wp_icon_api_check_permissions() {
	return current_user_can( 'edit_posts' );
}

/**
 * REST API callback to get all icons.
 *
 * @since 0.1.0
 * @return WP_REST_Response Response containing icons.
 */
function wp_icon_api_get_icons() {
	$icons = wp_icon_api_registry()->get_icons();
	return new WP_REST_Response( $icons, 200 );
}

/**
 * REST API callback to search icons.
 *
 * @since 0.1.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response Response containing filtered icons.
 */
function wp_icon_api_search_icons( $request ) {
	$search_term = $request->get_param( 'search' );
	$category    = $request->get_param( 'category' );

	$icons = wp_icon_api_registry()->get_icons();

	// Filter by search term
	if ( ! empty( $search_term ) ) {
		$icons = wp_icon_api_registry()->search_icons( $search_term );
	}

	// Filter by category
	if ( ! empty( $category ) ) {
		$icons = array_filter( $icons, function ( $icon ) use ( $category ) {
			return isset( $icon['category'] ) && $icon['category'] === $category;
		});
	}

	return new WP_REST_Response( array_values( $icons ), 200 );
}
/**
 * Register frontend scripts.
 *
 * @since 0.1.0
 */
function wp_icon_api_register_scripts() {
	wp_register_script(
		'wp-icon-api-htm',
		plugins_url( 'htm.js', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'htm.js' ),
		true
	);
}
add_action( 'init', 'wp_icon_api_register_scripts' );

add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Load scripts as ES modules.
 *
 * @since 0.1.0
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source.
 * @return string Modified script tag.
 */
function wp_icon_api_load_as_module( $tag, $handle, $src ) {
	if ( 'wp-icon-api-htm' === $handle || 'wp-icon-api-icon-block-editor-script' === $handle ) {
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'wp_icon_api_load_as_module', 10, 3 );

/**
 * Register the icon block.
 *
 * @since 0.1.0
 */
function wp_icon_api_register_block() {
	register_block_type( __DIR__ );
}
add_action( 'init', 'wp_icon_api_register_block' );

/**
 * Load sample icons.
 *
 * @since 0.1.0
 */
require_once __DIR__ . '/sample.php';