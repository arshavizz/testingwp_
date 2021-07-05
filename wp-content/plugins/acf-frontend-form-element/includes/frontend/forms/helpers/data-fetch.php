<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

function acf_frontend_get_field_data( $type = null )
{
    $field_types = [];
    $acf_field_groups = acf_get_field_groups();
    // bail early if no field groups
    if ( empty($acf_field_groups) ) {
        die;
    }
    // loop through array and add to field 'choices'
    if ( $acf_field_groups ) {
        foreach ( $acf_field_groups as $field_group ) {
            if ( !empty($field_group['acfef_group']) ) {
                continue;
            }
            $field_group_fields = acf_get_fields( $field_group['key'] );
            if ( is_array( $field_group_fields ) ) {
                foreach ( $field_group_fields as $acf_field ) {
                    
                    if ( $type ) {
                        if ( is_array( $type ) && in_array( $acf_field['type'], $type ) || !is_array( $type ) && $acf_field['type'] == $type ) {
                            $field_types[$acf_field['key']] = $acf_field['label'];
                        }
                    } else {
                        $field_types[$acf_field['key']]['type'] = $acf_field['type'];
                        $field_types[$acf_field['key']]['label'] = $acf_field['label'];
                        $field_types[$acf_field['key']]['name'] = $acf_field['name'];
                    }
                
                }
            }
        }
    }
    return $field_types;
}

function acf_frontend_user_id_fields()
{
    $fields = acf_frontend_get_acf_field_choices( false, 'user' );
    $keys = array_merge( [
        '[author]' => __( 'Post Author', 'acf-frontend-form-element' ),
    ], $fields );
    return $keys;
}

function acf_frontend_get_user_roles( $exceptions = array(), $all = false )
{
    if ( !current_user_can( 'administrator' ) ) {
        $exceptions[] = 'administrator';
    }
    $user_roles = array();
    if ( $all ) {
        $user_roles['all'] = __( 'All', 'acf-frontend-form-element' );
    }
    global  $wp_roles ;
    // loop through array and add to field 'choices'
    foreach ( $wp_roles->roles as $role => $settings ) {
        if ( !in_array( strtolower( $role ), $exceptions ) ) {
            $user_roles[$role] = $settings['name'];
        }
    }
    return $user_roles;
}

function acf_frontend_get_user_caps( $exceptions = array(), $all = false )
{
    $user_caps = array();
    $data = get_userdata( get_current_user_id() );
    
    if ( is_object( $data ) ) {
        $current_user_caps = $data->allcaps;
        foreach ( $current_user_caps as $cap => $true ) {
            if ( !in_array( strtolower( $cap ), $exceptions ) ) {
                $user_caps[$cap] = $cap;
            }
        }
    }
    
    return $user_caps;
}

function acf_frontend_get_acf_field_group_choices()
{
    $field_group_choices = [];
    $acf_field_groups = acf_get_field_groups();
    // loop through array and add to field 'choices'
    if ( is_array( $acf_field_groups ) ) {
        foreach ( $acf_field_groups as $field_group ) {
            if ( is_array( $field_group ) && !isset( $field_group['acfef_group'] ) ) {
                $field_group_choices[$field_group['key']] = $field_group['title'];
            }
        }
    }
    return $field_group_choices;
}

/* add_filter('acf/get_fields', function( $fields, $parent ){
	$group = explode( 'acfef_', $parent['key'] ); 

	if( empty( $group[1] ) ) return $fields;

	return array();
}, 5, 2);
 */
