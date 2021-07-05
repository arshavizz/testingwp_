<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Handles the admin part of the forms
 *
 * @since 1.0.0
 *
 */
class ACF_Frontend_Forms
{
    /**
     * Adds a form key to a form if one doesn't exist
     * 
     * @since 1.0.0
     *
     */
    function save_post( $post_id, $post )
    {
        // do not save if this is an auto save routine
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        // bail early if not acfef form
        if ( $post->post_type !== 'acf_frontend_form' ) {
            return $post_id;
        }
        
        if ( empty($post->post_name) ) {
            $form_key = 'form_' . uniqid();
            remove_action( 'save_post', array( $this, 'save_post' ) );
            wp_update_post( array(
                'ID'        => $post_id,
                'post_name' => $form_key,
            ) );
            add_action(
                'save_post',
                array( $this, 'save_post' ),
                10,
                2
            );
        } else {
            // only save once! WordPress save's a revision as well.
            if ( wp_is_post_revision( $post_id ) ) {
                return $post_id;
            }
            // verify nonce
            if ( !acf_verify_nonce( 'frontend_form' ) ) {
                return $post_id;
            }
            // disable filters to ensure ACF loads raw data from DB
            acf_disable_filters();
            // save fields
            if ( !empty($_POST['acf_fields']) ) {
                // loop
                foreach ( $_POST['acf_fields'] as $field ) {
                    // vars
                    $specific = false;
                    $save = acf_extract_var( $field, 'save' );
                    // only saved field if has changed
                    if ( $save == 'meta' ) {
                        $specific = array( 'menu_order', 'post_parent' );
                    }
                    // set parent
                    if ( !$field['parent'] ) {
                        $field['parent'] = $post_id;
                    }
                    // save field
                    acf_update_field( $field, $specific );
                }
            }
            // delete fields
            
            if ( !empty($_POST['_acf_delete_fields']) ) {
                // clean
                $ids = explode( '|', $_POST['_acf_delete_fields'] );
                $ids = array_map( 'intval', $ids );
                // loop
                foreach ( $ids as $id ) {
                    // bai early if no id
                    if ( !$id ) {
                        continue;
                    }
                    // delete
                    acf_delete_field( $id );
                }
            }
            
            
            if ( !empty($_POST['form']) ) {
                $_POST['form']['ID'] = $post_id;
                $_POST['form']['title'] = $_POST['post_title'];
                $_POST['form']['key'] = $post->post_name;
                $this->update_form_post( $_POST['form'] );
            }
        
        }
    
    }
    
    public function update_form_post( $data = array() )
    {
        unset( $data['emails_to_send'][0] );
        // may have been posted. Remove slashes
        $data = wp_unslash( $data );
        // parse types (converts string '0' to int 0)
        $data = acf_parse_types( $data );
        // extract some args
        $extract = acf_extract_vars( $data, array(
            'ID',
            'key',
            'title',
            'menu_order',
            'fields',
            'active',
            '_valid'
        ) );
        // vars
        $data = maybe_serialize( $data );
        // save
        $save = array(
            'ID'           => $extract['ID'],
            'post_status'  => 'publish',
            'post_title'   => $extract['title'],
            'post_name'    => $extract['key'],
            'post_type'    => 'acf_frontend_form',
            'post_excerpt' => sanitize_title( $extract['title'] ),
            'post_content' => $data,
            'menu_order'   => $extract['menu_order'],
        );
        // slash data
        // - WP expects all data to be slashed and will unslash it (fixes '\' character issues)
        $save = wp_slash( $save );
        // update the field group and update the ID
        
        if ( !empty($data['ID']) ) {
            wp_update_post( $save );
        } else {
            $form_id = wp_insert_post( $save );
        }
        
        // return
        return $data;
    }
    
    /**
     * Displays the form key after the title
     *
     * @since 1.0.0
     *
     */
    function edit_form_after_title()
    {
        global  $post ;
        
        if ( 'acf_frontend_form' == $post->post_type ) {
            // render post data
            acf_form_data( array(
                'screen'        => 'frontend_form',
                'post_id'       => $post->ID,
                'delete_fields' => 0,
                'validation'    => 0,
            ) );
            
            if ( $post->post_name ) {
                echo  '<div id="edit-slug-box">' ;
                echo  sprintf( '%s: <code>[acf_frontend form="%s"]</code>', __( 'Shortcode', 'acf-frontend-form-element' ), $post->post_name ) ;
                echo  '</div>' ;
            }
        
        }
    
    }
    
    /**
     * Adds custom columns to the listings page
     *
     * @since 1.0.0
     *
     */
    function manage_columns( $columns )
    {
        $new_columns = array(
            'shortcode' => __( 'Shortcode', 'acf-frontend-form-element' ),
        );
        // Remove date column
        unset( $columns['date'] );
        return array_merge( array_splice( $columns, 0, 2 ), $new_columns, $columns );
    }
    
