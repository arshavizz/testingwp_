<?php
namespace ACFFrontend\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists( 'ACFFrontend\Classes\Display_Form' ) ) :

	class Display_Form {

		public function get_form_data( $form_args ){
			global $post;
			$active_user = wp_get_current_user();
			$object_id = false;
		
			if( isset( $_GET['updated'] ) && isset( $_GET['edit'] ) ){
				$object = explode( '_', $_GET['updated'] );
				if( isset( $object[2] ) ){
					if( is_numeric( $object[2] ) ){
						$object_id = $object[2];
					}else{
						$object_id = substr( $object[2], 1 );
					}
				}
			}
			if( isset( $_GET['post_id'] ) ){
				$object_id = $_GET['post_id'];
			}
			if( isset( $_GET['user_id'] ) ){
				$object_id = $_GET['user_id'];
			}
			if( isset( $_GET['product_id'] ) ){
				$object_id = $_GET['product_id'];
			}
		
		/* 		if( 'new_comment' == $form_args['main_action'] ){
				$form_args['post_id'] = 'new_comment';
				if( $form_args['comment_parent_post'] == 'current_post' ){
					$comment_parent_post = $post->ID;
				}else{
					$comment_parent_post = $form_args['select_parent_post'];
				}
				$form_args['html_after_fields'] .= '<input type="hidden" value="' . $comment_parent_post . '" name="acfef_parent_post"/><input type="hidden" value="0" name="acfef_parent_comment"/>';
			} */
		
			switch( $form_args['hidden_fields']['main_action'] ){
				case 'new_post':
					$form_args['action'] = 'post';
					$can_edit = acfef_can_edit_post( $object_id, $form_args );
					if( $can_edit && isset( $_GET['edit'] ) ){
						$form_args['post_id'] = $can_edit;
					}else{
						$form_args['post_id'] = 'add_post';
					}		
					if( ! empty( $form_args['new_post_terms'] ) ){
						if( $form_args['new_post_terms'] == 'select_terms' ){
							$form_args['post_terms'] = $form_args['new_terms_select'];
						}
						if( $form_args['new_post_terms'] == 'current_term' ){
							$form_args['post_terms'] = get_queried_object()->term_id;
						} 
					}	
					break;
				case 'edit_post':
				case 'duplicate_post':
					$form_args['action'] = 'post';
					if( $form_args['post_to_edit'] == 'current_post' ){
						global $post;
						if( $post )	$form_args['post_id'] = $post->ID;
					}elseif( $form_args['post_to_edit'] == 'select_post' ){
						$form_args['post_id'] = $form_args['post_select'];
					}elseif( $form_args['post_to_edit'] == 'url_query' && isset( $_GET[ $form_args['url_query_post'] ] ) ){
						$form_args['post_id'] = $_GET[ $form_args['url_query_post'] ];
						$form_args['url_query'] = $form_args['url_query_post'];
					}
					break;
				case 'new_product':
					$form_args['action'] = 'product';
					if( $object_id ){
						$form_args['post_id'] = acfef_can_edit_post( $object_id, $form_args );
					}else{
						$status = $form_args['new_product_status'] != 'no_change' ? $form_args['new_product_status'] : 'publish';
			
						$form_args['post_id'] = 'add_product';
					
						if( ! empty( $form_args['new_product_terms'] ) ){
							if( $form_args['new_product_terms'] == 'select_terms' ){
								$form_args['product_terms'] = $form_args['new_product_terms_select'];
							}
							if( $form_args['new_product_terms'] == 'current_term' ){
								$form_args['product_terms'] = get_queried_object()->term_id;
							} 
						}
					}
					break;
				case 'edit_product':
					$form_args['action'] = 'product';
					$first_product = get_posts( array( 'posts_per_page' => 1, 'post_type' => 'product' ) );
						if( $first_product ){
							$form_args['post_id'] = $first_product[0]->ID;
						}else{
							$form_args['post_id'] = 'none';
						}		
						if( $form_args['product_to_edit'] == 'select_product' && $form_args['product_select'] ){
							$form_args['post_id'] = $form_args['product_select'];
						}elseif( $form_args['product_to_edit'] == 'url_query' && isset( $_GET[ $form_args['url_query_product'] ] ) ){
							$form_args['post_id'] = $_GET[ $form_args['url_query_product'] ];
							$form_args['url_query'] = $form_args['url_query_product'];
						}elseif( get_post_type( $post ) == 'product' ){
							$form_args['post_id'] = $post->ID;
						}
					break;
				case 'new_user':
					if( $object_id ){
						$form_args['post_id'] = 'user_' . $object_id;
					}else{
						$can_edit = acfef_can_edit_user( $object_id, $form_args );
						if( ! $can_edit || $form_args['redirect_action'] == 'clear' ){
							$form_args['post_id'] = 'user_0';
						}else{
							$form_args['post_id'] = 'user_' . $can_edit;
						}
					}
					break;
				case 'edit_user':
					if( $form_args['user_to_edit'] == 'current_user' ){
						$form_args['post_id'] = 'user_' . $active_user->ID; 
					}elseif( $form_args['user_to_edit'] == 'current_author' ){
						if ( is_author() ){
							$author_id = get_queried_object_id();
						}else{
							$author_id = get_the_author_meta( 'ID' );
						}
						$form_args['post_id'] = 'user_' . $author_id; 
					}elseif( $form_args['user_to_edit'] == 'select_user' ){
						$form_args['post_id'] = 'user_' . $form_args['user_select'];
					}elseif( $form_args['user_to_edit'] == 'url_query' && isset( $_GET[ $form_args['url_query_user'] ] ) ){
						$form_args['post_id'] = 'user_' . $_GET[ $form_args['url_query_user'] ];
						$form_args['url_query'] = $form_args['url_query_user'];
					}
					break;
				case 'new_term':
					$form_args['post_id'] = 'add_term';		
					$form_args['hidden_fields']['taxonomy_type'] = $form_args['new_term_taxonomy'];	
					$form_args['action'] = 'term';
					break;
				case 'edit_term':
					if( $form_args['term_to_edit'] == 'select_term' ){
						$term_id = $form_args['term_select'];
					}elseif( $form_args['term_to_edit'] == 'url_query' && isset( $_GET[ $form_args['url_query_term'] ] ) ){
						$term_id = $_GET[ $form_args['url_query_term'] ];	
						$form_args['url_query'] = $form_args['url_query_term'];					
					}else{
						$term_obj = get_queried_object();
						$term_id = $term_obj->term_id;
					} 
					
					if( ! isset( $term_obj ) ) $term_obj = get_term( $term_id );

					$form_args['hidden_fields']['taxonomy_type'] = $term_obj->taxonomy;
					$form_args['post_id'] = 'term_' . $term_id;	
					$form_args['action'] = 'term';
					
					break;
			} 
		
			if( isset( $action ) ){
				$form_args[ $action . '_fields'] = $args;
			}
		
			return $form_args;
		}


		public function get_form( $key ) {		
			if( is_numeric( $key ) && get_post_type( $key ) == 'acf_frontend_form' ){
				$form = get_post( $key );
				return $this->get_form_args( $form );
			}
			
			$args = array(
				'post_type' => 'acf_frontend_form',
				'posts_per_page' => '1',
				'name' => $key,
				'post_status' => 'any',
			);
			
			$form = get_posts( $args );	
			
			if ( $form ) {
				return $this->get_form_args( $form[0] );
			}

			return array();
		}
		
		public function get_form_args( $form ){		
			// Get form object if $form is the ID
			if ( is_numeric( $form ) ) {
				$form = get_post( $form );
			}
			
			// Make sure we have a post and that it's a form
			if ( empty( $form ) || 'acf_frontend_form' != $form->post_type ) {
				return false;
			}
			
			$form_args = $form->post_content ? maybe_unserialize( $form->post_content ) : array();

			$form_key = str_replace( 'form_', '', $form->post_name );

			$form_args['hidden_fields'] = [
				'screen_id'   => $form->ID,
				'main_action' => $form_args['main_action'],
				'element_id' => $form_key,
			];
			
			unset( $form_args['main_action'] );

			$fields_args = array(
				'post_type' => 'acf-field',
				'posts_per_page' => '-1',
				'post_parent' => $form->ID,
				'orderby' => 'menu_order', 
				'order' => 'ASC'
			);
			$form_args['fields'] = array();
			foreach( get_posts( $fields_args ) as $field ){
				$form_args['fields'][] = $field->post_name;
			}
			
			return $form_args;
		}	

		public function get_redirect_url( $form ){
			$redirect_url = '';
			$form['message_location'] = 'other';
			switch( $form['redirect'] ) {
				case 'custom_url':
					if( is_array( $form['custom_url'] ) ){
						$redirect_url = $form['custom_url']['url'];
					}else{
						$redirect_url = $form['custom_url'];
					}
					unset( $form['custom_url'] );
					break;
				case 'current':
					global $wp;
					$redirect_url = home_url( $wp->request );
					//$form_args['reload_current'] = true;
					$form['message_location'] = 'current';
					break;
				case 'referer_url':
					$redirect_url = home_url( add_query_arg( NULL, NULL ) );
					if ( wp_get_referer() ) {
						$redirect_url = wp_get_referer();
					}
					break;
				case 'post_url':
					$redirect_url = '%post_url%';
					break;    
			}
			unset( $form['redirect'] );
			$form['return'] = $redirect_url;

			return $form;
		}

		public function validate_form( $form ) {		

			if( ! is_array( $form ) ){
				$form = $this->get_form( $form );
			}
				
			$form_class = empty( $form['form_attributes']['class'] ) ? 'acfef-form -submit' : 'acfef-form -submit ' . $form['form_attributes']['class'];

			// defaults
			$form = acf_frontend_parse_args( $form, array(
				'id'					=> 'acf-form',
				'parent_form'			=> '',
				'post_id'				=> false,
				'action'				=> '',
				'fields'				=> false,
				'form'					=> true,
				'form_title'    		=> '',
				'form_attributes'		=> array(
					'class'					=> $form_class,
					'action'				=> '',
					'method'				=> 'post',
					'autocomplete'          => 'disableacf',
					'novalidate'            => 'novalidate',
				),
				'saved_drafts'		    => array(),
				'saved_revisions'	    => array(),
				'save_progress'		    => array(),
				'show_delete_button'    => false,
				'message_location' 		=> 'other',
				'html_before_fields'	=> '',
				'hidden_fields'         => array(
					'main_action' 			=> '',
				),
				'html_after_fields'		=> '',
				'hidden_submit'			=> false,				
				'submit_value'			=> __("Update", 'acf'),
				'label_placement'		=> 'top',
				'instruction_placement'	=> 'label',
				'field_el'				=> 'div',
				'uploader'				=> 'wp',
				'honeypot'				=> true,
				'show_update_message'   => true,
				'update_message'		=> __("Post updated", 'acf'),
				'html_updated_message'	=> '<div class="acfef-message"><div class="acf-notice -success acf-success-message -dismiss"><p class="success-msg">%s</p><span class="acfef-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div></div>',		
				'error_message'			=> __( 'There has been an error. The form has been submitted successfully, but some actions might not have been completed.', 'acf-frontend-form-element' ),		
				'kses'					=> true,
				'pay_for_submission'	=> false,
				'new_post_type'			=> 'post',
				'new_post_status' 		=> 'publish',
				'redirect' 				=> 'current',
				'custom_url'			=> '',
			));

			if( empty( $form['return'] ) ) $form = $this->get_redirect_url( $form );

			if( ! $form['post_id'] ){
				$form = $this->get_form_data( $form );
			}

			// filter post_id
			$form['post_id'] = acf_get_valid_post_id( $form['post_id'] );   
			

			if ( $form['pay_for_submission'] ) {
				$user_submissions = get_user_meta( get_current_user_id(), 'acfef_payed_submissions', true );
				$user_submitted = get_user_meta( get_current_user_id(), 'acfef_payed_submitted', true );
				if ( !$user_submitted ) {
					$user_submitted = 0;
				}				
				if ( $user_submitted >= $user_submissions ) {
					$form['hidden_submit'] = true;
				}
			
			}

			// filter
			$form = apply_filters('acf/validate_form', $form);    
			
			// return
			return $form;
			
		}

		public function multi_step_buttons( $args, $current_step ){
			$prev_button = $buttons_class = '';
					
			$form_step = $args['fields']['steps'][ $current_step ];
			if( $current_step > 1 ){  
				if($form_step['prev_button_text'] ){
					$prev_step = $current_step-1;
					$prev_button = '<input type="hidden" name="prev_step_num" value="' . $prev_step . '"/>';
					$prev_button .= '<input type="button" name="prev_step" class="acfef-prev-button acfef-submit-button acf-button button button-primary" value="' . $form_step['prev_button_text'] . '"/> ';
					$buttons_class = 'acfef-multi-buttons-align';
				}
			}
			
			$next_button = '<input type="submit" class="acfef-submit-button acf-button button button-primary" data-state="publish" value="' . $form_step['next_button_text'] . '" />';
			
			$submit_button =  '<div class="acf-form-submit"><div class="acfef-submit-buttons ' . $buttons_class . '">' . $prev_button . $next_button . '</div></div>';
		
			return $submit_button;
		}

		public function step_tabs( $args, $current_step ){
			$editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
			$current_post = get_post();
			$active_user = wp_get_current_user();
			$screens = ['desktop', 'tablet', 'phone'];
			
			$tabs_responsive = '';	
			if( isset( $args['steps_tabs_display'] ) ){
				foreach( $screens as $screen ){
					if( ! in_array( $screen, $args['steps_tabs_display'] ) ){
						$tabs_responsive .= 'elementor-hidden-' . $screen . ' ';
					}
				}
			}
			
			$counter_responsive = '';
			if( isset( $args['steps_counter_display'] ) ){
				foreach( $screens as $screen => $label ){
					if( ! in_array( $screen, $args['steps_counter_display'] ) ){
						$counter_responsive .= 'elementor-hidden-' . $screen . ' ';
					}
				}
			}
		
			$total_steps = count( $args['fields']['steps'] );
			
			echo '<div class="acfef-tabs elementor-tabs"><div class="acfef-tabs-wrapper ' . $tabs_responsive . '">';
			$steps = $args['fields']['steps'];
			
			if( in_array( 'tabs', $args['steps_display'] ) ){
		
				foreach( $steps as $step_count => $form_step ){
		
					$active = '';
					if( $step_count == $current_step ){
						$active = 'active';
					}
		
					$change_form = '';
					if( $editor || $args['tab_links'] ){
						$change_form = ' change-step';
					}
						
					$step_title = $form_step['form_title'] ? $form_step['form_title'] : $args['form_title'];
					if( $form_step['step_tab_text'] ){
						$step_title = $form_step['step_tab_text'];
					}
					if( $step_title == '' ){
						$step_title = __( 'Step', 'acf-frontend-form-element' ) . ' ' . $step_count;
					}else{
						if( $args['step_number'] ){
							$step_title = $step_count . '. ' . $step_title;
						}
					}
		
					echo '<a class="form-tab ' . $active . $change_form. '" data-step="' .$step_count. '"><p class="step-name">' . $step_title . '</p></a>';
				}
				
			}
		
			echo '</div>';
			
			echo '<div class="form-steps elementor-tabs-content-wrapper">';
		
			if( in_array( 'counter', $args['steps_display'] ) ){
				echo '<div class="' . $counter_responsive . 'step-count"><p>' . $args['counter_prefix'] .  $current_step . '/' . $total_steps . $args['counter_suffix'] . '</p></div>';
			}	
		}


		public function form_set_data( $data = array() ) {
			
			// defaults
			$data = wp_parse_args($data, array(
				'screen'		=> 'post',	// Current screen loaded (post, user, taxonomy, etc)
				'post_id'		=> 0,		// ID of current post being edited
				'nonce'			=> '',		// nonce used for $_POST validation (defaults to screen)
				'validation'	=> 1,		// enables form validation
				'changed'		=> 0,		// used by revisions and unload to detect change
			));
			
			// crete nonce
			$data['nonce'] = wp_create_nonce($data['screen']);

			// return 
			return $data;
		}


		public function form_render_data( $data = array(), $error_message = false ) {
			
			// set form data
			$data = $this->form_set_data( $data );
			
			?>
			<div <?php if( $error_message ){ echo 'data-error="' . $error_message . '"'; } ?> class="acf-form-data acf-hidden">
				<?php 
				
				// loop
				foreach( $data as $name => $value ) {
					
					// input
					acf_hidden_input(array(
						'name'	=> '_acf_' . $name,
						'value'	=> $value
					));
				}
				
				// actions
				do_action('acf/form_data', $data);
				do_action('acf/input/form_data', $data);
				
				?>
			</div>
			<?php
		}

		public function render_field_wrap( $field, $element = 'div', $instruction = 'label' ) {

			// Ensure field is complete (adds all settings).
			if( function_exists( 'acf_validate_field' ) ) {
				$field = acf_validate_field( $field );
			}

			// Prepare field for input (modifies settings).
			if( function_exists( 'acf_prepare_field' ) ) {
				$field = acf_prepare_field( $field );
			}

			// Allow filters to cancel render.
			if( !$field ) {
				return;
			}
			
			// Determine wrapping element.
			$elements = array(
				'div'	=> 'div',
				'tr'	=> 'td',
				'td'	=> 'div',
				'ul'	=> 'li',
				'ol'	=> 'li',
				'dl'	=> 'dt',
			);
			
			if( isset($elements[$element]) ) {
				$inner_element = $elements[$element];
			} else {
				$element = $inner_element = 'div';
			}
				
			// Generate wrapper attributes.
			$wrapper = array(
				'id'		=> '',
				'class'		=> 'acf-field',
				'width'		=> '',
				'style'		=> '',
				'data-name'	=> $field['_name'],
				'data-type'	=> $field['type'],
				'data-key'	=> $field['key'],
			);
			
			// Add field type attributes.
			$wrapper['class'] .= " acf-field-{$field['type']}";
			
			// add field key attributes
			if( $field['key'] ) {
				$wrapper['class'] .= " acf-field-{$field['key']}";
			}
			
			// Add required attributes.
			// Todo: Remove data-required
			if( $field['required'] ) {
				$wrapper['class'] .= ' is-required';
				$wrapper['data-required'] = 1;
			}
			
			// Clean up class attribute.
			$wrapper['class'] = str_replace( '_', '-', $wrapper['class'] );
			$wrapper['class'] = str_replace( 'field-field-', 'field-', $wrapper['class'] );
			
			// Merge in field 'wrapper' setting without destroying class and style.
			if( $field['wrapper'] ) {
				$wrapper = acf_merge_attributes( $wrapper, $field['wrapper'] );
			}
			
			// Extract wrapper width and generate style.
			// Todo: Move from $wrapper out into $field.
			$width = acf_extract_var( $wrapper, 'width' );
			if( $width ) {
				$width = acf_numval( $width );
				if( $element !== 'tr' && $element !== 'td' ) {
					$wrapper['data-width'] = $width;
					$wrapper['style'] .= " width:{$width}%;";
				}
			}
			
			// Clean up all attributes.
			$wrapper = array_map( 'trim', $wrapper );
			$wrapper = array_filter( $wrapper );
			
			/**
			 * Filters the $wrapper array before rendering.
			 *
			 * @date	21/1/19
			 * @since	5.7.10
			 *
			 * @param	array $wrapper The wrapper attributes array.
			 * @param	array $field The field array.
			 */
			$wrapper = apply_filters( 'acf/field_wrapper_attributes', $wrapper, $field );
			
			// Append conditional logic attributes.
			if( !empty($field['conditional_logic']) ) {
				$wrapper['data-conditions'] = $field['conditional_logic'];
			}
			if( !empty($field['conditions']) ) {
				$wrapper['data-conditions'] = $field['conditions'];
			}
			
			// Vars for render.
			$attributes_html = acf_esc_attr( $wrapper );
			
			// Render HTML
			echo "<$element $attributes_html>" . "\n";
				if( $element !== 'td' && ( ! isset( $field['field_label_hide'] ) || ! $field['field_label_hide'] ) ) {
					echo "<$inner_element class=\"acf-label\">" . "\n";
						acf_render_field_label( $field );
					echo "</$inner_element>" . "\n";
				}
		
				echo "<$inner_element class=\"acf-input\">" . "\n";
					if( $instruction == 'label' ) {
						acf_render_field_instructions( $field );
					}
					acf_render_field( $field );
					if( $instruction == 'field' ) {
						acf_render_field_instructions( $field );
					}
				echo "</$inner_element>" . "\n";
			echo "</$element>" . "\n";
		}

		public function render_previous( $steps, $post_id = 0 ) {
			if( isset( $steps ) ){
				foreach( $steps as $index => $step ) { ?>
					<div class="acf-step-fields step-<?php echo $index+1 ?>" data-step="<?php echo $index+1 ?>">
					<?php
						foreach( $step['fields'] as $field_data ) {
							if( isset( $field_data['acf'] ) ){
								$field = acf_maybe_get_field( $field_data['acf'], $post_id, false );
								if( ! $field ) continue;
								$field['required'] = 0;
								if( isset( $field['wrapper']['class'] ) ){
									$field['wrapper']['class'] .= ' acfef-hidden';
								}else{
									$field['wrapper']['class'] = 'acfef-hidden';
								}
		
								if( !isset( $field['value'] ) || $field['value'] === null ) {
									$field['value'] = acf_get_value( $post_id, $field );
								} 

								$field['prefix'] = 'step_' .$index;
								
								// Render wrap.
								$this->render_field_wrap( $field );
							}
						}
					?>
					</div>
					<?php
				}
			}
		}
		
		public function render_fields( $fields, $post_id = 0, $el = 'div', $instruction = 'label' ) {
			
			// Parameter order changed in ACF 5.6.9.
			if( is_array($post_id) ) {
				$args = func_get_args();
				$fields = $args[1];
				$post_id = $args[0];
			}
			
			// Filter our false results.
			$fields = array_filter( $fields );
			
			/**
			 * Filters the $fields array before they are rendered.
			 *
			 * @date	12/02/2014
			 * @since	5.0.0
			 *
			 * @param	array $fields An array of fields.
			 * @param	(int|string) $post_id The post ID to load values from.
			 */
			$fields = apply_filters( 'acf/pre_render_fields', $fields, $post_id );
			
			// Loop over and render fields.
			if( $fields ) {
				$open_columns = 0;
				foreach( $fields as $field ) {
					if( isset( $field['render_content'] ) ){
						echo $field['render_content'];
					}elseif( isset( $field['column'] ) ){
						if( $field['column'] == 'endpoint' ){
							if( $open_columns ) echo '</div>';
							$open_columns--;
						}else{
							if( isset( $field['nested'] ) ){
								$open_columns++;
							}else{
								if( $open_columns ) echo '</div>';
								$open_columns++;
							}    
							echo '<div class="acf-column elementor-repeater-item-' .$field['column']. '">';
						}
					}else{
						// Load value if not already loaded.
						if( !isset( $field['value'] ) || $field['value'] === null ) {
							$field['value'] = acf_get_value( $post_id, $field );
						} 
						global $field_submit_button;
					
						if( $field['type'] == 'submit_button' ) $field_submit_button = true;
						// Render wrap.
						$this->render_field_wrap( $field, $el, $instruction );
					}
				}
				if( $open_columns > 0 ){
					while( $open_columns > 0 ){
						echo '</div>';
						$open_columns--;
					}
				}
			}

			if( ! empty( $open_accordion ) ){
				echo '</div></div></div>';
			}
			
			/**
			*  Fires after fields have been rendered.
			*
			*  @date	12/02/2014
			*  @since	5.0.0
			*
			* @param	array $fields An array of fields.
			* @param	(int|string) $post_id The post ID to load values from.
			*/
			do_action( 'acf/render_fields', $fields, $post_id );
		}

		public function delete_button( $args ){
			$confirm_message = $args['delete_message'];
			$delete_button_icon = $args['delete_icon']['value'];
			$delete_button_text = $args['delete_text'];

			acf_enqueue_scripts();
			?> 
			<form class="acfef-form -delete" action="" method="POST" >

			<?php
			$delete_args = array( 
				'screen'	=> 'acf_delete',
				'form'		=> acf_encrypt(json_encode( $args )),
				'redirect'  => ! empty( $args['delete_redirect'] ) ? $args['delete_redirect']: $args['redirect'],
			);
			if( isset( $args['post_id'] ) ){
				$delete_args[ 'delete_post' ] = $args['post_id'];
			}
			if( isset( $args['term_id'] ) ){
				$delete_args[ 'delete_term' ] = $args['term_id'];
			}
			if( isset( $args['user_id'] ) ){
				$delete_args[ 'delete_user' ] = $args['user_id'];
			}

			$this->form_render_data( array_merge( $delete_args, $args['hidden_fields'] ) );
			?>
			<div class="acfef-delete-button-container">
			<button onclick="return confirm('<?php echo $confirm_message; ?>')" type="submit" class="button acfef-delete-button">
			<?php if ( $delete_button_icon ) { ?>
				<i class="<?php echo $delete_button_icon; ?>"></i>
			<?php } ?>
			<?php echo $delete_button_text; ?> </button>
				</div>
			</form>
			<?php
		}

		public function saved_drafts( $args ){
			$wg_id = $args['hidden_fields']['element_id'];
			$drafts_args = array(
				'posts_per_page' => -1,
				'post_status' => 'draft',	
				'post_type' => $args['new_post_type'],
				'author' => get_current_user_id(),
			);
			$form_submits = get_posts( $drafts_args );
			if( ! $form_submits ) return;

			?>
			<div class="acfef-form-posts"><p class="drafts-heading"><?php echo $args['saved_drafts']['saved_drafts_label']; ?></p>
			
			<?php    
			$draft_choices = ['add_post' => $args['saved_drafts']['saved_drafts_new'] ];
		
			if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
				for( $x = 1; $x < 4; $x++ ){
					$draft_choices[$x] = 'Draft ' . $x . ' (' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) . ')';
				}
				$select_class = 'preview-form-drafts';
			}else{
				foreach( $form_submits as $submit ){
					$post_time = get_the_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $submit->ID);
					$draft_choices[$submit->ID] = $submit->post_title . ' (' . $post_time . ')';
				}
				$select_class = 'posts-select';
			}  
			acf_select_input( array( 'choices' => $draft_choices, 'class' => $select_class, 'value' => $args['post_id'] ) ); 
		
			?>
			</div>
			<?php
		
		}

		public function saved_revisions( $args ){
			$wg_id = $args['hidden_fields']['element_id'];

			if( get_post_type( $args['post_id'] ) == 'revision' ){
				$parent_post = wp_get_post_parent_id( $args['post_id'] ); 
			}else{
				$parent_post = $args['post_id'];
			}

			$form_submits = wp_get_post_revisions( $parent_post );
			if( ! $form_submits ) return;
			?>
			<br><div class="acfef-form-posts"><p class="revisions-heading"><?php echo $args['saved_revisions']['saved_revisions_label']; ?></p>
			
			<?php    
			$revision_choices = [$parent_post => $args['saved_revisions']['saved_revisions_edit_main'] ];
		
			if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
				for( $x = 1; $x < 4; $x++ ){
					$revision_choices[$x] = 'Revision ' . $x . ' (' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) . ')';
				}
				$select_class = 'preview-form-revisions';
			}else{
				$first = true;
				if( is_array( $form_submits ) && count( $form_submits ) > 1 ){
					foreach( $form_submits as $index => $submit ){
						if( $first ){
							$first = false;
							continue;
						} 
						$post_time = get_the_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $index);
						$revision_choices[$index] = $submit->post_title . ' (' . $post_time . ')';
					}
					$select_class = 'posts-select';
				}
			}  
			acf_select_input( array( 'choices' => $revision_choices, 'class' => $select_class, 'value' => $args['post_id'] ) ); 
		
			?>
			</div>
			<?php
		
		}

		public function render_form( $form, $preview = false ) {
			$form = $this->validate_form( $form );

			if( isset( $form['who_can_see'] ) ){
				$form = apply_filters( 'acfef/show_form', $form );  
				if( empty( $form['display'] ) && ! $preview ){
					return;
				}      
			}

			acf_enqueue_scripts();
			acf_enqueue_uploader();

			$form['form_attributes']['id'] = $form['id'];

			$GLOBALS['acfef_form'] = $form;

			?>
			<form <?php echo acf_frontend_esc_attrs( $form['form_attributes'] ) ?>> 
			<?php
				
			$current_fields = $form['fields'];    
		
			if( isset( $form['fields']['steps'] ) ){
				$current_step = 1;
		
				if( isset( $form['step_index'] ) ){
					$current_step = $form['step_index'];
				}else{
					$form['step_index'] = $current_step;
				}

				$form['hidden_fields']['step'] = $current_step;
		
				if( $current_step == count( $form['fields']['steps'] ) ) $form['last_step'] = true;
				$current_fields = $form['fields']['steps'][ $current_step ]['fields'];
				if( $current_step > 1 ) $previous_steps = array_slice( $form['fields']['steps'], 0, $current_step-1 );
				$form_title = $form['fields']['steps'][ $current_step ]['form_title'];
		
				$submit_button = $this->multi_step_buttons( $form, $current_step );
				$this->step_tabs($form, $current_step);
			}else{
				if( $form['hidden_submit'] ){
					$hidden_submit = ' acf-hidden';
					$disabled_submit = ' disabled ';
				}else{
					$hidden_submit = '';
					$disabled_submit = ' ';
				}
				$submit_button = '<div class="acfef-submit-buttons' .$hidden_submit. '"><input'. $disabled_submit.'type="submit" class="acfef-submit-button acf-button button button-primary" data-state="publish" value="' .$form['submit_value']. '" /></div>';
				$current_fields = $form['fields'];
			} 
			if( ! isset( $form_title ) || ! $form_title ){
				$form_title = $form['form_title'];
			}
		
			if( $form_title ){
				echo '<h2 class="acfef-form-title">' . $form_title . '</h2>';
			}
		
			$post_id = $form['post_id'];
		
			if( $post_id === 'add_post' || $post_id === 'add_product' ) {
				$post_id = false;
			}
			
			// Set uploader type.
			acf_update_setting( 'uploader', $form['uploader'] );
			
			$fields = array();
		
			if( $current_fields ){
				foreach( $current_fields as $field_data ) {
					if( is_array( $field_data ) ){
						if( isset( $field_data['acf'] ) ){
							$field = acf_maybe_get_field( $field_data['acf'], $post_id, false );
							if( ! $field ) continue;
							if( isset( $field_data['class'] ) ){
								if( isset( $field['wrapper']['class'] ) ){
									$field['wrapper']['class'] .= ' ' .$field_data['class'];
								}else{
									$field['wrapper']['class'] = $field_data['class'];
								}
							}
							$fields[] = $field;
							if( $field_data['type'] == 'product_type' ) $form['product_types_field'] = true;
							if( $field_data['type'] == 'manage_stock' ) $form['product_manage_stock'] = true;
						}else{
							$fields[] = $field_data;
						}
					}else{
						$field_data = acf_maybe_get_field( $field_data, $post_id, false );
						if( $field_data ){
						if( $field_data['type'] == 'submit_button' ) $form['submit_button_field'] = true;
						$fields[] = $field_data;
						}
					}
				}
			}else{
				return;
			}
		
			$hidden_defaults = $this->hidden_default_fields( $form );

			if( $form['show_update_message'] && $form['message_location'] == 'current' ){
				if ( isset( $_GET['updated'] ) && $_GET['updated'] !== 'true' ){
					$form_id = explode( '_', $_GET['updated'] );
					$widget_id = $form_id[0];
					$page_id = $widget_page = $form_id[1];
					if( isset( $form_id[2] ) ) $page_id = $form_id[2];
		
					$update_message = $form['update_message'];
					if( strpos( $update_message, '[' ) !== 'false' ){
						$update_message = acff()->dynamic_values->get_dynamic_values( $update_message, $page_id );
					}  
					printf( $form['html_updated_message'], $update_message );
				}    
			}
		
				$this->form_render_data( array_merge( array( 
					'screen'	=> 'acfef_form',
					'status'    => '',
					'post_id'	=> $form['post_id'],
					'form'		=> acf_encrypt(json_encode( $form ))
				), $form['hidden_fields'] ), $form['error_message'] );
			?>
			<?php if( isset( $form['template_id'] ) ){
				echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $form['template_id'] );
			}else{ ?>
			<div class="acf-fields acf-form-fields -<?php echo esc_attr($form['label_placement'])?>">
				<?php if( isset( $current_step ) ){ 
					if( isset( $previous_steps ) ){
						$this->render_previous( $previous_steps, $post_id );
					} ?>
					<div class="acf-step-fields step-<?php echo $current_step ?>" data-step="<?php echo $current_step ?>">
				<?php }        
				$this->render_fields( $fields, $post_id, $form['field_el'], $form['instruction_placement'] ); ?>
				<?php if( isset( $current_step ) ){ ?>
					</div>
				<?php }        
				$this->render_fields( $hidden_defaults, $post_id );
				
				echo $form['html_after_fields']; ?>
			</div>
			<?php global $field_submit_button;
			if( empty( $form['submit_button_field'] ) && ! $field_submit_button ){ ?>
			<div class="acf-form-submit">
				<?php echo $submit_button; ?>
			</div>
			<?php $field_submit_button = 0; } }
			if( isset( $form['fields']['steps'] ) ){
				echo '</div></div>';
			} 
		
			if( $form['save_progress'] ){ 
				$state = $form['post_id'] == 'add_post' ? 'draft' : 'revision';
				?>
				<div class="save-progress-buttons">
				<?php if ( $form['save_progress']['desc'] ) { ?>
					<p class="description"><span class="btn-dsc"><?php echo $form['save_progress']['desc']; ?></span></p>
				<?php } ?>
				<input formnovalidate type="submit" class="save-progress-button acf-submit-button acf-button button" value="<?php echo $form['save_progress']['text']; ?>" name="save_progress" data-state="<?php echo $state ?>" /></div>
			<?php	
			}
			?>
			</form>
			<?php
			
			do_action( 'acf_frontend/after_form', $form );
			
			if( $form['saved_drafts'] ){
				$this->saved_drafts( $form );
			}
			if( $form['saved_revisions'] ){
				$this->saved_revisions( $form );
			}

		}

		public function hidden_default_fields( $form ){
			acf_add_local_field( array(
				'prefix'	=> 'acf',
				'name'		=> '_validate_email',
				'key'		=> '_validate_email',
				'type'		=> 'text',
				'value'		=> '',
				'no_save'   => 1,
				'wrapper'	=> array( 'style' => 'display:none !important' )
			) );
			$fields	= array( acf_get_field('_validate_email') );

			if( ! acf_frontend_edit_mode() && $form['action'] == 'product' ){
				$wg_id = $form['hidden_fields']['element_id'];
				if( empty( $form['product_types_field'] ) ){
					acf_add_local_field( array(
						'prefix'	=> 'acf',
						'name'		=> $wg_id. '_product_type',
						'key'		=> 'acfef_' .$wg_id. '_product_type',
						'type'		=> 'product_types',
						'wrapper'	=> array( 'style' => 'display:none !important' )
					) );
					$fields[] = acf_get_field( $wg_id. '_product_type' );
				}
				if( empty( $form['product_manage_stock'] ) ){
					acf_add_local_field( array(
						'prefix'	=> 'acf',
						'name'		=> $wg_id. '_manage_stock',
						'key'		=> 'acfef_' .$wg_id. '_manage_stock',
						'custom_manage_stock' => 1,
						'type'		=> 'true_false',
						'ui'		=> 0,
						'wrapper'	=> array( 'style' => 'display:none !important' )
					) );
					$fields[] = acf_get_field( $wg_id. '_manage_stock' );
				}
			}

			return $fields;
		}

		public function change_form() {
			if( !isset($_POST['form_data']) ) {
				return false;
			}
		
			$form = json_decode( acf_decrypt($_POST['form_data']), true );
			if( !$form ) {
				return false;
			}
			if( isset( $_POST['draft'] ) ){
				$form['post_id'] = $_POST['draft'];
				if( is_numeric( $_POST['draft'] ) ){
					$form['hidden_fields']['main_action'] = 'edit_post';
				}else{
					$form['hidden_fields']['main_action'] = 'new_post';
				}
			}else{
				if( isset( $_POST['step'] ) ){
					$form['step_index'] = $_POST['step'];
				}else{
					$form['step_index'] = $form['step_index']-1;	
				}
				if( $form['step_index'] == count( $form['fields']['steps'] ) ){
					$form['last_step'] = true;
				}else{
					if( isset( $form['last_step'] ) ) unset( $form['last_step'] );
				}
			}
			$GLOBALS['acfef_form'] = $form;

			ob_start();
			$this->render_form( $form );
			$reload_form = ob_get_contents();
			ob_end_clean();
		
			wp_send_json_success( ['reload_form' => $reload_form, 'to_top' => true
			] );
			die;	
		}

		public function get_steps( $field ){
			if( $field['field_type'] == 'step' ){
				return true;
			}
			return false;
		}

		public function ajax_add_form(){
			// vars
			$args = wp_parse_args($_POST, array(
				'nonce'				=> '',
				'field_key'			=> '',
				'parent_form'		=> '',
				'form_action'		=> '',
			));

			// verify nonce
			if( !acf_verify_ajax() ) {
				die();
			}		

			// load field
			$field = acf_get_field( $args['field_key'] );
			if( !$field ) {
				die();
			}

			$edit_post = is_numeric( $args['form_action'] );

			$hidden_fields = [ 
				'field_id' => $args['field_key'],
				'main_action' => $edit_post ? 'edit_post' : 'new_post',
			];		
			if( is_admin() ) $hidden_fields['screen_id'] = 'admin';
			$form_id = 'acf-form-' . $args['field_key'];
			
			$form_args = array( 'post_id' => $args['form_action'], 'post_fields' => ['post_status' => 'publish'], 'id' => $form_id, 'fields' => ['acfef_title'], 'form_attributes' => array( 'data-field' => $args['field_key'] ), 'ajax_submit' => true, 'hidden_fields' => $hidden_fields, 'redirect_action' => 'clear_form', 'return' => '', 'parent_form' => $args['parent_form'], 'action' => 'post', 'new_post_status' => 'publish' );

			if( ! empty( $field['post_form_template'] ) && $field['post_form_template'] != 'none' ){
				$form_args['template_id'] = $field['post_form_template'];
			}else{
				if( is_numeric( $args['form_action'] ) ){
					$form_args['update_message'] = __( 'Post Updated Successfully!', 'acf-frontend-form-element' );
					$form_args['submit_value'] = __( 'Update', 'acf-frontend-form-element' );
				}else{
					$form_args['update_message'] = __( 'Post Added Successfully!', 'acf-frontend-form-element' );
					$form_args['submit_value'] = __( 'Publish', 'acf-frontend-form-element' );

					$form_args['post_fields'] = ['post_status' => 'publish'];
				}
			}

			$all_post_types = acf_get_pretty_post_types();

			if( ! $edit_post ){
				switch( $args['form_action'] ){
					case 'add_post':
						if( empty( $field['post_type'] ) ){
							$form_args['new_post_type'] = 'post';
							$post_type_choices = $all_post_types;
						}elseif( count( $field['post_type'] ) > 1 ){
							$form_args['new_post_type'] = $field['post_type'][0];
							$post_type_choices = [];
		
							foreach( $field['post_type'] as $post_type ){
								$post_type_choices[ $post_type ] = $all_post_types[ $post_type ];
							}
						}else{
							$form_args['new_post_type'] = $field['post_type'][0];
						}
		
						if( isset( $post_type_choices ) ){
							acf_add_local_field(
								array(
									'key' => 'acfef_post_type',
									'label' => __( 'Post Type', 'acf-frontend-form-element' ),
									'default_value' => current( $field['post_type'] ),
									'name' => 'acfef_post_type',
									'type' => 'post_type',
									'layout' => 'vertical',
									'choices' => $post_type_choices,
								)
							);	
							$form_args['fields'][] = 'acfef_post_type';
							
						}
					break;
				}
				
			}
			$this->render_form( $form_args );
			die;	
		}
		
		/**
		 * Registers the shortcode advanced_form which renders the form specified by the "form" attribute
		 *
		 * @since 1.0.0
		 *
		 */
		public function form_shortcode( $atts ) {
		
			if ( isset( $atts['form'] ) ) {
				
				$form_id = $atts['form'];
				unset( $atts['form'] );
				
				ob_start();
				
				$this->render_form( $form_id );
				
				$output = ob_get_clean();
				
				return $output;
				
			}
		
		}

		public function __construct(){
			add_shortcode( 'acf_frontend', array( $this, 'form_shortcode' ) );

			add_action( 'wp_ajax_acfef/forms/change_form', array( $this, 'change_form' ) );
			add_action( 'wp_ajax_nopriv_acfef/forms/change_form', array( $this, 'change_form'  ) );		

			add_action( 'wp_ajax_acf_frontend/forms/add_form', array( $this, 'ajax_add_form' ) );	
			add_action( 'wp_ajax_nopriv_acf_frontend/forms/add_form', array( $this, 'ajax_add_form' ) );
		}
	}

	acff()->form_display = new Display_Form();

endif;	


	