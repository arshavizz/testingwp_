<?php

namespace ACFFrontend\Classes;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'ACFFrontend\\Classes\\Submit_Form' ) ) {
    class Submit_Form
    {
        public function check_submit_form()
        {
            // verify nonce
            if ( !acf_verify_nonce( 'acfef_form' ) ) {
                return;
            }
            // bail ealry if form not submit
            if ( empty($_POST['_acf_form']) ) {
                return;
            }
            // load form
            $form = json_decode( acf_decrypt( $_POST['_acf_form'] ), true );
            // bail ealry if form is corrupt
            if ( empty($form) ) {
                return;
            }
            // kses
            if ( $form['kses'] && isset( $_POST['acf'] ) ) {
                $_POST['acf'] = wp_kses_post_deep( $_POST['acf'] );
            }
            // validate data
            acf_validate_save_post( true );
            // remove validate email field before it saves an empty row in the database
            if ( isset( $_POST['acf']['_validate_email'] ) ) {
                unset( $_POST['acf']['_validate_email'] );
            }
            // submit
            $this->submit_form( $form );
        }
        
        function create_record( $form )
        {
            // Retrieve all form fields and their values
            $form['record'] = array();
            if ( isset( $_POST['acf'] ) ) {
                foreach ( $_POST['acf'] as $k => $value ) {
                    $field = acf_get_field( $k );
                    $field['_input'] = $value;
                    $field['value'] = acf_format_value( $value, 0, $field );
                    $form['record'][$field['name']] = $field;
                }
            }
            return $form;
        }
        
        public function submit_form( $form )
        {
            // filter
            $form = apply_filters( 'acf/pre_submit_form', $form );
            // vars
            $post_id = wp_kses( $_POST['_acf_post_id'], 'strip' );
            $form = $this->create_record( $form );
            // add global for backwards compatibility
            $GLOBALS['acfef_form'] = $form;
            // allow for custom save
            $post_id = apply_filters( 'acf/pre_save_post', $post_id, $form );
            // save
            acf_save_post( $post_id );
            // restore form (potentially modified)
            $form = $GLOBALS['acfef_form'];
            $form['record']['post_id'] = $post_id;
            // action
            do_action( 'acf/submit_form', $form, $post_id );
            $this->return_form( $form, $post_id );
        }
        
