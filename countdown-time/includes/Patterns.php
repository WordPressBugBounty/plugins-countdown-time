<?php
namespace CTB;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Patterns
 *
 * Handles Gutenberg block patterns registration from pattern.json file.
 *
 * @package CTB
 */
class Patterns{
	/**
	 * Constructor. Sets up action hook for plugin init.
	 */
	public function __construct(){
		add_action('init', [$this, 'onPluginsLoaded']);
	}

	/**
	 * Decode pattern.json, filter Pro patterns if using the free version,
	 * register block pattern category and individual block patterns.
	 *
	 * @return void
	 */
	function onPluginsLoaded(){
		$patterns = wp_json_file_decode( __DIR__ . '/pattern.json', [ 'associative' => true ] );

		// Register Pattern Category
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category( 'ctbPattern', [ 'label' => __( 'Countdown Time', 'countdown-time' ) ] );
		}

		// Register Pattern
		if ( !empty( $patterns ) ) {
			foreach ( $patterns as $pattern ) {
				if ( function_exists( 'register_block_pattern' ) ) {
					register_block_pattern( $pattern['name'], [
						'title'			=> $pattern['title'],
						'content'		=> $pattern['content'],
						'description'	=> $pattern['description'],
						'categories'	=> [ 'ctbPattern' ],
						'keywords'		=> $pattern['keywords'],
						'blockTypes'	=> $pattern['blockTypes'],
						'viewportWidth'	=> 1200
					] );
				}
			}
		}
	}
}
new Patterns();