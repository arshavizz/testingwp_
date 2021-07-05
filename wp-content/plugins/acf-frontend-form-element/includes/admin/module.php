<?php

namespace ACFFrontend;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'ACFFrontend_Settings' ) ) {
    class ACFFrontend_Settings
    {
        private  $tabs = array() ;
        public function plugin_page()
        {
            global  $acff_settings ;
            $acff_settings = add_menu_page(
                'ACF Frontend',
                'ACF Frontend',
                'manage_options',
                'acfef-settings',
                [ $this, 'admin_settings_page' ],
                'dashicons-feedback',
                '87.87778'
            );
            add_submenu_page(
                'acfef-settings',
                __( 'Settings', 'acf-frontend-form-element' ),
                __( 'Settings', 'acf-frontend-form-element' ),
                'manage_options',
                'acfef-settings',
                '',
                0
            );
        }
        
        function admin_settings_page()
        {
            global  $acff_active_tab ;
            $acff_active_tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'welcome' );
            ?>

			<h2 class="nav-tab-wrapper">
			<?php 
            do_action( 'acfef_settings_tabs' );
            ?>
			</h2>
			<?php 
            do_action( 'acfef_settings_content' );
        }
        
        public function add_tabs()
        {
            add_action( 'acfef_settings_tabs', [ $this, 'settings_tabs' ], 1 );
            add_action( 'acfef_settings_content', [ $this, 'settings_render_options_page' ] );
        }
        
        public function settings_tabs()
        {
            global  $acff_active_tab ;
            foreach ( $this->tabs as $name => $label ) {
                ?>
				<a class="nav-tab <?php 
                echo  ( $acff_active_tab == $name || '' ? 'nav-tab-active' : '' ) ;
                ?>" href="<?php 
                echo  admin_url( '?page=acfef-settings&tab=' . $name ) ;
                ?>"><?php 
                _e( $label, 'acf-frontend-form-element' );
                ?> </a>
			<?php 
            }
        }
        
        public function settings_render_options_page()
        {
            global  $acff_active_tab ;
            
            if ( '' || 'welcome' == $acff_active_tab ) {
                ?>
			<style>p.acfef-text{font-size:20px}</style>
			<h3><?php 
                _e( 'Hello and welcome', 'acf-frontend-form-element' );
                ?></h3>
			<p class="acfef-text"><?php 
                _e( 'If this is your first time using ACF Frontend, we recommend you watch Paul Charlton from WPTuts beautifully explain how to use it.', 'acf-frontend-form-element' );
                ?></p>
			<iframe width="560" height="315" src="https://www.youtube.com/embed/iHx7krTqRN0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			<br>
			<p class="acfef-text"><?php 
                _e( 'If you have any questions at all please feel welcome to email shabti at', 'acf-frontend-form-element' );
                ?> <a href="mailto:shabti@frontendform.com">shabti@frontendform.com</a> <?php 
                _e( 'or on whatsapp', 'acf-frontend-form-element' );
                ?> <a href="https://api.whatsapp.com/send?phone=972584526441">+972-58-452-6441</a></p>
			<?php 
            } else {
                foreach ( $this->tabs as $form_tab => $label ) {
                    
                    if ( $form_tab == $acff_active_tab ) {
                        $hide_admin_fields = apply_filters( 'acfef/' . $acff_active_tab . '_fields', [] );
                        acff()->form_display->render_form( [
                            'post_id'        => 'acfef_options',
                            'hidden_fields'  => [
                            'admin_page' => $acff_active_tab,
                            'screen_id'  => 'options',
                        ],
                            'fields'         => $hide_admin_fields,
                            'submit_value'   => __( 'Save Settings', 'acf-frontend-form-element' ),
                            'update_message' => __( 'Settings Saved', 'acf-frontend-form-element' ),
                            'redirect'       => 'custom_url',
                            'custom_url'     => admin_url( '?page=acfef-settings&tab=' . $_GET['tab'] ),
                        ] );
                    }
                
                }
            }
        
        }
        
        public function configs()
        {
            
            if ( !get_option( 'acfef_hide_wp_dashboard' ) ) {
                add_option( 'acfef_hide_wp_dashboard', true );
                add_option( 'acfef_hide_by', array_map( 'strval', [
                    0 => 'user',
                ] ) );
            }
            
            require_once __DIR__ . '/admin-pages/custom-fields.php';
        }
        
        public function settings_sections()
        {
            require_once __DIR__ . '/admin-pages/local_avatar/settings.php';
            require_once __DIR__ . '/admin-pages/uploads_privacy/settings.php';
            require_once __DIR__ . '/admin-pages/hide_admin/settings.php';
            require_once __DIR__ . '/admin-pages/google/settings.php';
            require_once __DIR__ . '/admin-pages/forms/settings.php';
            do_action( 'acf_frontend/admin_pages' );
        }
        
        public function validate_save_post()
        {
            if ( isset( $_POST['_acf_post_id'] ) && $_POST['_acf_post_id'] == 'acfef_options' ) {
                
                if ( isset( $_POST['_acf_admin_page'] ) ) {
                    $page_slug = $_POST['_acf_admin_page'];
                    apply_filters( 'acfef/' . $page_slug . '_fields', [] );
                }
            
            }
        }
        
        public function scripts()
        {
            
            if ( acff()->dev_mode ) {
                $min = '';
            } else {
                $min = '-min';
            }
            
            wp_register_style(
                'acfef-modal',
                ACFEF_URL . 'includes/assets/css/acfef-modal-min.css',
                array(),
                ACFEF_ASSETS_VERSION
            );
            wp_register_style(
                'acfef',
                ACFEF_URL . 'includes/assets/css/acfef-min.css',
                array(),
                ACFEF_ASSETS_VERSION
            );
            wp_register_script(
                'acfef-modal',
                ACFEF_URL . 'includes/assets/js/acfef-modal.min.js',
                array( 'jquery' ),
                ACFEF_ASSETS_VERSION
            );
            wp_register_script(
                'acfef',
                ACFEF_URL . 'includes/assets/js/acfef' . $min . '.js',
                array( 'jquery', 'acf-input' ),
                ACFEF_ASSETS_VERSION,
                true
            );
            wp_register_script(
                'acfef-password-strength',
                ACFEF_URL . 'includes/assets/js/password-strength.min.js',
                array( 'password-strength-meter' ),
                ACFEF_ASSETS_VERSION,
                true
            );
        }
        
        public function __construct()
        {
            $this->tabs = array(
                'welcome'         => 'Welcome',
                'local_avatar'    => 'Local Avatar',
                'uploads_privacy' => 'Uploads Privacy',
                'hide_admin'      => 'Hide WP Dashboard',
                'google'          => 'Google APIs',
            );
            $this->tabs = apply_filters( 'acf_frontend/admin_tabs', $this->tabs );
            $this->settings_sections();
            add_action( 'wp_loaded', array( $this, 'scripts' ) );
            add_action( 'init', array( $this, 'configs' ) );
            add_action( 'admin_menu', array( $this, 'plugin_page' ), 15 );
            add_action( 'acf/validate_save_post', array( $this, 'validate_save_post' ) );
            $this->add_tabs();
        }
    
    }
    acff()->settings_tabs = new ACFFrontend_Settings();
}
