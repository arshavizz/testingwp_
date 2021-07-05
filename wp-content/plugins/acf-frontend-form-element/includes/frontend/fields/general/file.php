<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists('acf_frontend_file_field') ) :

	class acf_frontend_file_field {
		

		function file_folders_setting( $field ) {
			acf_render_field_setting( $field, array(
				'label'			=> __('Happy Files Folder','acf'),
				'instructions'	=> __('Limit the media library choice to specific Happy Files Categories','acf'),
				'type'			=> 'radio',
				'name'			=> 'happy_files_folder',
				'layout'		=> 'horizontal',
				'default_value' => 'all',
				'choices' 		=> acf_frontend_get_image_folders(),
			));	
		}

		function happy_files_folder( $query ) {
			if( empty( $_POST['query']['_acfuploader'] ) ) {
				return $query;
			}
			
			// load field
			$field = acf_get_field( $_POST['query']['_acfuploader'] );
			if( !$field ) {
				return $query;
			}
			
			if( !isset( $field['happy_files_folder'] ) || $field['happy_files_folder'] == 'all' ){
				return $query;
			}

			
			if( isset( $query['tax_query'] ) ){
				$tax_query = $query['tax_query'];
			}else{
				$tax_query = [];
			}
			
			$tax_query[] = array(
				'taxonomy' => 'happyfiles_category',
				'field' => 'name',
				'terms' => $field['happy_files_folder'],
			);
			$query['tax_query'] = $tax_query;
			
			return $query;
		}


		public function update_attachments_value( $value, $post_id = false, $field = false ){
			if( is_numeric( $post_id ) && $value ){
				remove_filter( 'acf/update_value/type=' . $field['type'], [ $this, 'update_attachments_value'], 8, 3 );
				$post = get_post( $post_id );
				if( wp_is_post_revision( $post ) ){
					$post_id = $post->post_parent;
				}
				foreach( $value as $attach_id ){
					$attach_id = (int) $attach_id;
					acf_connect_attachment_to_post( (int) $attach_id, $post_id );					
				}
				add_filter( 'acf/update_value/type=' . $field['type'], [ $this, 'update_attachments_value'], 8, 3 );
			}
			
			return $value;
		}	
		public function update_attachment_value( $value, $post_id = false, $field = false ){									
			if( is_numeric( $post_id ) ){  				
				remove_filter( 'acf/update_value/type=' . $field['type'], [ $this, 'update_attachment_value'], 8, 3 );
				$value = (int) $value;
				$post = get_post( $post_id );
				if( wp_is_post_revision( $post ) ){
					$post_id = $post->post_parent;
				}
				acf_connect_attachment_to_post( $value, $post_id );

				add_filter( 'acf/update_value/type=' . $field['type'], [ $this, 'update_attachment_value'], 8, 3 );

			}	
			return $value;
		}

		public function upload_button_text_setting( $field ) {
			acf_render_field_setting( $field, array(
				'label'			=> __('Button Text'),
				'name'			=> 'button_text',
				'type'			=> 'text',
				'placeholder'	=> __( 'Add Image', 'acf-frontend-form-element' ),
			) );

		}

		public function __construct() {
			if( defined( 'HAPPYFILES_VERSION' ) ){
				$file_fields = array( 'image', 'file', 'gallery', 'featured_image', 'main_image', 'product_images' );
				foreach( $file_fields as $type ){
					add_action( 'acf/render_field_settings/type=' .$type,  [ $this, 'file_folders_setting'] );
				}				
				add_filter( 'ajax_query_attachments_args', [ $this, 'happy_files_folder'] );
			}


			$multiple_files = array( 'gallery', 'upload_images', 'product_images' );
			foreach( $multiple_files as $type ){
				add_filter( 'acf/update_value/type=' .$type, [ $this, 'update_attachments_value'], 8, 3 );
				add_action( 'acf/render_field_settings/type=' .$type,  [ $this, 'upload_button_text_setting'] );	
			}

			$single_file = array( 'file', 'image', 'upload_image', 'featured_image', 'main_image' );
			foreach( $single_file as $type ){
				add_filter( 'acf/update_value/type=' .$type, [ $this, 'update_attachment_value'], 8, 3 );
				add_action( 'acf/render_field_settings/type=' .$type,  [ $this, 'upload_button_text_setting'] );	
			}
					


		}
	}

	new acf_frontend_file_field();

endif;

