<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'ACFFrontend_Hooks' ) ) {
    class ACFFrontend_Hooks
    {
        public function frontend_only_setting( $field )
        {
            acf_render_field_setting( $field, array(
                'label'        => __( 'Hidden Field' ),
                'instructions' => __( 'Lets you hide the field everywhere. Useful if you need hidden data', 'acf-frontend-form-element' ),
                'name'         => 'invisible',
                'type'         => 'true_false',
                'ui'           => 1,
            ), true );
            acf_render_field_setting( $field, array(
                'label'        => __( 'Show On Frontend Only' ),
                'instructions' => __( 'Lets you hide the field on the backend to avoid duplicate fields.', 'acf-frontend-form-element' ),
                'name'         => 'only_front',
                'type'         => 'true_false',
                'ui'           => 1,
                'conditions'   => [ [
                'field'    => 'invisible',
                'operator' => '==',
                'value'    => '0',
            ] ],
            ), true );
            acf_render_field_setting( $field, array(
                'label'        => __( 'Hide Field Label', 'acf-frontend-form-element' ),
                'instructions' => __( 'Lets you hide the field\'s label including HTML markup.', 'acf-frontend-form-element' ),
                'name'         => 'field_label_hide',
                'type'         => 'true_false',
                'ui'           => 1,
                'conditions'   => [ [
                'field'    => 'only_front',
                'operator' => '==',
                'value'    => '1',
            ] ],
            ), true );
        }
        
        public function read_only_setting( $field )
        {
            $types = array(
                'text',
                'textarea',
                'email',
                'number'
            );
            if ( in_array( $field['type'], $types ) ) {
                acf_render_field_setting( $field, array(
                    'label'        => __( 'Read Only', 'acf-frontend-form-element' ),
                    'instructions' => 'Prevent users from changing the data.',
                    'name'         => 'readonly',
                    'type'         => 'true_false',
                    'ui'           => 1,
                ) );
            }
        }
        
        public function hide_acfef_fields( $groups )
        {
            global  $post ;
            if ( isset( $post->post_type ) && $post->post_type == 'acf-field-group' ) {
                unset( $groups['Form'] );
            }
            unset( $groups['acfef-hidden'] );
            return $groups;
        }
        
        /* 		public function acfef_load_text_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acfef_is_custom( $field ) ){
        				return $value;
        			}
        			if( $post_id ){
        				
        			if( strpos( $post_id, 'comment' ) !== false ){
        					$current_user = wp_get_current_user();
        					if( $current_user !== 0 ){
        						if( isset( $field['custom_author'] ) && $field['custom_author'] == 1 ){
        							$value = esc_html( $current_user->display_name );
        						}				
        					}
        				}
        			}
        
        			return $value;
        		}
        
        
        		public function acfef_load_email_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acfef_is_custom( $field ) ){
        				return $value;
        			}
        			if( $post_id ){
        				if( strpos( $post_id, 'comment' ) !== false ){
        					$current_user = wp_get_current_user();
        					if( $current_user !== 0 ){			
        						if( isset( $field['custom_author_email'] ) && $field['custom_author_email'] == 1 ){
        							$value = esc_html( $current_user->user_email );
        						}
        					}
        				}
        			}
        			return $value;
        		}
        			 */
        public function update_acfef_values( $value, $post_id = false, $field = false )
        {
            if ( !empty($field['no_save']) ) {
                return null;
            }
            
            if ( isset( $_POST['_acf_status'] ) && $_POST['_acf_status'] == 'publish' ) {
                $revisions = wp_get_post_revisions( $post_id );
                
                if ( !empty($revisions[0]) ) {
                    remove_filter(
                        'acf/update_value',
                        [ $this, 'update_acfef_values' ],
                        7,
                        3
                    );
                    acf_update_value( $value, $revisions[0]->ID, $field );
                    add_filter(
                        'acf/update_value',
                        [ $this, 'update_acfef_values' ],
                        7,
                        3
                    );
                }
            
            }
            
            if ( $post_id !== 'acfef_options' ) {
                return $value;
            }
            update_option( $field['key'], $value );
            return null;
        }
        
        /* 		public function acfef_update_text_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acfef_is_custom( $field ) ){
        				return $value;
        			}
        
        			if( strpos( $post_id, 'term' ) !== false ){
        				$term_id = explode( '_', $post_id )[1];
        				$edit_term = get_term( $term_id );
        				if( ! is_wp_error( $edit_term ) ){
        					if( isset( $field['custom_term_name'] ) && $field['custom_term_name'] == 1 ){
        						$update_args = array( 'name' => $value );
        						if( $field['change_slug'] )$update_args['slug'] = sanitize_title( $value );
        						wp_update_term( $term_id, $edit_term->taxonomy, $update_args );
        					}
        				}
        			}elseif( strpos( $post_id, 'comment' ) !== false ){
        				$comment_id = explode( '_', $post_id )[1];
        				$comment_to_edit = [
        					'comment_ID' => $comment_id,
        				];
        				if( isset( $field['custom_author'] ) && $field['custom_author'] == 1 ){
        					$comment_to_edit['comment_author'] = esc_attr( $value );
        				}
        				wp_update_comment( $comment_to_edit );
        			}
        			
        			return null;
        		}
        		
        		
        		public function acfef_update_email_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acfef_is_custom( $field ) ){
        				return $value;
        			}
        			if( strpos( $post_id, 'comment' ) !== false ){
        				$comment_id = explode( '_', $post_id )[1];
        				$comment_to_edit = [
        					'comment_ID' => $comment_id,
        				];
        				if( isset( $field['custom_author_email'] ) && $field['custom_author_email'] == 1 ){
        					$comment_to_edit['comment_author_email'] = esc_attr( $value );
        				}
        				wp_update_comment( $comment_to_edit );
        			}
        			
        			return null;
        		} */
        public function after_save_post( $post_id )
        {
            if ( !isset( $_POST['acf'] ) ) {
                return $post_id;
            }
            $form = false;
            
            if ( isset( $_POST['_acf_form'] ) ) {
                // Load registered form using id.
                $form = acf()->form_front->get_form( $_POST['_acf_form'] );
                // Fallback to encrypted JSON.
                if ( !$form ) {
                    $form = json_decode( acf_decrypt( $_POST['_acf_form'] ), true );
                }
            }
            
            if ( isset( $_POST['_acf_element_id'] ) ) {
                acf_update_metadata(
                    $post_id,
                    'acfef_form_source',
                    $_POST['_acf_element_id'],
                    true
                );
            }
            
            if ( strpos( $post_id, 'user_' ) !== false ) {
                $user_id = explode( 'user_', $post_id )[1];
                $user_to_edit = get_user_by( 'ID', $user_id );
                if ( isset( $form['user_manager'] ) ) {
                    update_user_meta( $user_id, 'acfef_manager', $form['user_manager'] );
                }
                
                if ( isset( $form['display_name_default'] ) && empty($_POST['custom_display_name']) ) {
                    switch ( $form['display_name_default'] ) {
                        case 'user_login':
                            $display_name = $user_to_edit->user_login;
                            break;
                        case 'user_email':
                            $display_name = $user_to_edit->user_email;
                            break;
                        case 'first_name':
                            $display_name = $user_to_edit->first_name;
                            break;
                        case 'last_name':
                            $display_name = $user_to_edit->last_name;
                            break;
                        case 'nickname':
                            $display_name = $user_to_edit->nickname;
                            break;
                        case 'first_last':
                            $display_name = $user_to_edit->first_name . ' ' . $user_to_edit->last_name;
                            break;
                    }
                    if ( isset( $display_name ) ) {
                        wp_update_user( [
                            'ID'           => $user_id,
                            'display_name' => $display_name,
                        ] );
                    }
                }
            
            }
        
        }
        
        public function exclude_groups( $field_group )
        {
            
            if ( empty($field_group['acfef_group']) ) {
                return $field_group;
            } elseif ( is_admin() ) {
                
                if ( function_exists( 'get_current_screen' ) ) {
                    $current_screen = get_current_screen();
                    
                    if ( isset( $current_screen->post_type ) && $current_screen->post_type == 'acf_frontend_form' ) {
                        return $field_group;
                    } else {
                        return null;
                    }
                
                }
            
            }
        
        }
        
        public function before_validation()
        {
            if ( isset( $_POST['_acf_field_id'] ) ) {
                acf_add_local_field( array(
                    'key'    => 'acfef_post_type',
                    'label'  => __( 'Post Type', 'acf-frontend-form-element' ),
                    'name'   => 'acfef_post_type',
                    'type'   => 'post_type',
                    'layout' => 'vertical',
                ) );
            }
        }
        
        public function skip_validation()
        {
            if ( isset( $_POST['_acf_status'] ) && $_POST['_acf_status'] != 'publish' ) {
                acf_reset_validation_errors();
            }
        }
        
        public function enqueue_scripts()
        {
            wp_enqueue_style( 'acfef' );
            wp_enqueue_style( 'acfef-modal' );
            wp_enqueue_script( 'acfef' );
            wp_enqueue_script( 'acfef-modal' );
            wp_enqueue_style( 'dashicons' );
        }
        
        public function prepare_form_fields( $field )
        {
            if ( empty($field['parent']) ) {
                return $field;
            }
            
            if ( get_post_type( $field['parent'] ) == 'acf_frontend_form' ) {
                if ( empty($field['data_name']) ) {
                    $field['data_name'] = $field['type'];
                }
                $form_id = str_replace( 'form_', '', get_post_field( 'post_name', $field['parent'] ) );
                $field['key'] = 'acfef_' . $form_id . '_' . $field['data_name'];
            }
            
            return $field;
        }
        
        public function prepare_field_hidden( $field )
        {
            if ( empty($field['invisible']) ) {
                return $field;
            }
            
            if ( isset( $field['wrapper']['class'] ) ) {
                $field['wrapper']['class'] .= ' acf-hidden';
            } else {
                $field['wrapper']['class'] = 'acf-hidden';
            }
            
            return $field;
        }
        
        public function prepare_field_frontend( $field )
        {
            // bail early if no 'admin_only' setting
            if ( empty($field['only_front']) ) {
                return $field;
            }
            $render = true;
            // return false if is admin (removes field)
            if ( is_admin() && !wp_doing_ajax() ) {
                $render = false;
            }
            if ( acf_frontend_edit_mode() ) {
                $render = true;
            }
            if ( !$render ) {
                return false;
            }
            // return\
            return $field;
        }
        
        public function prepare_field_column( $field )
        {
            if ( !empty($field['start_column']) ) {
                echo  '<div style="width:' . $field['start_column'] . '%" class="acf-column">' ;
            }
            if ( isset( $field['end_column'] ) ) {
                echo  '</div>' ;
            }
            // return\
            return $field;
        }
        
        public function include_field_types()
        {
            //general
            include_once 'fields/general/related-terms.php';
            include_once 'fields/general/submit-button.php';
            include_once 'fields/general/upload-image.php';
            include_once 'fields/general/upload-images.php';
            include_once 'fields/general/list-items.php';
            include_once 'fields/general/group.php';
            //include_once('fields/general/flexible-content.php');
            include_once 'fields/general/text.php';
            include_once 'fields/general/file.php';
            include_once 'fields/general/relationship.php';
            include_once 'fields/general/no-suggestions.php';
            //post
            include_once 'fields/post/title.php';
            include_once 'fields/post/content.php';
            include_once 'fields/post/excerpt.php';
            include_once 'fields/post/slug.php';
            include_once 'fields/post/featured-image.php';
            include_once 'fields/post/post-type.php';
            include_once 'fields/post/date.php';
            include_once 'fields/post/author.php';
            include_once 'fields/post/menu-order.php';
            include_once 'fields/post/allow-comments.php';
            //term
            include_once 'fields/term/name.php';
            include_once 'fields/term/slug.php';
            include_once 'fields/term/description.php';
            //user
            include_once 'fields/user/username.php';
            include_once 'fields/user/email.php';
            include_once 'fields/user/password.php';
            include_once 'fields/user/password-confirm.php';
            include_once 'fields/user/first-name.php';
            include_once 'fields/user/last-name.php';
            include_once 'fields/user/nickname.php';
            include_once 'fields/user/display-name.php';
            include_once 'fields/user/bio.php';
            include_once 'fields/user/role.php';
        }
        
        public function hide_field_name_setting()
        {
            global  $post ;
            if ( empty($post->post_type) ) {
                return;
            }
            
            if ( $post->post_type == 'acf-field-group' || $post->post_type == 'acf_frontend_form' ) {
                $fields = array(
                    'submit-button',
                    'post-title',
                    'post-slug',
                    'post-type',
                    'post-content',
                    'post-excerpt',
                    'featured-image',
                    'post-date',
                    'menu-order',
                    'allow-comments',
                    'username',
                    'user-email'
                );
                echo  '<style>' ;
                foreach ( $fields as $field ) {
                    echo  '.acf-field-object-' . $field . ' .acf-field-setting-name{display:none}.acf-field-object-' . $field . ' .li-field-name{visibility:hidden}' ;
                }
                echo  '</style>' ;
            }
        
        }
        
        public function __construct()
        {
            add_action( 'acf/include_field_types', array( $this, 'include_field_types' ), 6 );
            add_action( 'acf/enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_action( 'admin_footer', array( $this, 'hide_field_name_setting' ) );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_form_fields' ), 3 );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_field_hidden' ), 3 );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_field_frontend' ), 3 );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_field_column' ), 3 );
            //Add field settings by type
            add_action( 'acf/render_field_settings', [ $this, 'frontend_only_setting' ] );
            add_action( 'acf/render_field_settings', [ $this, 'read_only_setting' ] );
            add_filter( 'acf/get_field_types', [ $this, 'hide_acfef_fields' ] );
            add_action(
                'acf/save_post',
                [ $this, 'after_save_post' ],
                10,
                1
            );
            add_filter(
                'acf/update_value',
                [ $this, 'update_acfef_values' ],
                7,
                3
            );
            add_filter( 'acf/load_field_group', [ $this, 'exclude_groups' ] );
            add_action( 'acf/validate_save_post', [ $this, 'before_validation' ], 1 );
            add_action( 'acf/validate_save_post', [ $this, 'skip_validation' ], 999 );
            require_once __DIR__ . '/forms/classes/form-submit.php';
            require_once __DIR__ . '/forms/classes/form-display.php';
            require_once __DIR__ . '/forms/classes/permissions.php';
            require_once __DIR__ . '/forms/helpers/data-fetch.php';
            require_once __DIR__ . '/forms/classes/shortcodes.php';
            require_once __DIR__ . '/forms/helpers/permissions.php';
            require_once __DIR__ . '/forms/actions/action-base.php';
            //actions
            require_once __DIR__ . '/forms/actions/term.php';
            require_once __DIR__ . '/forms/actions/user.php';
            require_once __DIR__ . '/forms/actions/post.php';
            require_once __DIR__ . '/forms/actions/comment.php';
        }
    
    }
    acff()->acf_extension = new ACFFrontend_Hooks();
}