function acf_frontend_get_acf_field_choices( $groups = false, $type = false )
{
    $user_fields = $all_fields = [];
    
    if ( $groups ) {
        $acf_field_groups = $groups;
    } else {
        $acf_field_groups = acf_get_field_groups();
    }
    
    // bail early if no field groups
    if ( empty($acf_field_groups) ) {
        return array();
    }
    foreach ( $acf_field_groups as $group ) {
        if ( !is_array( $group ) ) {
            $group = acf_get_field_group( $group );
        }
        if ( !empty($field_group['acfef_group']) ) {
            continue;
        }
        $group_fields = acf_get_fields( $group );
        if ( is_array( $group_fields ) ) {
            foreach ( $group_fields as $acf_field ) {
                if ( !is_array( $acf_field ) ) {
                    continue;
                }
                $acf_field_key = ( isset( $acf_field['_clone'] ) ? $acf_field['__key'] : $acf_field['key'] );
                
                if ( $type == 'user' && $acf_field['type'] == 'user' ) {
                    $user_fields[$acf_field['name']] = $acf_field['label'];
                } else {
                    
                    if ( $groups ) {
                        $all_fields[] = $acf_field_key;
                    } else {
                        $all_fields[$acf_field_key] = $acf_field['label'];
                    }
                
                }
            
            }
        }
    }
    if ( $type == 'user' && isset( $user_fields ) ) {
        return $user_fields;
    }
    return $all_fields;
}

function acf_frontend_get_post_type_choices()
{
    $post_type_choices = [];
    $args = array();
    $output = 'names';
    // names or objects, note names is the default
    $operator = 'and';
    // 'and' or 'or'
    $post_types = get_post_types( $args, $output, $operator );
    // loop through array and add to field 'choices'
    if ( is_array( $post_types ) ) {
        foreach ( $post_types as $post_type ) {
            $post_type_choices[$post_type] = str_replace( '_', ' ', ucfirst( $post_type ) );
        }
    }
    return $post_type_choices;
}

function acf_frontend_get_image_folders()
{
    $folders = [
        'all' => __( 'All Folders', 'acf-frontend-form-element' ),
    ];
    $taxonomies = get_terms( array(
        'taxonomy'   => 'happyfiles_category',
        'hide_empty' => false,
    ) );
    if ( empty($taxonomies) ) {
        return $folders;
    }
    foreach ( $taxonomies as $category ) {
        $folders[$category->name] = ucfirst( esc_html( $category->name ) );
    }
    return $folders;
}

function acf_frontend_get_random_string( $length = 15 )
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen( $characters );
    $randomString = '';
    for ( $i = 0 ;  $i < $length ;  $i++ ) {
        $randomString .= $characters[rand( 0, $charactersLength - 1 )];
    }
    return $randomString;
}

function acf_frontend_get_client_ip()
{
    $server_ip_keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    foreach ( $server_ip_keys as $key ) {
        if ( isset( $_SERVER[$key] ) && filter_var( $_SERVER[$key], FILTER_VALIDATE_IP ) ) {
            return $_SERVER[$key];
        }
    }
    // Fallback local ip.
    return '127.0.0.1';
}

function acf_frontend_get_site_domain()
{
    return str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
}

function acf_frontend_esc_attrs( $attrs )
{
    $html = '';
    // Loop over attrs and validate data types.
    foreach ( $attrs as $k => $v ) {
        // String (but don't trim value).
        
        if ( is_string( $v ) && $k !== 'value' ) {
            $v = trim( $v );
            // Boolean
        } elseif ( is_bool( $v ) ) {
            $v = ( $v ? 1 : 0 );
            // Object
        } elseif ( is_array( $v ) || is_object( $v ) ) {
            $v = json_encode( $v );
        }
        
        // Generate HTML.
        $html .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
    }
    // Return trimmed.
    return trim( $html );
}

function acf_frontend_slug_exists( $post_name )
{
    global  $wpdb ;
    
    if ( $wpdb->get_row( "SELECT post_name FROM {$wpdb->posts} WHERE post_name = '{$post_name}'", 'ARRAY_A' ) ) {
        return true;
    } else {
        return false;
    }

}

