<?php

if( ! class_exists('acf_field_post_author') ) :

class acf_field_post_author extends acf_field_user {
	
	
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
		$this->name = 'post_author';
		$this->label = __("Author",'acf');
        $this->category = 'Post';
		$this->defaults = array(
            'data_name'     => 'author',
			'role' 			=> '',
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'return_format'	=> 'array',
		);
        add_filter( 'acf/load_field/type=user',  [ $this, 'load_post_author_field'] );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
		
	}
    
    function load_post_author_field( $field ){
        if( ! empty( $field['custom_post_author'] ) ){
            $field['type'] = 'post_author';
        }
        return $field;
    }

    function prepare_field( $field ){
        $field['type'] = 'user';
        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        if( $post_id && is_numeric( $post_id ) ){  
            $edit_post = get_post( $post_id );
            $value = $edit_post->post_author;
        }else{
            $value = get_current_user_id();
        }
        return $value;
    }

    public function pre_update_value( $value, $post_id = false, $field = false ){
        if( $post_id && is_numeric( $post_id ) ){  
            $post_to_edit = [
                'ID' => $post_id,
            ];
            $post_to_edit['post_author'] = $value;
            remove_action( 'acf/save_post', '_acf_do_save_post' );
            wp_update_post( $post_to_edit );
            add_action( 'acf/save_post', '_acf_do_save_post' );

        }
        return $value;
    }

    public function update_value( $value, $post_id = false, $field = false ){
        return null;
    }


}

// initialize
acf_register_field_type( 'acf_field_post_author' );

endif;
	
?>