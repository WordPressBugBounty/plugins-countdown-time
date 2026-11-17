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
			$labels = $this->getPatternLabels();

			foreach ( $patterns as $pattern ) {
				if ( function_exists( 'register_block_pattern' ) ) {
					$label = $labels[ $pattern['name'] ] ?? [];

					register_block_pattern( $pattern['name'], [
						'title'			=> $label['title'] ?? $pattern['title'],
						'content'		=> $pattern['content'],
						'description'	=> $label['description'] ?? $pattern['description'],
						'categories'	=> [ 'ctbPattern' ],
						'keywords'		=> $pattern['keywords'],
						'blockTypes'	=> $pattern['blockTypes'],
						'viewportWidth'	=> 1200
					] );
				}
			}
		}
	}

	/**
	 * Translatable titles and descriptions for the patterns in pattern.json, keyed by pattern name.
	 *
	 * @return array
	 */
	private function getPatternLabels() {
		return [
			'ctb-patterns/countdown-2'	=> [
				'title'			=> __( 'Countdown Time 2', 'countdown-time' ),
				'description'	=> __( 'Countdown Time Design', 'countdown-time' )
			],
			'ctb-patterns/countdown-3'	=> [
				'title'			=> __( 'Countdown Time 3', 'countdown-time' ),
				'description'	=> __( 'Countdown Time Design', 'countdown-time' )
			],
			'ctb-patterns/countdown-4'	=> [
				'title'			=> __( 'Countdown Time 4', 'countdown-time' ),
				'description'	=> __( 'Countdown Time Design', 'countdown-time' )
			],
			'ctb-patterns/countdown-5'	=> [
				'title'			=> __( 'Countdown Time 5', 'countdown-time' ),
				'description'	=> __( 'Countdown Time Design', 'countdown-time' )
			]
		];
	}
}
new Patterns();