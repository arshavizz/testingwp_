<?php

if( ! class_exists('acf_field_user_password') ) :

class acf_field_user_password extends acf_field_password {
	
	
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
		$this->name = 'user_password';
		$this->label = __("Password",'acf');
        $this->category = 'User';
		$this->defaults = array(
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> '',
            'force_edit'    => 0
		);
        add_filter( 'acf/load_field/type=password',  [ $this, 'load_user_password_field'] );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      

    }
    
    function load_user_password_field( $field ){
        if( ! empty( $field['custom_password'] ) ){
            $field['type'] = 'user_password';
        }
        return $field;
    }

    function prepare_field( $field ){
        if( isset( $field['wrapper']['class'] ) ){
            $field['wrapper']['class'] .= ' password_main';
        }else{
            $field['wrapper']['class'] = 'password_main';
        }	

        if( ! $field['value'] ) return $field;

        $field['value'] = '';

        if( empty( $field['force_edit'] ) ){
            $field['required'] = false;
            $field['wrapper']['class'] .= ' edit_password';
            
            if( isset( $field['custom_password'] ) ){
                $field['edit_user_password'] = true;			
            }
        } 

        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        $user = explode( 'user_', $post_id ); 

        if( empty( $user[1] ) ){
            return $value;
        }else{
            $user_id = $user[1]; 
            $edit_user = get_user_by( 'ID', $user_id );
            if( $edit_user instanceof WP_User ){
                $value = 'i';
            }
        }
        return $value;
    }

    function validate_value( $is_valid, $value, $field, $input ){
       if( isset( $_POST['_acf_main_action'] ) && $_POST['_acf_main_action'] == 'edit_user' && ! isset( $_POST['edit_user_password'] ) ){
            return $is_valid;
        }
                 
            if( isset( $_POST['custom_password_confirm'] ) && $_POST['acf'][ $_POST['custom_password_confirm'] ] != $value ){
                return __( 'The passwords do not match', 'acf-frontend-form-element' );
            }	
            if( (int) esc_attr( $_POST['password-strength'] ) < (int) esc_attr( $_POST['required-strength'] ) ){
                if( ! $field['required'] && $value == '' && ! isset( $_POST['edit_user_password'] ) ){
                    return $is_valid;
                }
                return __( 'The password is too weak. Please make it stronger.', 'acf-frontend-form-element' );
            }	
                    
        return $is_valid;
    }

    public function pre_update_value( $value, $post_id = false, $field = false ){
        $user = explode( 'user_', $post_id ); 

        if( ! empty( $user[1] ) ){
            $user_id = $user[1]; 
            remove_action( 'acf/save_post', '_acf_do_save_post' );
            wp_update_user( array( 'ID' => $user_id, 'user_pass' => $value ) );
            add_action( 'acf/save_post', '_acf_do_save_post' );
        }
        return null;
    }

    function render_field( $field ){
        $field['type'] = 'password';
        parent::render_field( $field );

        wp_enqueue_script( 'password-strength-meter' );
        wp_enqueue_script( 'acfef-password-strength' );
        echo  '<input type="hidden" name="custom_password" value="' . $field['key'] . '"/>' ;
        if( isset( $field['password_strength'] ) ){
            echo '<div class="pass-strength-result weak"></div>';
            echo '<input type="hidden" value="' . $field['password_strength'] . '" name="required-strength"/>';	
            echo '<input class="password-strength" type="hidden" value="" name="password-strength"/>';
        }	
        if ( empty( $field[ 'force_edit' ] ) ) {        
            if ( isset( $field['edit_user_password'] ) ) {
                echo '<button class="cancel-edit" type="button">'.$field['cancel_edit_password'].'</button><button class="acf-button button button-primary edit-password" type="button">'.$field['edit_password'].'</button>';
            }
        }

    }

   
}

// initialize
acf_register_field_type( 'acf_field_user_password' );

endif;
	
?>