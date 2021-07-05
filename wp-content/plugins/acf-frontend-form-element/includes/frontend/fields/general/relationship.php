<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'acf_frontend_relationship_field' ) ) {
    class acf_frontend_relationship_field
    {
        public function add_edit_field( $field )
        {
            $users = get_users();
            $label = __( 'Dynamic', 'acf-frontend-form-element' );
            $user_choices = [
                $label => [
                'current_user' => __( 'Current User', 'acf-frontend-form-element' ),
            ],
            ];
            // Append.
            
            if ( $users ) {
                $user_label = __( 'Users', 'acf-frontend-form-element' );
                $user_choices[$user_label] = [];
                foreach ( $users as $user ) {
                    $user_text = $user->user_login;
                    // Add name.
                    
                    if ( $user->first_name && $user->last_name ) {
                        $user_text .= " ({$user->first_name} {$user->last_name})";
                    } elseif ( $user->first_name ) {
                        $user_text .= " ({$user->first_name})";
                    }
                    
                    $user_choices[$user_label][$user->ID] = $user_text;
                }
            }
            
            acf_render_field_setting( $field, array(
                'label'        => __( 'Filter by Post Author', 'acf-frontend-form-element' ),
                'instructions' => '',
                'type'         => 'select',
                'name'         => 'post_author',
                'choices'      => $user_choices,
                'multiple'     => 1,
                'ui'           => 1,
                'allow_null'   => 1,
                'placeholder'  => __( "All Users", 'acf-frontend-form-element' ),
            ) );
            acf_render_field_setting( $field, array(
                'label'        => __( 'Add and Edit Posts' ),
                'instructions' => __( 'Allow posts to be created and edited whilst editing', 'acf-frontend-form-element' ),
                'name'         => 'add_edit_post',
                'type'         => 'true_false',
                'ui'           => 1,
            ) );
            acf_render_field_setting( $field, array(
                'label'         => __( 'Add Post Button' ),
                'name'          => 'add_post_button',
                'type'          => 'text',
                'default_value' => __( 'Add Post' ),
                'placeholder'   => __( 'Add Post' ),
                'conditions'    => [ [
                'field'    => 'add_edit_post',
                'operator' => '==',
                'value'    => '1',
            ] ],
            ) );
            acf_render_field_setting( $field, array(
                'label'         => __( 'Form Container Width' ),
                'name'          => 'form_width',
                'type'          => 'number',
                'prepend'       => 'px',
                'default_value' => 600,
                'placeholder'   => 600,
                'conditions'    => [ [
                'field'    => 'add_edit_post',
                'operator' => '==',
                'value'    => '1',
            ] ],
            ) );
        }
        
        public function load_relationship_field( $field )
        {
            if ( !isset( $field['add_edit_post'] ) ) {
                return $field;
            }
            if ( isset( $field['form_width'] ) ) {
                $field['wrapper']['data-form_width'] = $field['form_width'];
            }
            return $field;
        }
        
        public function edit_post_button(
            $title,
            $post,
            $field,
            $post_id
        )
        {
            if ( isset( $field['add_edit_post'] ) && $field['add_edit_post'] == 1 ) {
                $title .= '<a href="#" class="acf-icon -pencil small dark edit-rel-post" data-name="edit_item"></a>';
            }
            return $title;
        }
        
        public function add_post_button( $field )
        {
            
            if ( isset( $field['add_edit_post'] ) && $field['add_edit_post'] == 1 ) {
                $post_types = acf_get_pretty_post_types();
                $add_post_button = ( $field['add_post_button'] ? $field['add_post_button'] : __( 'Add Post', 'acf-frontend-form-element' ) );
                ?>
				<div class="margin-top-10 acf-actions">
					<a class="add-rel-post acf-button button button-primary" href="#" data-name="add_item"><?php 
                echo  $add_post_button ;
                ?></a>
				</div>
				
			<?php 
            }
        
        }
        
        public function relationship_query( $args, $field, $post_id )
        {
            if ( !isset( $field['post_author'] ) ) {
                return $args;
            }
            $post_author = acf_get_array( $field['post_author'] );
            
            if ( in_array( 'current_user', $post_author ) ) {
                $key = array_search( 'current_user', $post_author );
                $post_author[$key] = get_current_user_id();
            }
            
            $args['author__in'] = $post_author;
            return $args;
        }
        
        public function __construct()
        {
            add_filter( 'acf/load_field/type=relationship', [ $this, 'load_relationship_field' ] );
            add_action( 'acf/render_field_settings/type=relationship', [ $this, 'add_edit_field' ] );
            add_action( 'acf/render_field/type=relationship', [ $this, 'add_post_button' ], 10 );
            add_filter(
                'acf/fields/relationship/result',
                [ $this, 'edit_post_button' ],
                10,
                4
            );
            add_filter(
                'acf/fields/relationship/query',
                [ $this, 'relationship_query' ],
                10,
                3
            );
        }
    
    }
    new acf_frontend_relationship_field();
}
