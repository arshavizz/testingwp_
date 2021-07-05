<?php

if( ! class_exists('acf_field_featured_image') ) :

class acf_field_featured_image extends acf_field_upload_image {
	
	
	/*
	*  initialize
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'featured_image';
		$this->label = __("Featured Image",'acf');
        $this->category = 'Post';
        $this->defaults = array(
			'return_format'	=> 'array',
			'preview_size'	=> 'medium',
			'library'		=> 'all',
			'min_width'		=> 0,
			'min_height'	=> 0,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> ''
		);
		
        add_filter( 'acf/load_field/type=image',  [ $this, 'load_featured_image_field'] );
		add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );        

	}
    
    function load_featured_image_field( $field ){
        if( ! empty( $field['custom_featured_image'] ) ){
            $field['type'] = 'featured_image';
        }
        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        if( $post_id && is_numeric( $post_id ) ){  
            $value = get_post_meta( $post_id, '_thumbnail_id', true );
        }
        return $value;
    }

    public function pre_update_value( $value, $post_id = false, $field = false ){
        if( $post_id && is_numeric( $post_id ) ){              
            update_metadata( 'post', $post_id, '_thumbnail_id', $value );
            	
			$value = (int) $value;
			$post = get_post( $post_id );
			if( wp_is_post_revision( $post ) ){
				$post_id = $post->post_parent;
			}
			acf_connect_attachment_to_post( $value, $post_id );
        }
		return null;
	}

	public function update_value( $value, $post_id = false, $field = false ){
		return null;
	}


}

// initialize
acf_register_field_type( 'acf_field_featured_image' );

endif;
	
?>