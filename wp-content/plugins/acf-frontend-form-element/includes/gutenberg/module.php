<?php
namespace ACFFrontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'ACFFrontend_Gutenberg' ) ) :

	class ACFFrontend_Gutenberg{

	public function register_blocks() {
		$asset_file = require_once( __DIR__ . '/assets/js/acf-frontend-form.asset.php');
		wp_register_script( 'acf-frontend-form', ACFEF_URL . 'includes/gutenberg/assets/js/acf-frontend-form.js', $asset_file['dependencies'],
		$asset_file['version'] );
	
		register_block_type('acf-frontend/form', [
			'editor_script' => 'acf-frontend-form',
			'render_callback' => array( $this, 'render_form_block' ),
			'attributes' => [
				'formID' => [
					'type' => 'number',
					'default' => 0
				],
				'editMode' => [
					'type' => 'boolean',
					'default' => 0
				]
			]
		]);
	}

	public function render_form_block($attr, $content) {
		$render = '';
		if ( $attr['formID'] == 0 ){
			return __( 'Please Select a Form', 'acf-frontend-form-element' );
		}
		if ( get_post_type( $attr['formID'] ) == 'acf_frontend_form' ){
			ob_start();
			acff()->form_display->render_form( $attr['formID'], $attr['editMode'] );
			$render = ob_get_contents();
			ob_end_clean();	
		}
		return $render;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}
}

acff()->gutenberg = new ACFFrontend_Gutenberg();

endif;	