function acf_frontend_parse_args( $args, $defaults )
{
    $new_args = (array) $defaults;
    foreach ( $args as $key => $value ) {
        
        if ( is_array( $value ) && isset( $new_args[$key] ) ) {
            $new_args[$key] = acf_frontend_parse_args( $value, $new_args[$key] );
        } else {
            $new_args[$key] = $value;
        }
    
    }
    return $new_args;
}

function acf_frontend_edit_mode()
{
    $edit_mode = false;
    if ( !empty(acff()->elementor) ) {
        $edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
    }
    return $edit_mode;
}

function acf_frontend_get_product_object()
{
    
    if ( isset( $GLOBALS['acfef_form'] ) ) {
        $form = $GLOBALS['acfef_form'];
        if ( $form['hidden_fields']['main_action'] == 'edit_product' ) {
            return wc_get_product( $form['post_id'] );
        }
    }
    
    return false;
}

function acf_frontend_get_field_type_groups()
{
    $fields = [];
    $fields['acf'] = array(
        'label'   => __( 'ACF Field', 'acf-frontend-form-element' ),
        'options' => array(
        'ACF_fields'       => __( 'ACF Fields', 'acf-frontend-form-element' ),
        'ACF_field_groups' => __( 'ACF Field Groups', 'acf-frontend-form-element' ),
    ),
    );
    $fields['post'] = array(
        'label'   => __( 'Post' ),
        'options' => array(
        'title'          => __( 'Post Title', 'acf-frontend-form-element' ),
        'slug'           => __( 'Slug', 'acf-frontend-form-element' ),
        'content'        => __( 'Post Content', 'acf-frontend-form-element' ),
        'featured_image' => __( 'Featured Image', 'acf-frontend-form-element' ),
        'excerpt'        => __( 'Post Excerpt', 'acf-frontend-form-element' ),
        'categories'     => __( 'Categories', 'acf-frontend-form-element' ),
        'tags'           => __( 'Tags', 'acf-frontend-form-element' ),
        'author'         => __( 'Post Author', 'acf-frontend-form-element' ),
        'published_on'   => __( 'Published On', 'acf-frontend-form-element' ),
        'post_type'      => __( 'Post Type', 'acf-frontend-form-element' ),
        'menu_order'     => __( 'Menu Order' ),
        'allow_comments' => __( 'Allow Comments' ),
        'taxonomy'       => __( 'Custom Taxonomy', 'acf-frontend-form-element' ),
    ),
    );
    $fields['user'] = array(
        'label'   => __( 'User', 'acf-frontend-form-element' ),
        'options' => array(
        'username'         => __( 'Username', 'acf-frontend-form-element' ),
        'password'         => __( 'Password', 'acf-frontend-form-element' ),
        'confirm_password' => __( 'Confirm Password', 'acf-frontend-form-element' ),
        'email'            => __( 'Email', 'acf-frontend-form-element' ),
        'first_name'       => __( 'First Name', 'acf-frontend-form-element' ),
        'last_name'        => __( 'Last Name', 'acf-frontend-form-element' ),
        'nickname'         => __( 'Nickname', 'acf-frontend-form-element' ),
        'display_name'     => __( 'Display Name', 'acf-frontend-form-element' ),
        'bio'              => __( 'Biography', 'acf-frontend-form-element' ),
        'role'             => __( 'Role', 'acf-frontend-form-element' ),
    ),
    );
    $fields['term'] = array(
        'label'   => __( 'Term', 'acf-frontend-form-element' ),
        'options' => array(
        'term_name'        => __( 'Term Name', 'acf-frontend-form-element' ),
        'term_slug'        => __( 'Term Slug', 'acf-frontend-form-element' ),
        'term_description' => __( 'Term Description', 'acf-frontend-form-element' ),
    ),
    );
    $fields['layout'] = array(
        'label'   => __( 'Layout', 'acf-frontend-form-element' ),
        'options' => array(
        'message' => __( 'Message', 'acf-frontend-form-element' ),
        'column'  => __( 'Column', 'acf-frontend-form-element' ),
    ),
    );
    return $fields;
}