    /**
     * Outputs the content for the custom columns
     *
     * @since 1.0.0
     *
     */
    function columns_content( $column, $post_id )
    {
        //$form = acff()->form_display->get_form( $post_id );
        if ( 'shortcode' == $column ) {
            echo  sprintf( '<code>[acf_frontend form="%s"]</code>', get_post_field( 'post_name', $post_id ) ) ;
        }
    }
    
    /**
     * Hides the months filter on the forms listing page.
     *
     * @since 1.6.5
     *
     */
    function disable_months_dropdown( $disabled, $post_type )
    {
        if ( 'acf_frontend_form' != $post_type ) {
            return $disabled;
        }
        return true;
    }
    
    /**
     * Registers the form permissions settings fields
     *
     * @since 1.0.0
     *
     */
    function mb_permissions()
    {
        global  $form ;
        $fields = (require_once __DIR__ . '/fields/permissions.php');
        $this->render_fields( $fields, $form );
    }
    
    /*  mb_actions
     *
     *  This function will render the HTML for the medtabox 'Actions'
     *
     */
    function mb_actions()
    {
        global  $form ;
        $fields = (require_once __DIR__ . '/fields/actions.php');
        $this->render_fields( $fields, $form );
    }
    
    /*  mb_emails
     *
     *  This function will render the HTML for the medtabox 'Actions'
     *
     */
    function mb_emails()
    {
        global  $form ;
        $fields = (require_once __DIR__ . '/fields/emails.php');
        $this->render_fields( $fields, $form );
    }
    
    /*  mb_settings
     *
     *  This function will render the HTML for the medtabox 'Form Settings'
     *
     */
    function mb_settings()
    {
        global  $form ;
        $fields = (require_once __DIR__ . '/fields/settings.php');
        $this->render_fields( $fields, $form );
    }
    
    function render_fields( $fields, $form )
    {
        foreach ( $fields as $field ) {
            $field['wrapper'] = array(
                'width' => '',
                'class' => '',
                'id'    => '',
            );
            $field['prefix'] = 'form';
            $field['name'] = $field['key'];
            if ( isset( $form[$field['key']] ) ) {
                $field['value'] = $form[$field['key']];
            }
            acff()->form_display->render_field_wrap( $field );
        }
    }
    
    /*
     *  mb_fields
     *
     *  This function will render the HTML for the medtabox 'acf-field-group-fields'
     *
     *  @type	function
     *  @date	28/09/13
     *  @since	5.0.0
     *
     *  @param	N/A
     *  @return	N/A
     */
    function mb_fields()
    {
        // global
        global  $post ;
        $form_fields = array();
        $args = array(
            'post_type'      => 'acf-field',
            'posts_per_page' => '-1',
            'post_parent'    => $post->ID,
            'fields'         => 'ids',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        $fields_query = get_posts( $args );
        if ( $fields_query ) {
            foreach ( $fields_query as $field ) {
                $form_fields[] = acf_get_field( $field );
            }
        }
        // get fields
        $view = array(
            'fields' => $form_fields,
            'parent' => 0,
        );
        // load view
        acf_get_view( 'field-group-fields', $view );
    }
    
    function admin_head()
    {
        // global
        global  $post, $form ;
        // set global var
        $form = $this->get_form_data( $post );
        // metaboxes
        add_meta_box(
            'acf-field-group-fields',
            __( "Fields", 'acf' ),
            array( $this, 'mb_fields' ),
            'acf_frontend_form',
            'normal',
            'high'
        );
        add_meta_box(
            'acf-frontend-form-settings',
            __( "Settings", 'acf' ),
            array( $this, 'mb_settings' ),
            'acf_frontend_form',
            'side',
            'core'
        );
        $meta_boxes = array(
            'actions'     => 'Actions',
            'permissions' => 'Permissions',
        );
        foreach ( $meta_boxes as $name => $label ) {
            add_meta_box(
                'acf-frontend-form-' . $name,
                __( $label, 'acf-frontend-form-element' ),
                array( $this, 'mb_' . $name ),
                'acf_frontend_form',
                'normal',
                'core'
            );
        }
    }
    
    public function get_form_data( $post )
    {
        $form = maybe_unserialize( $post->post_content );
        if ( !$form ) {
            $form = array();
        }
        $form = acf_frontend_parse_args( $form, array(
            'redirect'            => 'current',
            'custom_url'          => '',
            'show_update_message' => 1,
            'update_message'      => __( 'Your post has been updated successfully.', 'acf-frontend-form-element' ),
            'main_action'         => 'edit_post',
            'post_to_edit'        => 'current',
            'url_query_post'      => '',
            'select_post'         => '',
            'who_can_see'         => '',
            'by_role'             => '',
            'user_id'             => '',
            'dynamic'             => '',
        ) );
        return $form;
    }
    
    function admin_enqueue_scripts()
    {
        // no autosave
        wp_dequeue_script( 'autosave' );
        // custom scripts
        wp_enqueue_style( 'acf-field-group' );
        wp_enqueue_script( 'acf-field-group' );
        // localize text
        acf_localize_text( array(
            'The string "field_" may not be used at the start of a field name' => __( 'The string "field_" may not be used at the start of a field name', 'acf' ),
            'This field cannot be moved until its changes have been saved'     => __( 'This field cannot be moved until its changes have been saved', 'acf' ),
            'Field group title is required'                                    => __( 'Form title is required', 'acf' ),
            'Move to trash. Are you sure?'                                     => __( 'Move to trash. Are you sure?', 'acf' ),
            'No toggle fields available'                                       => __( 'No toggle fields available', 'acf' ),
            'Move Custom Field'                                                => __( 'Move Custom Field', 'acf' ),
            'Checked'                                                          => __( 'Checked', 'acf' ),
            '(no label)'                                                       => __( '(no label)', 'acf' ),
            '(this field)'                                                     => __( '(this field)', 'acf' ),
            'copy'                                                             => __( 'copy', 'acf' ),
            'or'                                                               => __( 'or', 'acf' ),
            'Null'                                                             => __( 'Null', 'acf' ),
        ) );
        // localize data
        acf_localize_data( array(
            'fieldTypes' => acf_get_field_types_info(),
        ) );
        // 3rd party hook
        do_action( 'acf/field_group/admin_enqueue_scripts' );
    }
    
    function current_screen()
    {
        // validate screen
        if ( !acf_is_screen( 'acf_frontend_form' ) ) {
            return;
        }
        // disable filters to ensure ACF loads raw data from DB
        acf_disable_filters();
        // enqueue scripts
        acf_enqueue_scripts();
        add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'acf/input/admin_head', array( $this, 'admin_head' ) );
    }
    
