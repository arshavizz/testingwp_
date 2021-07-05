<?php
namespace ACFFrontend\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'ACFFrontendForm' ) ) :

class Dynamic_Values{

	
/* 	function implode_recur($separator, $arrayvar) {
		$return = "";
		foreach ($arrayvar as $av)
		if (is_array ($av)) 
			$return .= $this->implode_recur($separator, $av); // Recursive array 
		else                   
			$return .= $separator.$av;
	
		return $return . '<br>';
	} */

	function get_user_field( $field, $user = null, $context = 'display' ) {
		if( is_object( $user ) ) $user = $user->ID;
		if( is_array( $user ) ) $user = $user['ID'];

		$user_data = get_userdata( $user );
	
		if ( ! $user_data ) {
			return '';
		}
	
		if ( ! isset( $user_data->$field ) ) {
			return '';
		}
	
		return sanitize_user_field( $field, $user_data->$field, $user, $context );
	}


	function get_dynamic_values( $text, $post_id = 0, $form = array() ) {

		if( empty( $form['record'] ) ) $form = $this->get_record();

		// Find all merge tags
		if ( preg_match_all( "/\[(.+?)?\]/", $text, $matches ) ) {
			foreach ( $matches[1] as $i=>$tag ){
				$value = '';
				
				if( isset( $form['record'] ) && 'all_fields' == $tag ) $value = $this->get_all_fields_values( $tag, $form );

				if( ! $value ) $value = $this->get_field_value( $tag, $form );
				if( ! $value ) $value = $this->get_sub_field_value( $tag, $form );
				if( ! $value ) $value = $this->get_post_value( $tag, $form );
				if( ! $value ) $value = $this->get_user_value( $tag, $form );
				
				
				$text = str_replace( $matches[0][ $i ], $value, $text );
			}
		
		}
		
		return $text;
	}

	public function get_record() {
		if ( ! isset( $_COOKIE['acf_form_record'] ) ) {
		  return false;
		}
	
		$record_key = $_COOKIE['acf_form_record'];
		$record_args = array(
			'post_type' => 'acf_form_record',
			'posts_per_page' => 1,
			'post_status' => 'all',
			'title'	=> $record_key,
		);
		if( get_posts( $record_args ) ){
			$record = get_posts( $record_args )[0];
			return json_decode( acf_decrypt( $record->post_content ), true );	;
		}else{
			return false;
		}		
	}

	function get_all_fields_values( $tag, $form ) {
		$fields = $form['record'];
		$return = '<table class="acf-display-values">';
		
		foreach ( $fields as $field ) {
		  if( ! is_array( $field ) ) continue;	
		  
		  if ( 'clone' == $field['type'] ) {
			
			foreach ( $field['sub_fields'] as $sub_field ) {
			  
			  $return .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
			  $return .= sprintf( '<tr><td>%s</td></tr>', $this->display_field( $sub_field, $field['value'][ $sub_field['name'] ] ) );
			  
			}
			
		  } else {
		  
			$return .= sprintf( '<tr><th>%s</th></tr>', $field['label'] );
			$return .= sprintf( '<tr><td>%s</td></tr>', $this->display_field( $field ) );
		  
		  }
		  
		}
		
		$return .= '</table>';
	
		return $return;
	  }
	