        public function return_form( $form, $post_id )
        {
            // get form id
            
            if ( isset( $_POST['_acf_element_id'] ) ) {
                $form_id = $_POST['_acf_element_id'];
            } elseif ( isset( $_POST['_acf_field_id'] ) ) {
                $form_id = $_POST['_acf_field_id'];
            } elseif ( isset( $_POST['_acf_admin_page'] ) ) {
                $form_id = $_POST['_acf_admin_page'];
            } else {
                return;
            }
            
            
            if ( isset( $_POST['_acf_status'] ) && $_POST['_acf_status'] != 'publish' ) {
                $form['post_id'] = $post_id;
                $form['hidden_fields']['main_action'] = 'edit_post';
                ob_start();
                acff()->form_display->render_form( $form );
                $response['clear_form'] = ob_get_clean();
                $response['saved_message'] = $form['saved_draft_message'];
                wp_send_json_success( $response );
                exit;
            }
            
            
            if ( isset( $_POST['_acf_step_action'] ) ) {
                $main_action = $_POST['_acf_step_action'];
            } else {
                $main_action = $_POST['_acf_main_action'];
            }
            
            
            if ( isset( $_POST['log_back_in'] ) ) {
                $user_id = $_POST['log_back_in'];
                $user_object = get_user_by( 'ID', $user_id );
                
                if ( $user_object ) {
                    wp_set_current_user( $user_id, $user_object->user_login );
                    wp_set_auth_cookie( $user_id );
                    do_action( 'wp_login', $user_object->user_login, $user_object );
                }
            
            }
            
            do_action(
                'acfef/on_submit',
                $post_id,
                $form,
                $form_id
            );
            
            if ( isset( $form['ajax_submit'] ) ) {
                $update_message = $form['update_message'];
                if ( strpos( $update_message, '[$' ) !== 'false' || strpos( $update_message, '[' ) !== 'false' ) {
                    $update_message = acff()->dynamic_values->get_dynamic_values( $update_message, $post_id, $form );
                }
                $response = array(
                    'post_id'        => $post_id,
                    'update_message' => $update_message,
                    'to_top'         => true,
                );
                
                if ( isset( $form['form_attributes']['data-field'] ) ) {
                    $title = get_post_field( 'post_title', $post_id ) . '<a href="#" class="acf-icon -pencil small dark edit-rel-post" data-name="edit_item"></a>';
                    $response['append'] = [
                        'id'     => $post_id,
                        'text'   => $title,
                        'action' => ( is_numeric( $form['post_id'] ) ? 'edit' : 'add' ),
                    ];
                    $response['field_key'] = $form['form_attributes']['data-field'];
                }
                
                
                if ( isset( $form['redirect_action'] ) ) {
                    $main_action = $form['hidden_fields']['main_action'];
                    
                    if ( $form['redirect_action'] == 'edit' ) {
                        $form['post_id'] = $post_id;
                        if ( $main_action == 'new_post' ) {
                            $form['hidden_fields']['main_action'] = 'edit_post';
                        }
                        if ( $main_action == 'new_user' ) {
                            $form['hidden_fields']['main_action'] = 'edit_user';
                        }
                        if ( $main_action == 'edit_product' ) {
                            $form['hidden_fields']['main_action'] = 'edit_product';
                        }
                    } else {
                        
                        if ( $main_action == 'new_post' ) {
                            $form['post_id'] = 'add_post';
                            $form['hidden_fields']['main_action'] = 'new_post';
                        }
                        
                        
                        if ( $main_action == 'new_user' ) {
                            $form['post_id'] = 'user_0';
                            $form['hidden_fields']['main_action'] = 'new_user';
                        }
                        
                        
                        if ( $main_action == 'new_product' ) {
                            $form['post_id'] = 'add_product';
                            $form['hidden_fields']['main_action'] = 'new_product';
                        }
                    
                    }
                
                }
                
                ob_start();
                acff()->form_display->render_form( $form );
                $response['clear_form'] = ob_get_clean();
                wp_send_json_success( $response );
                exit;
            } else {
                // vars
                $return = acf_maybe_get( $form, 'return', '' );
                // redirect
                
                if ( $return ) {
                    $object_type = '';
                    
                    if ( strpos( $post_id, '_' ) !== false ) {
                        $object = explode( '_', $post_id );
                        $object_type = '_' . $object[0][0];
                        $post_id = $object[1];
                    }
                    
                    
                    if ( !empty($form['login_user']) ) {
                        $user = get_user_by( 'ID', $post_id );
                        
                        if ( !empty($user->user_login) ) {
                            wp_set_current_user( $post_id, $user->user_login );
                            wp_set_auth_cookie( $post_id );
                        }
                    
                    }
                    
                    // update %placeholders%
                    $return = str_replace( '%post_id%', $post_id, $return );
                    $return = str_replace( '%post_url%', get_permalink( $post_id ), $return );
                    $query_args = [];
                    $query_args['updated'] = $form_id . '_' . $_POST['_acf_screen_id'];
                    if ( is_numeric( $post_id ) ) {
                        $query_args['updated'] .= '_' . $object_type . $post_id;
                    }
                    if ( isset( $form['redirect_action'] ) && $form['redirect_action'] == 'edit' ) {
                        $query_args['edit'] = 1;
                    }
                    if ( isset( $_POST['_acf_modal'] ) && $_POST['_acf_modal'] == 1 ) {
                        $query_args['modal'] = true;
                    }
                    if ( !empty($form['url_query']) ) {
                        $query_args[$form['url_query']] = $post_id;
                    }
                    if ( isset( $form['redirect_params'] ) ) {
                        $query_args = array_merge( $query_args, $form['redirect_params'] );
                    }
                    $return = add_query_arg( $query_args, $return );
                    $return = acff()->dynamic_values->get_dynamic_values( $return, $post_id, $form );
                    if ( isset( $form['last_step'] ) ) {
                        $return = remove_query_arg( [ 'form_id', 'modal', 'step' ], $return );
                    }
                    $return_args = array(
                        'redirect' => $return,
                    );
                    if ( isset( $form['reload_current'] ) ) {
                        $return_args['reload_current'] = true;
                    }
                    $form['post_id'] = $post_id;
                    $this->save_record( $form );
                    wp_send_json_success( $return_args );
                    die;
                }
            
            }
        
        }
        