    function before_posts_query()
    {
        $fields = array( array(
            'key'        => 'select_post',
            'label'      => 'Select Post',
            'name'       => 'select_post',
            'prefix'     => 'form',
            'type'       => 'post_object',
            'post_type'  => '',
            'taxonomy'   => '',
            'allow_null' => 0,
            'multiple'   => 0,
            'ui'         => 1,
        ) );
        foreach ( $fields as $field ) {
            $field['prefix'] = 'form';
            $field['name'] = $field['key'];
            acf_add_local_field( $field );
        }
    }
    
    function before_users_query()
    {
        $fields = array( array(
            'key'           => 'user_id',
            'label'         => __( 'Select By User', 'acf-frontend-form-element' ),
            'type'          => 'user',
            'instructions'  => '',
            'allow_null'    => 0,
            'multiple'      => 1,
            'ajax'          => 1,
            'ui'            => 1,
            'return_format' => 'id',
        ) );
        foreach ( $fields as $field ) {
            $field['prefix'] = 'form';
            $field['name'] = $field['key'];
            acf_add_local_field( $field );
        }
    }
    
    function enqueue_admin_scripts()
    {
        global  $post ;
        if ( empty($post->post_type) || $post->post_type != 'acf_frontend_form' ) {
            return;
        }
        
        if ( acff()->dev_mode ) {
            $min = '';
        } else {
            $min = '-min';
        }
        
        wp_enqueue_style(
            'acf-frontend-form-admin',
            ACFEF_URL . 'includes/assets/css/admin-min.css',
            array(),
            ACFEF_ASSETS_VERSION
        );
        wp_enqueue_script(
            'acf-frontend-form-admin',
            ACFEF_URL . 'includes/assets/js/admin' . $min . '.js',
            array( 'jquery' ),
            ACFEF_ASSETS_VERSION,
            true
        );
        $text = array(
            'form' => array(
            'label'   => __( 'Form', 'acf-frontend-form-element' ),
            'options' => array(
            'all_fields'     => __( 'All Fields', 'acf-frontend-form-element' ),
            'acf:field_name' => __( 'ACF Field', 'acf-frontend-form-element' ),
        ),
        ),
            'post' => array(
            'label'   => __( 'Post', 'acf-frontend-form-element' ),
            'options' => array(
            'post:id'             => __( 'Post ID', 'acf-frontend-form-element' ),
            'post:title'          => __( 'Title', 'acf-frontend-form-element' ),
            'post:content'        => __( 'Content', 'acf-frontend-form-element' ),
            'post:featured_image' => __( 'Featured Image', 'acf-frontend-form-element' ),
        ),
        ),
            'user' => array(
            'label'   => __( 'User', 'acf-frontend-form-element' ),
            'options' => array(
            'user:id'         => __( 'User ID', 'acf-frontend-form-element' ),
            'user:username'   => __( 'Username', 'acf-frontend-form-element' ),
            'user:email'      => __( 'Email', 'acf-frontend-form-element' ),
            'user:first_name' => __( 'First Name', 'acf-frontend-form-element' ),
            'user:last_name'  => __( 'Last Name', 'acf-frontend-form-element' ),
            'user:role'       => __( 'Role', 'acf-frontend-form-element' ),
        ),
        ),
        );
        if ( $text ) {
            wp_localize_script( 'acf-frontend-form-admin', 'acffdv', $text );
        }
    }
    
