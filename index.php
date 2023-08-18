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
 * @package wp-icons-api
 * @param array $options {
 * 	 An array of options for registering an icon.
 * 	 @type string $name The name of the icon.
 * 	 @type string $src The URL of the icon SVG file.
 * 	 @type string $label The label of the icon.
 * 	 @type string $description The description of the icon.
 * 	 @type string $keywords The keywords of the icon.
 * 	 @type string $svg The SVG markup of the icon.
 */
function register_icon( $options ) {
	// add icons to the registry
	wp_icon_registry()->register_icon( $options );
}


/**
 * A registry of icons registered through the register_icon function.
 * @package wp-icons-api
 * @since 0.1.0
 */
class WP_Icon_Registry {
	
	/**
	 * An array of icons registered through the register_icon function.
	 * @var array
	 */
	private $icons = array();

	/**
	 * Registers an icon for use in the block editor.
	 * @package wp-icons-api
	 * @param array $options {
	 * 	 An array of options for registering an icon.
	 * 	 @type string $name The name of the icon.
	 * 	 @type string $src The URL of the icon SVG file.
	 * 	 @type string $label The label of the icon.
	 * 	 @type string $description The description of the icon.
	 * 	 @type string $keywords The keywords of the icon.
	 * 	 @type string $svg The SVG markup of the icon.
	 * }
	 */
	public function register_icon( $options ) {
		$this->icons[] = $options;
	}

	/**
	 * Returns an array of icons registered through the register_icon function.
	 * @package wp-icons-api
	 * @return array
	 */
	public function get_icons() {
		return $this->icons;
	}

}

/**
 * Returns an instance of the WP_Icon_Registry class.
 * @package wp-icons-api
 * @return WP_Icon_Registry
 */
function wp_icon_registry() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new WP_Icon_Registry();
	}
	return $instance;
}

// register the plugin on a rest api hook
add_action( 'rest_api_init', function () {
	// register the icons endpoint
	register_rest_route( 'wp/v2', '/icons', array(
		'methods' => 'GET',
		'callback' => function () {
			// return the icons registered through the register_icon function
			return wp_icon_registry()->get_icons();
		},
	) );
	// register a rest route that searches for icons
	register_rest_route( 'wp/v2', '/icons/search', array(
		'methods' => 'GET',
		'callback' => function ( $request ) {
			// get the search term from the request
			$search_term = $request->get_param( 'search' );
			// get the icons registered through the register_icon function
			$icons = wp_icon_registry()->get_icons();
			// filter the icons by the search term
			$icons = array_filter( $icons, function ( $icon ) use ( $search_term ) {
				// check if the icon name contains the search term
				$match = ( stripos( $icon['name'], $search_term ) ) || ( stripos( $icon['label'], $search_term ) ) || ( stripos( $icon['description'], $search_term ) ) || ( in_array( $search_term, $icon['keywords'] ) );
				return false !== $match;
			} );
			// return the filtered icons
			return array_values( $icons );
		},
	) );
} );
add_action( 'init', function() {
	wp_register_script(
		'wp-icon-api-htm',
		plugins_url( 'htm.js', __FILE__ ),
		[], // no dependencies
		filemtime( plugin_dir_path( __FILE__ ) . 'htm.js' ),
		true // in footer
	);
} );

add_filter( 'should_load_separate_core_block_assets', '__return_true' );

add_filter("script_loader_tag", "load_as_module", 10, 3);
function load_as_module($tag, $handle, $src)
{
    if ( 'wp-icon-api-htm' === $handle ) {
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }

		if ( 'wp-icon-api-icon-block-editor-script' === $handle ) {
			$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		}

    return $tag;
}

// register a new icon block
// the block has an edit component and is a dynamic block
add_action( 'init', function () {
	// Register the block by passing the location of block.json to register_block_type.
	register_block_type( __DIR__ );
} );

include 'sample.php';