        public function reload_form(
            $post_id,
            $form,
            $step,
            $step_index
        )
        {
            $form['step_index'] = $form['step_index'] + 1;
            $form['post_id'] = $post_id;
            $main_action = $form['hidden_fields']['main_action'];
            if ( $main_action == 'new_post' ) {
                $form['hidden_fields']['main_action'] = 'edit_post';
            }
            if ( $main_action == 'new_user' ) {
                $form['hidden_fields']['main_action'] = 'edit_user';
            }
            ob_start();
            acff()->form_display->render_form( $form );
            $reload_form = ob_get_contents();
            ob_end_clean();
            wp_send_json_success( [
                'clear_form' => $reload_form,
                'widget'     => $form['hidden_fields']['element_id'],
                'step'       => $form['step_index'],
            ] );
            die;
        }
        
        private function save_record( $form )
        {
            $record_key = wp_generate_password( 12, false, false );
            $expiration_time = time() + 60 * MINUTE_IN_SECONDS;
            setcookie(
                'acf_form_record',
                $record_key,
                $expiration_time,
                '/'
            );
            wp_insert_post( array(
                'post_type'    => 'acf_form_record',
                'post_status'  => 'auto-draft',
                'post_title'   => $record_key,
                'post_content' => acf_encrypt( json_encode( $form ) ),
            ) );
        }
        
        public function form_message()
        {
            $return = '';
            
            if ( isset( $_GET['updated'] ) && $_GET['updated'] !== 'true' ) {
                $form_id = explode( '_', $_GET['updated'] );
                $type = 'update';
            }
            
            
            if ( isset( $_GET['trashed'] ) ) {
                $form_id = explode( '_', $_GET['trashed'] );
                $type = 'delete';
            }
            
            
            if ( isset( $_GET['deleted'] ) ) {
                $form_id = explode( '_', $_GET['deleted'] );
                $type = 'delete';
            }
            
            if ( !isset( $form_id ) ) {
                return;
            }
            $widget = false;
            if ( acff()->elementor ) {
                $widget = acff()->elementor->get_the_widget( $form_id );
            }
            
            if ( !$widget ) {
                $settings = acff()->form_display->get_form( 'form_' . $form_id[0] );
                
                if ( !$settings ) {
                    return;
                } else {
                    $settings['show_success_message'] = $settings['show_update_message'];
                    if ( $settings['redirect'] == 'current' ) {
                        return;
                    }
                }
            
            } else {
                $settings = $widget->get_settings_for_display();
            }
            
            
            if ( $type == 'update' && isset( $settings['show_success_message'] ) ) {
                $show_message = $settings['show_success_message'];
                $message = $settings['update_message'];
                if ( $settings['redirect'] == 'current' ) {
                    return;
                }
            }
            
            if ( $type == 'delete' ) {
                
                if ( isset( $settings['show_delete_message'] ) ) {
                    $show_message = $settings['show_delete_message'];
                    $message = $settings['delete_message'];
                }
            
            }
            if ( !$show_message || empty($message) ) {
                return;
            }
            $widget_id = $form_id[0];
            $post_id = $widget_page = $form_id[1];
            if ( isset( $form_id[2] ) ) {
                $post_id = $form_id[2];
            }
            if ( strpos( $message, '[$' ) !== 'false' || strpos( $message, '[' ) !== 'false' ) {
                $message = acff()->dynamic_values->get_dynamic_values( $message, $post_id );
            }
            if ( !$message ) {
                return;
            }
            /* $return = '<div id="modal_' .$post_id. '" class="modal edit-modal show">
            			<div class="modal-content"> 
            			<div class="modal-inner"> 
            			<span onClick="closeModal(\'' .$post_id. '\')" class="acf-icon -cancel close"></span>
            				<div class="content-container">' . $message . '</div>
            				</div>
            			</div>
            			</div>'; */
            $return = '<div class="-fixed acfef-message elementor-' . $widget_page . '">
					<div class="elementor-element elementor-element-' . $widget_id . '">
						<div class="acf-notice -success acf-success-message -dismiss"><p class="success-msg">' . $message . '</p><span class="acfef-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div>
					</div>
					</div>';
            acf_enqueue_scripts();
            echo  $return ;
        }
        
