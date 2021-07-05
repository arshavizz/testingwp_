<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(		
    array(
        'key' => 'redirect',
        'label' => 'Redirect After Submit',
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
        ),
        'choices' => array(
            'current' => 'Reload Current Page',
            'custom_url' => 'Custom URL',
            'referer' => 'Referer',
            'post_url' => 'Post URL',
        ),
        'allow_null' => 0,
        'multiple' => 0,
        'ui' => 0,
        'return_format' => 'value',
        'ajax' => 0,
        'placeholder' => '',
    ),
    array(
        'key' => 'custom_url',
        'label' => 'Custom Url',
        'type' => 'url',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'redirect',
                    'operator' => '==',
                    'value' => 'custom_url',
                ),
            ),
        ),
        'placeholder' => '',
    ),
    array(
        'key' => 'show_update_message',
        'label' => 'Success Message',
        'type' => 'true_false',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'message' => '',
        'ui' => 1,
        'ui_on_text' => '',
        'ui_off_text' => '',
    ),
    array(
        'key' => 'update_message',
        'label' => '',
        'field_label_hide' => true,
        'type' => 'textarea',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'show_update_message',
                    'operator' => '==',
                    'value' => '1',
                ),
            ),
        ),
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '2',
        'new_lines' => '',
    ),
    array(
        'key' => 'error_message',
        'label' => '',
        'field_label_hide' => true,
        'type' => 'textarea',
        'instructions' => __( 'There shouldn\'t be any problems with the form submission, but if there are, this is what your users will see. If you are expeiencing issues, try and changing your cache settings and reach out to ', 'acf-frontend-form-element' ) . 'support@frontendform.com',
        'required' => 0,
        'placeholder' => __( 'There has been an error. Form has been submitted successfully, but some actions might not have been completed.', 'acf-frontend-form-element' ),
        'maxlength' => '',
        'rows' => '2',
        'new_lines' => '',
    ),
);