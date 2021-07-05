<?php

namespace ACFFrontend;

use ACFFrontend\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if( ! class_exists( 'ACFFrontend_Oxygen' ) ) :
    class ACFFrontend_Oxygen{
        public function global_settings_tab() {
  
            global $oxygen_toolbar;
            $oxygen_toolbar->settings_tab(__("ACF Frontend", "oxygen"), "acf_frontend", "panelsection-icons/styles.svg");
        }
        public function register_add_plus_section() {
            global $oxygen_toolbar;
            $oxygen_toolbar::oxygen_add_plus_accordion_section('acf_frontend',__("ACF Frontend"));
        }

        function register_add_plus_subsections() { ?>
        
            <?php do_action("oxygen_add_plus_acf_frontend"); ?>
        
        <?php }

        public function add_oxygen_elements() {		
            require_once( __DIR__ . "/elements/general/acf-frontend-form.php" );
        }


        public function __construct() {		
            //add_action('oxygen_vsb_global_styles_tabs', array($this, 'global_settings_tab'));

            add_action('oxygen_add_plus_acf_frontend_section_content', array($this, 'register_add_plus_subsections'));

            add_action('oxygen_add_plus_sections', array($this, 'register_add_plus_section'));
            
            require_once( __DIR__ . "/elements/general/acf-frontend-form.php" );
        }
    }

acff()->oxygen = new ACFFrontend_Oxygen();

endif;	