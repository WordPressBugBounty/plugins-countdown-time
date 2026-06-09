<?php
/**
 * Block rendering template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block registration object.
 *
 * @package CTB
 */

if ( !defined( 'ABSPATH' ) ) { exit; }

$id = wp_unique_id( 'ctbCountdownTime-' );
?>
<div
	<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is properly escaped ?>
	<?php echo get_block_wrapper_attributes(); ?>
	id='<?php echo esc_attr( $id ); ?>'
	data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ?? [] ) ); ?>'
	data-content='<?php echo esc_attr( wp_json_encode( $content ?? '' ) ); ?>'
></div>