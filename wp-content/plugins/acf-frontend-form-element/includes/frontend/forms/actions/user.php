<?php
namespace ACFFrontend\Actions;

use ACFFrontend\Plugin;
use ACFFrontend;
use ACFFrontend\Classes\ActionBase;
use ACFFrontend\Widgets;
use Elementor\Controls_Manager;
use ElementorPro\Modules\QueryControl\Module as Query_Module;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( ! class_exists( 'ActionUser' ) ) :

class ActionUser extends ActionBase {
	
	public function get_name() {
		return 'user';
	}

	public function get_label() {
		return __( 'User', 'acf-frontend-form-element' );
	}

	public function get_fields_display( $form_field, $local_field, $wg_id = '' ){

		switch( $form_field['field_type'] ){
			case 'username':
				$local_field['type'] = 'text';
				$local_field['disabled'] = isset( $form_field['allow_edit'] ) ? ! $form_field['allow_edit'] : 1;
				$local_field['custom_username'] = true;
			break;
			case 'password':
				$local_field['type'] = 'user_password';
				$local_field['edit_password'] = isset( $form_field['edit_password'] ) ? $form_field['edit_password'] : 'Edit Password';
				$local_field['cancel_edit_password'] = isset( $form_field['cancel_edit_password'] ) ? $form_field['cancel_edit_password'] : 'Cancel';
				$local_field['force_edit'] = isset( $form_field['force_edit_password'] ) ? $form_field['force_edit_password'] : 0;
			break;				
			case 'confirm_password':
				$local_field['type'] = 'user_password_confirm';
			break;			
			case 'email':
				$local_field['type'] = 'email';
				if( isset( $form_field['set_as_username'] ) ){
					$local_field['set_as_username'] = true;
				}
				$local_field['custom_email'] = true;
			break;
			case 'first_name':
				$local_field['type'] = 'text';
				$local_field['custom_first_name'] = true;
			break;
			case 'last_name':
				$local_field['type'] = 'text';
				$local_field['custom_last_name'] = true;
			break;					
			case 'nickname':
				$local_field['type'] = 'text';
				$local_field['custom_nickname'] = true;
			break;				
			case 'display_name':
				$local_field['type'] = 'display_name';
				
			break;			
			case 'bio':
				$local_field['type'] = 'textarea';
				$local_field['custom_user_bio'] = true;
			break;
			case 'bio':
				$local_field['type'] = 'url';
				$local_field['custom_user_url'] = true;
			break;
			case 'role':
				$local_field['type'] = 'role';
				if( isset( $form_field['role_field_options'] ) ){
					$local_field['role_options'] = $form_field['role_field_options'];
				}
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
				$local_field['default_value'] = isset( $form_field['default_role'] ) ? $form_field['default_role'] : 'subscriber';
			break;
			
		}
		return $local_field;
	}
	