    function __construct()
    {
        require_once __DIR__ . '/post-types.php';
        // Actions
        add_action(
            'edit_form_after_title',
            array( $this, 'edit_form_after_title' ),
            11,
            0
        );
        add_action(
            'save_post',
            array( $this, 'save_post' ),
            10,
            2
        );
        add_action( 'current_screen', array( $this, 'current_screen' ) );
        add_filter(
            'manage_acf_frontend_form_posts_columns',
            array( $this, 'manage_columns' ),
            10,
            1
        );
        add_action(
            'manage_acf_frontend_form_posts_custom_column',
            array( $this, 'columns_content' ),
            10,
            2
        );
        add_filter(
            'disable_months_dropdown',
            array( $this, 'disable_months_dropdown' ),
            10,
            2
        );
        add_action( 'wp_ajax_acf/fields/post_object/query', array( $this, 'before_posts_query' ), 4 );
        add_action( 'wp_ajax_nopriv_acf/fields/post_object/query', array( $this, 'before_posts_query' ), 4 );
        add_action( 'wp_ajax_acf/fields/user/query', array( $this, 'before_users_query' ), 4 );
        add_action( 'wp_ajax_nopriv_acf/fields/user/query', array( $this, 'before_users_query' ), 4 );
        add_action(
            'admin_enqueue_scripts',
            array( $this, 'enqueue_admin_scripts' ),
            10,
            0
        );
        add_action(
            'acf/prepare_field',
            array( $this, 'dynamic_value_insert' ),
            15,
            1
        );
        add_action(
            'media_buttons',
            array( $this, 'add_dynamic_value_button' ),
            15,
            1
        );
    }
    
    function dynamic_value_insert( $field )
    {
        if ( empty($field['dynamic_value_choices']) ) {
            return $field;
        }
        $field['wrapper']['data-dynamic_values'] = '1';
        if ( $field['type'] == 'text' ) {
            $field['type'] = 'no_suggestions';
        }
        return $field;
    }
    
    function add_dynamic_value_button( $editor )
    {
        global  $post ;
        if ( empty($post->post_type) || $post->post_type != 'acf_frontend_form' ) {
            return;
        }
        if ( is_string( $editor ) && 'acf-editor' == substr( $editor, 0, 10 ) ) {
            echo  '<a class="dynamic-value-options button">' . __( 'Dynamic Value', 'acf-frontend-form-element' ) . '</a>' ;
        }
    }
    
    function render_shortcode_option( $field, $parents = array() )
    {
        $insert_value = '';
        
        if ( empty($parents) ) {
            $insert_value = sprintf( '[form:%s]', $field['name'] );
        } else {
            $hierarchy = array_merge( $parents, array( $field['name'] ) );
            $top_level_name = array_shift( $hierarchy );
            $insert_value = sprintf( '[form:%s[%s]]', $top_level_name, join( '][', $hierarchy ) );
        }
        
        $label = wp_strip_all_tags( $field['label'] );
        $type = acf_get_field_type_label( $field['type'] );
        echo  sprintf( '<div class="field-option" data-insert-value="%s" role="button">', $insert_value ) ;
        echo  sprintf( '<span class="field-name">%s</span><span class="field-type">%s</span>', $label, $type ) ;
        echo  '</div>' ;
        // Append options for sub fields if they exist (and we are dealing with a group or clone field)
        $parent_field_types = array( 'group', 'clone' );
        
        if ( in_array( $field['type'], $parent_field_types ) && isset( $field['sub_fields'] ) ) {
            array_push( $parents, $field['name'] );
            echo  '<div class="sub-fields-wrapper">' ;
            foreach ( $field['sub_fields'] as $sub_field ) {
                $this->render_shortcode_option( $sub_field, $parents );
            }
            echo  '</div>' ;
        }
    
    }

}
new ACF_Frontend_Forms();