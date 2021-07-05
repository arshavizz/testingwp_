<?php
namespace ACFFrontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ACFEF_Uploads_Privacy_Settings{
	
	public function get_name() {
		return 'uploads_privacy';
	}
	

	function filter_by_author_field($args) {
		$value = ( get_option( 'filter_media_author' ) == 1 ) ? $value  = ' checked' : '';
    	echo '<input type="checkbox" id="filter_media_author" name="filter_media_author" value="1"' . $value . '/>';
	}
	
	function filter_media_author( $query ){
    	if ( get_option( 'filter_media_author' ) == '1' ) {
			$user_id = get_current_user_id();
			if ( $user_id && ! current_user_can( 'activate_plugins' ) ) {
				$query['author'] = $user_id;
			}
		}
		return $query;
	}

	public function acfef_get_settings_fields( $field_keys ){
		$default = get_option( 'local_avatar' ) ? get_option( 'local_avatar' ) : 'none';

		$local_fields = array(
			array(
				'key' => 'filter_media_author',
				'label' => __( 'Media Uploads Privacy', 'acf-frontend-form-element' ),
				'name' => 'filter_media_author',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'ui' => 1,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
		);

		foreach( $local_fields as $local_field ){
			$local_field['value'] = get_option( $local_field['key'] );
			acf_add_local_field( $local_field );
			$field_keys[] = $local_field['key'];
		}
		return $field_keys;
	} 
	
	public function __construct() {	
		add_filter( 'ajax_query_attachments_args', [ $this, 'filter_media_author'] );
		add_filter( 'acfef/uploads_privacy_fields', [ $this, 'acfef_get_settings_fields'] );
	}
	
}
new ACFEF_Uploads_Privacy_Settings( $this );