	  function get_user_value( $tag, $form ){
		if ( ! preg_match_all( '/user:(.*)/', $tag, $matches ) ) {
			return '';
		}
		$value = '';
		
		$post_id = $form['record']['post_id'];
		if( strpos( $post_id, 'user_' ) !== false ){
			$user_id = str_replace( 'user_', '', $post_id );
		}elseif( strpos( $post_id, 'u' ) !== false ){
			$user_id = str_replace( 'u', '', $post_id );
		}else{
			$user_id = get_current_user_id();
		}
		$edit_user = get_user_by( 'ID', $user_id );

		if( empty( $edit_user->user_login ) ) return $value;

		$field_name = $matches[1][0];
		switch( $field_name ){
			case 'id':
				$value = $user_id;
			break;
			case 'username':
				$value = $edit_user->user_login;
			break;
			case 'email':
				$value = $edit_user->user_email;
			break;
			case 'first_name':
				$value = $edit_user->first_name;
			break;
			case 'last_name':
				$value = $edit_user->last_name;
			break;
			case 'display_name':
				$value = $edit_user->display_name;
			break;
			case 'role':
				$role = $edit_user->roles[0];
				global $wp_roles;
				$value = $wp_roles->roles[ $role ]['name'];
			break;
			case 'bio':
				$value = $edit_user->description;
			break;
		}
		return $value;
	}
	function get_post_value( $tag, $form ){
		if ( ! preg_match_all( '/post:(.*)/', $tag, $matches ) ) {
			return '';
		}

		$value = '';
		$post_id = $form['record']['post_id'];
		$edit_post = get_post( $post_id );
		if( ! is_wp_error( $edit_post ) ){
			$field_name = $matches[1][0];
			switch( $field_name ){
				case 'id':
					return $post_id;
				break;
				case 'post_title':
				case 'title':
					return $edit_post->post_title;
				break;
				case 'slug':
						$value = $edit_post->post_name;
				break;
				case 'post_content':
				case 'content':
				case 'desc':
						$value = $edit_post->post_content;
				break;
				case 'post_excerpt':
				case 'excerpt':
				case 'short_desc':
						$value = $edit_post->post_excerpt;
				break;
				case 'featured_image':
				case 'main_image':
					$post_thumb_id = get_post_thumbnail_id( $post_id );
					$post_thumb_url = wp_get_attachment_url( $post_thumb_id );
					$max_width = '500px';
					$post_tag = explode( ':', $field_name );
					if( isset( $post_tag[1] ) ){
						if( $post_tag[1] == 'image_link' ){
							$value = $post_thumb_id;
						}elseif( $post_tag[1] == 'image_id' ){
							$value = $post_thumb_url;
						}else{
							$max_width = $post_tag[1];
							if( is_numeric( $max_width ) ) $max_width .= 'px';							
						}
					}
					if( ! $value ){
						$value = '<div style="max-width:' .$max_width. '"><a href="' .$post_thumb_url. '"><img style=" width: 100%;height: auto" src="' . $post_thumb_url . '"/></a></div>';
					}
				break;
				case 'post_url':
				case 'url':
						$value = get_permalink( $post_id );
				break;
			}
			return $value;
		}
		return '';
	}
	
	
	  function get_sub_field_value( $tag, $form ) {	
		if ( ! preg_match_all( '/acf:(.*)/', $tag, $matches ) ) {
			return '';
		}
		$fields = $form['record'];

		$field_name = $matches[1][0];
		if( isset( $fields[$field_name] ) ){
			$field = $fields[$field_name];
		}else{
			$field = get_field_object( $field_name, $form['record']['post_id'] );	
		}
		if ( ! $field ) return '';
	
		// The previous regex will greedily match everything inside brackets.
		// For example "field:f1[s1][s2][s3] will yield "s1][s2][s3".
		// By splitting this we get the selector: array( "s1", "s2", "s3" )
		$selector = explode( '][', $matches[2][0] );
	
		// Find the nested subfield and its value
		$sub_field = $this->sub_field( $field, $selector );
		$sub_field_value = $this->sub_field_value( $field, $selector );
	
		if ( $sub_field ) {
		  return $this->display_field( $sub_field, $sub_field_value );
		}
	
		return '';
	  }
	
	  function get_field_value( $tag, $form ) {	
		if ( ! preg_match_all( '/acf:(.*)/', $tag, $matches ) ) {
			return '';
		}
		$fields = $form['record'];

		$field_name = $matches[1][0];
		
		if( isset( $fields[$field_name] ) ){
			$field = $fields[$field_name];
		}else{
			$field = get_field_object( $field_name, $form['record']['post_id'] );	
		}
		if ( ! $field ) return '';
	
		return $this->display_field( $field );
	  }

	  function sub_field( $field, $selector ) {
		  
		while ( ! empty( $selector ) && $field && isset( $field['sub_fields'] ) ) {
			$search = array_shift( $selector );
			$field = acf_search_fields( $search, $field['sub_fields'] );
		}

		return $field;
	}

   function sub_field_value( $field, $selector ) {
		$value = $field['value'];

		while ( ! empty( $selector ) ) {
			$search = array_shift( $selector );
			if ( isset( $value[ $search ] ) ) {
				$value = $value[ $search ];
			} else {
				return false;
			}
		}

		return $value;
	}
	