        public function delete_object()
        {
            if ( !acf_verify_nonce( 'acf_delete' ) ) {
                return;
            }
            // bail ealry if form not submit
            if ( empty($_POST['_acf_form']) ) {
                return;
            }
            // load form
            $form = json_decode( acf_decrypt( $_POST['_acf_form'] ), true );
            // bail ealry if form is corrupt
            if ( empty($form) ) {
                return;
            }
            $page_id = $_POST['_acf_screen_id'];
            $button_id = $_POST['_acf_element_id'];
            $redirect_query = array();
            
            if ( isset( $_POST['_acf_delete_post'] ) ) {
                $post_id = intval( $_POST['_acf_delete_post'] );
                
                if ( isset( $form['force_delete'] ) && $form['force_delete'] == 'true' ) {
                    $deleted = wp_delete_post( $post_id, true );
                    $redirect_query['deleted'] = $button_id . '_' . $page_id . '_' . $post_id;
                } else {
                    $deleted = wp_trash_post( $post_id );
                    $redirect_query['trashed'] = $button_id . '_' . $post_id;
                }
            
            }
            
            
            if ( isset( $_POST['_acf_delete_term'] ) ) {
                $term_id = intval( $_POST['_acf_delete_term'] );
                $deleted = wp_delete_term( $term_id, sanitize_text_field( $_POST['_acf_taxonomy_type'] ) );
                $redirect_query['deleted'] = $button_id . '_' . $page_id . '_t' . $term_id;
            }
            
            
            if ( isset( $_POST['_acf_delete_user'] ) ) {
                $user_id = intval( $_POST['_acf_delete_user'] );
                $deleted = wp_delete_user( $user_id, $form['reassign_posts'] );
                $redirect_query['deleted'] = $button_id . '_' . $page_id . '_u' . $user_id;
            }
            
            
            if ( isset( $deleted ) ) {
                $redirect_url = add_query_arg( $redirect_query, $form['redirect'] );
                wp_send_json_success( array(
                    'redirect' => $redirect_url,
                ) );
                die;
            }
        
        }
        
        public function delete_records()
        {
            $record_args = array(
                'post_type'      => 'acf_form_record',
                'post_status'    => 'all',
                'date_query'     => array( array(
                'before'    => '60 minutes ago',
                'inclusive' => true,
            ) ),
                'posts_per_page' => -1,
            );
            if ( get_posts( $record_args ) ) {
                foreach ( get_posts( $record_args ) as $post ) {
                    wp_delete_post( $post->ID, true );
                }
            }
        }
        
        public function __construct()
        {
            add_action( 'init', array( $this, 'delete_records' ) );
            add_action( 'wp_footer', array( $this, 'form_message' ) );
            add_action( 'wp_ajax_acfef/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'wp_ajax_nopriv_acfef/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'admin_post_acfef/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'admin_post_nopriv_acfef/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'wp_ajax_acfef/delete_object', array( $this, 'delete_object' ) );
            add_action( 'wp_ajax_nopriv_acfef/delete_object', array( $this, 'delete_object' ) );
        }
    
    }
    acff()->form_submit = new Submit_Form();
}
