<?php

class ACF_Frontend_Form_Oxygen extends OxyEl {

    var $js_added = false;

    function name() {
        return __('ACF Frontend Form');
    }

    function enableFullPresets() {
        return true;
    }

    function icon() {
        return CT_FW_URI.'/toolbar/UI/oxygen-icons/add-icons/pro-menu.svg';
    }

    function button_place() {
        return "acf::frontend";
    }

    function button_priority() {
        return 9;
    }

    function init() {

        //add_action("oxygen_default_classes_output", array( $this->El, "generate_defaults_css" ) );

        //add_filter("oxy_allowed_empty_options_list", array( $this, "allowedEmptyOptions") );
        //add_filter("oxygen_vsb_element_presets_defaults", array( $this, "presetsDefaults") );

    }

    function presetsDefaults($defaults) {

        $default_pro_menu_presets = array();
        
        $defaults = array_merge($defaults, $default_pro_menu_presets);

        return $defaults;
    }

    function afterInit() {
        $this->removeApplyParamsButton();
    }

    function allowedEmptyOptions($options) {

        $options_to_add = array(
            // TODO: autoprefix with 'oxy-pro-menu_' somehow?
            "oxy-pro-menu_mobile_menu_open_icon_text",
            "oxy-pro-menu_mobile_menu_close_icon_text",
            "oxy-pro-menu_mobile_menu_close_icon_text",
            "menu_dropdown_animation",
        );

        $options = array_merge($options, $options_to_add);

        return $options;
    }

    function customCSS($options, $selector) {

        // TODO: autoprefix with 'oxy-pro-menu_' somehow?
        // make it more API way?
        if (!isset($options["oxy-pro-menu_show_mobile_menu_below"]) || $options["oxy-pro-menu_show_mobile_menu_below"]=='never') {
            return;
        }

    
        $css = "";

        if ($options["oxy-pro-menu_show_mobile_menu_below"]!="always") {
            $max_width = oxygen_vsb_get_media_query_size($options["oxy-pro-menu_show_mobile_menu_below"]);
            $css .= "@media (max-width: {$max_width}px) {";
        }
        
        $css .= "$selector .oxy-pro-menu-mobile-open-icon {
                display: inline-flex;
            }
            $selector.oxy-pro-menu-open .oxy-pro-menu-mobile-open-icon {
                display: none;
            }
            $selector .oxy-pro-menu-container {
                visibility: hidden;
                position: fixed;
            }
            $selector.oxy-pro-menu-open .oxy-pro-menu-container {
                visibility: visible;
            }";
        
        if ($options["oxy-pro-menu_show_mobile_menu_below"]!="always") {
            $css .= "}";
        }

        if (isset($options["oxy-pro-menu_dropdown_icon_size"]) && $options["oxy-pro-menu_dropdown_icon_size"]!="") {
            $icon_size = intval($options["oxy-pro-menu_dropdown_icon_size"]);
        }
        else {
            // hardcode for testing, it is better to get this from $defaults
            $icon_size = 24;
        }

        if ($icon_size < 32) {
            $margin_right = intdiv (32-$icon_size, 2);
            $css .= "$selector .oxy-pro-menu-open-container .oxy-pro-menu-list .menu-item-has-children .oxy-pro-menu-dropdown-icon-click-area, 
                   $selector .oxy-pro-menu-off-canvas-container .oxy-pro-menu-list .menu-item-has-children .oxy-pro-menu-dropdown-icon-click-area {
                    margin-right: -{$margin_right}px;
            }";
        }

        return $css;
    }


    function animations_dropdown($option, $label) {

        // animation type control
        global $oxygen_vsb_aos;

        ob_start();?>
            
        <div class="oxygen-control-wrapper">
            <label class='oxygen-control-label'><?php echo $label; ?></label>
            <div class='oxygen-control'>
                <div class="oxygen-select oxygen-select-box-wrapper">
                    <div class="oxygen-select-box"
                        ng-class="{'oxygen-option-default':iframeScope.isInherited(iframeScope.component.active.id, '<?php echo $option; ?>')}">
                        <div class="oxygen-select-box-current">{{$parent.iframeScope.getOption('<?php echo $option; ?>')}}</div>
                        <div class="oxygen-select-box-dropdown"></div>
                    </div>
                    <div class="oxygen-select-box-options">
                        <div class="oxygen-select-box-option" 
                            ng-click="$parent.iframeScope.setOptionModel('<?php echo $option; ?>','')">&nbsp;</div>
                        <?php foreach ($oxygen_vsb_aos->animations_list as $name => $label) : ?>
                        <div class="oxygen-select-box-option" 
                            ng-click="$parent.iframeScope.setOptionModel('<?php echo $option; ?>','<?php echo $name; ?>')"><?php echo $label; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php 

        return ob_get_clean();
    }


    function controls() {

        // Menu list custom control. TODO: Do we need an easy API way of adding this type of control?

        $forms = get_posts( array( 'hide_empty' => false, 'post_type' => 'acf_frontend_form' ) ); 

        if( $forms ){ 
               
            // prepare a list of id:name pairs
            $forms_list = array(); 
            foreach ( $forms as $key => $form ) {
                $forms_list[$form->ID] = $form->post_title;
            } 
            $forms_list = json_encode( $forms_list );
            $forms_list = htmlspecialchars( $forms_list, ENT_QUOTES );

            ob_start(); ?>

                    <div class='oxygen-control-wrapper'>
                        <label class='oxygen-control-label'><?php _e("Form","acf-frontend-form-element"); ?></label>
                        <div class='oxygen-control'>
                            <div class="oxygen-select oxygen-select-box-wrapper">
                                <div class="oxygen-select-box">
                                    <div class="oxygen-select-box-current"
                                        ng-init="formsList=<?php echo $forms_list; ?>">{{formsList[iframeScope.getOption('form_id')]}}</div>
                                    <div class="oxygen-select-box-dropdown"></div>
                                </div>
                                <div class="oxygen-select-box-options">
                                    <?php foreach( $forms as $key => $form ) : ?>
                                    <div class="oxygen-select-box-option" 
                                        ng-click="iframeScope.setOptionModel('form_id','<?php echo $form->ID; ?>')"><?php echo $form->post_title; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php 

            $html = ob_get_clean();

            $this->addCustomControl($html, 'form_id')->rebuildElementOnChange();
        }
       

    }

    function render($options, $defaults, $content) {

        if ( $options['form_id'] == 0 ){
			echo __( 'Please Select a Form', 'acf-frontend-form-element' );
		}
		if ( get_post_type( $options['form_id'] ) == 'acf_frontend_form' ){
			acff()->form_display->render_form( $options['form_id'] );
		}
      
    }

   
}

new ACF_Frontend_Form_Oxygen();