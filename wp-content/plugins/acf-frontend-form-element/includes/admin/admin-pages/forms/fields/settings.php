<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(		
    array(
        'key' => 'main_action',
        'label' => 'Type',
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'choices' => array(
            'edit_post' => 'Edit Post',
            'new_post' => 'New Post',
            //'new_user' => 'New User',
            //'edit_user' => 'Edit User',
        ),
        'default_value' => false,
        'allow_null' => 0,
        'multiple' => 0,
        'ui' => 0,
        'return_format' => 'value',
        'ajax' => 0,
        'placeholder' => '',
    ),	
    array(
        'key' => 'post_to_edit',
        'label' => 'Post to Edit',
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'main_action',
                    'operator' => '==',
                    'value' => 'edit_post',
                ),
            ),
        ),
        'choices' => array(
            'current' => 'Current Post',
            'url_query' => 'URL Query',
            'select_post' => 'Select Post',
        ),
        'default_value' => false,
        'allow_null' => 0,
        'multiple' => 0,
        'ui' => 0,
        'return_format' => 'value',
        'ajax' => 0,
        'placeholder' => '',
    ),
    array(
        'key' => 'url_query_post',
        'label' => 'URL Query Key',
        'type' => 'text',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'main_action',
                    'operator' => '==',
                    'value' => 'edit_post',
                ),
                array(
                    'field' => 'post_to_edit',
                    'operator' => '==',
                    'value' => 'url_query',
                ),
            ),
        ),
        'placeholder' => '',
    ),
    array(
        'key' => 'select_post',
        'label' => 'Select Post',
        'name' => 'select_post',
        'prefix' => 'form',
        'value' => $form['select_post'],
        'type' => 'post_object',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'main_action',
                    'operator' => '==',
                    'value' => 'edit_post',
                ),
                array(
                    'field' => 'post_to_edit',
                    'operator' => '==',
                    'value' => 'select_post',
                ),
            ),
        ),
        'post_type' => '',
        'taxonomy' => '',
        'allow_null' => 0,
        'multiple' => 0,
        'return_format' => 'object',
        'ui' => 1,
    ),

);