<?php
/**
 * Register sample icons for the WP Icon API plugin.
 *
 * @package WP_Icon_API
 * @since 0.1.0
 */

/**
 * Register sample icons in batch.
 *
 * @since 0.1.0
 */
function wp_icon_api_register_sample_icons() {
	$samples_folder = __DIR__ . '/samples/';
	
	// Validate samples folder exists
	if ( ! is_dir( $samples_folder ) ) {
		return;
	}

	$icon_files = array_diff( scandir( $samples_folder ), array( '.', '..' ) );
	$icons_to_register = array();

	foreach ( $icon_files as $filename ) {
		// Only process SVG files
		if ( 'svg' !== strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
			continue;
		}

		// Generate icon name from filename
		$icon_name = str_replace( '-', ' ', $filename );
		$icon_name = ucwords( $icon_name );
		$icon_name = str_replace( '.svg', '', $icon_name );
		
		// Generate keywords from filename
		$keywords = explode( '-', str_replace( '.svg', '', $filename ) );
		$keywords = array_map( 'trim', $keywords );

		// Determine category based on filename
		$category = 'general';
		if ( strpos( $filename, 'cloud' ) !== false ) {
			$category = 'weather';
		} elseif ( strpos( $filename, 'wordpress' ) !== false ) {
			$category = 'brand';
		} elseif ( in_array( str_replace( '.svg', '', $filename ), array( 'check', 'gear', 'alarm-clock' ) ) ) {
			$category = 'interface';
		}

		$icons_to_register[] = array(
			'name'        => $icon_name,
			'src'         => plugins_url( 'samples/' . $filename, __FILE__ ),
			'label'       => $icon_name,
			'description' => sprintf( 
				/* translators: %s: Icon name */
				__( 'An %s icon.', 'wp-icon-api' ), 
				$icon_name 
			),
			'keywords'    => $keywords,
			'category'    => $category,
		);
	}

	// Register all icons at once
	foreach ( $icons_to_register as $icon_options ) {
		wp_icon_api_register_icon( $icon_options );
	}
}

// Register icons on init to ensure all functions are available
add_action( 'init', 'wp_icon_api_register_sample_icons' );