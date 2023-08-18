<?php

$samplesFolder = __DIR__ . '/samples/';
$iconFiles = array_diff(
	scandir($samplesFolder),
	array('.', '..')
);

foreach ($iconFiles as $filename) {
	// call register_icon to register an icon
	// on rest api init hook

	// replace dash with space
	// capitalize
	// remove the .svg extension
	$iconName = str_replace('-', ' ', $filename);
	$iconName = ucwords($iconName);
	$iconName = str_replace('.svg', '', $iconName);
	
	$keywords = explode('-', str_replace('.svg', '', $filename));

	add_action( 'rest_api_init', function () use ($iconName, $filename, $keywords) {
		register_icon( array(
			'name' => $iconName,
			'src' => plugins_url( 'samples/' . $filename, __FILE__ ),
			'label' => __( $iconName, 'wp-icon-api' ),
			'description' => __( 'An ' . $iconName . ' icon.', 'wp-icon-api' ),
			'keywords' => $keywords,
		) );
	} );
}