	  function display_field( $field, $value = false ) {
		if ( ! $value ) {
			$value = $field['value'];
		}
		if ( ! $value ) return '';
	
		$return = '';

		switch( $field['type'] ){
			case 'repeater':
				if( is_array( $value ) ){
					$return .= '<table class="acf-display-values acf-display-values-repeater">';
			
					// Column headings
					$return .= '<thead><tr>';
					
					foreach ( $field['sub_fields'] as $sub_field ) {
						$return .= sprintf( '<th>%s</th>', $sub_field['label'] );
					}
					
					$return .= '</tr></thead>';
					
					
					// Rows
					$return .= '<tbody>';
					
					if ( is_array( $value ) ) {
						foreach ( $value as $row_values ) {
							$return .= '<tr>';
							
							foreach ( $field['sub_fields'] as $sub_field ) {
								
								$return .= sprintf( '<td>%s</td>', $this->display_field( $sub_field, $row_values[ $sub_field['name'] ] ) );
								
							}
							
							$return .= '</tr>';
						}
					}
					
					$return .= '</tbody>';
					
					
					$return .= '</table>';
				}
			break;
			case 'clone':
			case 'group':
				$return .= sprintf( '<table class="acf-display-values acf-display-values-%s">', $field['type'] );
		
				foreach ( $field['sub_fields'] as $sub_field ) {
					if ( isset( $value[ $sub_field['name'] ] ) ) {
						$return .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
						$return .= sprintf( '<tr><td>%s</td></tr>', $this->display_field( $sub_field, $value[ $sub_field['name'] ] ) );
					}
				}
				
				$return .= '</table>';
			break;
			case 'true_false':
				$true_text = isset( $field['ui_on_text'] ) && ! empty( $field['ui_on_text'] ) ? $field['ui_on_text'] : __( 'Yes', 'advanced-forms' );
				$false_text = isset( $field['ui_off_text'] ) && ! empty( $field['ui_off_text'] ) ? $field['ui_off_text'] : __( 'No', 'advanced-forms' );
				
				$return = $value ? $true_text : $false_text;
			break;
			case 'image':
			case 'featured_image':
			case 'main_image':
			case 'site_logo':
						$return_type = isset( $tag[2] ) ? $tag[2] : '300';
				switch( $return_type ){
					case 'image_link':
						$return = $img_url;
					break;
					case 'image_id':
						$return = $img_id;
					break;
					default:
						$return = sprintf( '<img style="max-width:' .$return_type. '" src="%s" alt="%s" />', esc_attr( $value['sizes']['medium'] ), esc_attr( $value['alt']));
				}
			break;
			case 'gallery':
			case 'product_images':
				foreach ( $value as $image ) {
					$return .= sprintf( '<img src="%s" alt="%s" />', esc_attr( $image['sizes']['medium'] ), esc_attr( $image['alt']));
				}
			break;
			case 'file':
				$return .= sprintf( '<a href="%s">%s</a>', $value['url'], htmlspecialchars( $value['title'] ) );
			break;
			case 'wysiwyg':
			case 'textarea':
			case 'post_excerpt':
			case 'post_content':
			case 'user_bio':
				$return .= wp_kses_post( stripslashes( $value ) );
			break;
			case 'taxonomy':
				if( $value ){
					$returns = array();
					foreach( $value as $single_value ){
						if( $field['return_format'] == 'id' ) $single_value = get_term( $single_value );
						$returns[] = $single_value->name;
					}
					$return = join( ', ', $returns );
				}
			break;
			case 'relationship':
			case 'product_grouped':
			case 'product_upsells':
			case 'product_cross_sells':
			case 'post_object':
				if( is_array( $value ) && $value ){
					$returns = array();
					foreach( $value as $single_value ){
						if( $field['return_format'] == 'id' ) $single_value = get_post( $single_value );
						$returns[] = $single_value->post_title ? $single_value->post_title : '(no-name)';
					}
					$return = join( ', ', $returns );
				}elseif( $value ){
					if( $field['return_format'] == 'id' ) $value = get_post( $value );
					$return = $value->post_title ? $value->post_title : '(no-name)';
				}
			break;
			case 'user':
			case 'post_author':
				if( is_array( $value ) && $value ){
					$returns = array();
					if( $field['return_format'] == 'array' ){
						foreach( $value as $single_value ){
							$returns[] = sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
						}
					}else{
						foreach( $value as $single_value ){
							if( $field['return_format'] == 'id' ) $single_value = get_userdata( $single_value );
							$returns[] = sprintf( '%s %s', $value->first_name, $value->last_name );
						}
					}
					$return = join( ', ', $returns );
				}elseif( $value ){
					if( $field['return_format'] == 'array' ){
						$return = sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
					}else{
						if( $field['return_format'] == 'id' ) $value = get_userdata( $value );
						$return = sprintf( '%s %s', $value->first_name, $value->last_name );
					}
				}
			break;
			default:
				$return = $this->display_default_value( $value ); 
		}
		
		// Allow third-parties to alter rendered field
		$return = apply_filters( 'acf_frontend/display_value', $return, $field, $value );
		$return = apply_filters( 'acf_frontend/display_value/name=' . $field['name'], $return, $field, $value );
		$return = apply_filters( 'acf_frontend/display_value/key=' . $field['key'], $return, $field, $value );
		
		return $return;
	}
	
	
	function display_default_value( $value ) {
		$return = '';
		 
		if ( $value instanceof WP_Post ) {
			
			$return = $value->post_title ? $value->post_title : '(no-name)';
			
		} elseif ( $value instanceof WP_User ) {
			
			$return = sprintf( '%s %s', $value->first_name, $value->last_name );
		
		} elseif ( is_array( $value ) && isset( $value['user_email'] ) ) {
			
			$return = sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
			
		} elseif ( $value instanceof WP_Term ) {
			
			$return = $value->name;
			
		} elseif ( is_array( $value ) ) {
			
			$returns = array();
			
			foreach ( $value as $single_value ) {
				
				$returns[] = $this->display_default_value( $single_value );
				
			}
			
			$return = join( ', ', $returns );
			
		} else {
			
			$return = (string)$value;
			
		}
	
		// Sanitize output to protect against XSS
		return htmlspecialchars( $return );
	}
	

}

acff()->dynamic_values = new Dynamic_Values();

endif;	