	public function register_settings_section( $widget ) {
		
					
		$widget->start_controls_section(
			'section_edit_user',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						[
							'name' => 'main_action',
							'operator' => 'in',
							'value' => ['new_user', 'edit_user'],
						],	
						
					],
				],
			]
		);
		
		$widget->add_control(
			'user_settings',
			[
				'label' => __( 'User Settings', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->action_controls( $widget );
				
		$widget->end_controls_section();
	}
	
	
	public function action_controls( $widget, $step = false ){
		$condition = [
			'main_action' => [ 'edit_user', 'delete_user'],
		];
		if( $step ){
			$condition['field_type'] = 'step';
			$condition['overwrite_settings'] = 'true';
		}

		$widget->add_control(
			'user_to_edit',
			[
				'label' => __( 'Select User', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'current_user',
				'options' => [
					'current_user'  => __( 'Current User', 'acf-frontend-form-element' ),
					'current_author'  => __( 'Current Author', 'acf-frontend-form-element' ),
					'url_query' => __( 'URL Query', 'acf-frontend-form-element' ),
					'select_user' => __( 'Select User', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);
		$condition['user_to_edit'] = 'url_query';
		$widget->add_control(
			'url_query_user',
			[
				'label' => __( 'URL Query', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'user_id', 'acf-frontend-form-element' ),
				'default' => __( 'user_id', 'acf-frontend-form-element' ),
				'description' => __( 'Enter the URL query parameter containing the id of the user you want to edit', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);	
		$condition['user_to_edit'] = 'select_user';
		if( ! class_exists( 'ElementorPro\Modules\QueryControl\Module' ) ){		
			$widget->add_control(
				'user_select',
				[
					'label' => __( 'User', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'default' => get_current_user_id(),
					'description' => __( 'Enter user id', 'acf-frontend-form-element' ),
					'condition' => $condition,
				]
			);		
		}else{			
			$widget->add_control(
				'user_select',
				[
					'label' => __( 'User', 'acf-frontend-form-element' ),
					'label_block' => true,
					'type' => Query_Module::QUERY_CONTROL_ID,
					'autocomplete' => [
						'object' => Query_Module::QUERY_OBJECT_USER,
						'display' => 'detailed',
					],				
					'default' => get_current_user_id(),
					'condition' => $condition,
				]
			);
		}

		unset( $condition['user_to_edit'] );
		$condition['main_action'] = 'new_user';

		$widget->add_control(
			'username_default',
			[
				'label' => __( 'Default Username', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'generate',
				'options' => [
					'generate' => __( 'Generate Random Number', 'acf-frontend-form-element' ),
					'id' => __( 'Generate From ID', 'acf-frontend-form-element' )
				],
				'description' => __( 'Will be overwritten if your form has a "Username" field', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'username_prefix',
			[
				'label' => __( 'Username Prefix', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'user_',
				'description' => __( 'Please enter only lowercase latin letters, numbers, @, -, . and _', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'username_suffix',
			[
				'label' => __( 'Username Suffix', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Please enter only lowercase latin letters, numbers, @, -, . and _', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'display_name_default',
			[
				'label' => __( 'Default Display Name', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'user_login' => __( 'Username', 'acf-frontend-form-element' ),
					'user_email' => __( 'Email', 'acf-frontend-form-element' ),
					'first_name' => __( 'First Name', 'acf-frontend-form-element' ),
					'last_name' => __( 'Last Name', 'acf-frontend-form-element' ),
					'first_last' => __( 'First and Last Name', 'acf-frontend-form-element' ),
					'nickname' => __( 'Nickname', 'acf-frontend-form-element' ),
				],
				'description' => __( 'Will be overwritten if your form has a "Display Name" field', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);

		$widget->add_control(
			'new_user_role',
			[
				'label' => __( 'New User Role', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'subscriber',
				'options' => acf_frontend_get_user_roles( ['administrator'] ),
				'condition' => $condition,
			]
		);
		
		$widget->add_control(
			'hide_admin_bar',
			[
				'label' => __( 'Hide WordPress Admin Area?', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Hide', 'acf-frontend-form-element' ),
				'label_off' => __( 'Show','acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition' => $condition,
			]
		);
		if( ! $step ){

			$widget->add_control(
				'login_user',
				[
					'label' => __( 'Log in as new user?', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'acf-frontend-form-element' ),
					'label_off' => __( 'No','acf-frontend-form-element' ),
					'return_value' => 'true',
					'condition' => $condition,			
				]
			);			
		}
		
		$condition['main_action'] = ['new_user', 'edit_user'];

		$widget->add_control(
			'user_manager',
			[
				'label' => __( 'Managing User', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => __( 'No Manager','acf-frontend-form-element' ),
					'current_user' => __( 'Current User','acf-frontend-form-element' ),
					//'current_author' => __( 'Current Post Author','acf-frontend-form-element' ),
					'select_user' => __( 'Select User','acf-frontend-form-element' ),
				],
				'description' => __( 'Who will be in charge of editing this user\'s data?', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$condition['user_manager'] = 'select_user';
		if( ! class_exists( 'ElementorPro\Modules\QueryControl\Module' ) ){		
			$widget->add_control(
				'manager_select',
				[
					'label' => __( 'User', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'default' => get_current_user_id(),
					'description' => __( 'Enter user id', 'acf-frontend-form-element' ),
					'condition' => $condition,
				]
			);		
		}else{			
			$widget->add_control(
				'manager_select',
				[
					'label' => __( 'User', 'acf-frontend-form-element' ),
					'label_block' => true,
					'type' => Query_Module::QUERY_CONTROL_ID,
					'autocomplete' => [
						'object' => Query_Module::QUERY_OBJECT_USER,
						'display' => 'detailed',
					],				
					'default' => get_current_user_id(),
					'condition' => $condition,
				]
			);
		}
		
	}
		
	public function on_submit( $post_id, $form ){	
		if( $post_id != 'user_0' ) return $post_id; 
		
		$username_generated = false;
		$user_to_insert = [];								 
					
		$submit_keys = [ 
			'username' => 'user_login',
			'email' => 'user_email',
			'password' => 'user_pass',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'display_name' => 'display_name',		
			'role' => 'role',
		];
		$wg_id = isset( $_POST['_acf_element_id'] ) ? '_' . $_POST['_acf_element_id'] : '';

		foreach( $submit_keys as $key => $name ){
			$field_key = 'acfef' . $wg_id . '_' . $key;

			if( isset( $_POST['acf'][ $field_key ] ) ) {	
				$field_value = acf_extract_var( $_POST['acf'], $field_key );
				$field = acf_get_field( $field_key );

				if( $key == 'display_name' && $field_value ){
					$_POST['custom_display_name'] = 1;
				} 

				if( $field ){
					if( ! empty( $field['save_prepend'] ) ) $field_value = $field['prepend'] . $field_value;
					if( ! empty( $field['save_append'] ) ) $field_value .= $field['append'];
					if( !empty( $field['set_as_username'] ) ) $user_to_insert[ 'user_login' ] = $field_value;
				}

				$user_to_insert[ $name ] = $field_value;
			}
			
		}

		if( empty( $user_to_insert['user_login'] ) ){ 
			$prefix = sanitize_title( $form['username_prefix'] );
			$suffix = sanitize_title( $form['username_suffix'] );
			$user_to_insert['user_login'] = $this->generate_username( $prefix, $suffix );
			$username_generated = true;
		}

		if( empty( $user_to_insert['user_pass'] ) ){ 
			$user_to_insert['user_pass'] = wp_generate_password();
		}	
		
		if( empty( $user_to_insert['role'] ) ){ 
			$user_to_insert['role'] = $form['new_user_role'];
		}
	
		$user_to_insert['show_admin_bar_front'] = $form['hide_admin_bar'];
		
		$user_to_insert = apply_filters( 'acfef/user_fields_save', $user_to_insert, $form );
		
		$user_id = wp_insert_user( $user_to_insert );  

		if( $username_generated && $form['username_default'] == 'id' ){
			$prefix = sanitize_title( $form['username_prefix'] );
			$suffix = sanitize_title( $form['username_suffix'] );
			$new_username = sprintf( '%s%s%s', $prefix, $user_id, $suffix );
			if ( ! username_exists( $new_username ) ) {
				global $wpdb;
				$wpdb->update( $wpdb->users, array( 'user_login' => $new_username ), ['ID' => $user_id ] );
				update_user_meta( $user_id, 'nickname', $new_username );
				wp_update_user( ['ID' => $user_id, 'display_name' => $new_username ] );
			}
		}

		if ( ! is_wp_error( $user_id ) ) {
			update_user_meta( $user_id, 'hide_admin_area', $form['hide_admin_bar'] );			
			$_POST['acfef_registered_user'] = 1;
			return 'user_' . $user_id;
		}else{	
			return '-1';
		}
	}  
	
	public function generate_username( $prefix = '', $suffix = '' ) {	
		static $i;
		if ( null === $i ) {
			$i = 1;
		} else {
			$i ++;
		}
		$new_username = sprintf( '%s%s%s', $prefix, $i, $suffix );
		if ( ! username_exists( $new_username ) ) {
			return $new_username;
		} else {
			return $this->generate_username( $prefix, $suffix );
		}
	}
	

	public function __construct(){
		add_filter( 'acf/pre_save_post', array( $this, 'on_submit' ), 4, 2 );
	}
}
acff()->local_actions['user'] = new ActionUser();

endif;	