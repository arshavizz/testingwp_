<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(		
    array(
        'key' => 'who_can_see',
        'label' => __( 'Who Can See This...', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'choices' => array(
            'logged_in'  => __( 'Only Logged In Users', 'acf-frontend-form-element' ),
            'logged_out' => __( 'Only Logged Out', 'acf-frontend-form-element' ),
            'all'        => __( 'All Users', 'acf-frontend-form-element' ),
        ),
    ),
    array(
        'key' => 'by_role',
        'label' => __( 'Select By Role', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'multiple' => 1,
        'ui' => 1,
        'choices' => acf_frontend_get_user_roles( array(), true ),
    ),
    array(
        'key' => 'user_id',
        'label' => __( 'Select By User', 'acf-frontend-form-element' ),
        'type' => 'user',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'allow_null' => 0,
        'multiple' => 1,
        'ajax' => 1,
        'ui' => 1,
        'return_format' => 'id',
    ), 
    array(
        'key' => 'dynamic',
        'label' => __( 'Dynamic Permissions', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'choices' => acf_frontend_user_id_fields(),
        'allow_null' => 1,
    ),
);
