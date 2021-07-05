(function($) {
	
		/**
	 * Insert text in input at cursor position
	 *
	 * Reference: http://stackoverflow.com/questions/1064089/inserting-a-text-where-cursor-is-using-javascript-jquery
	 *
	 */
		function insert_at_caret(input, text) {
		var txtarea = input;
		if (!txtarea) { return; }
		
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		} else if (br == "ff") {
			strPos = txtarea.selectionStart;
		}
		
		var front = (txtarea.value).substring(0, strPos);
		var back = (txtarea.value).substring(strPos, txtarea.value.length);
		txtarea.value = front + text + back;
		strPos = strPos + text.length;
		if (br == "ie") {
			txtarea.focus();
			var ieRange = document.selection.createRange();
			ieRange.moveStart ('character', -txtarea.value.length);
			ieRange.moveStart ('character', strPos);
			ieRange.moveEnd ('character', 0);
			ieRange.select();
		} else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		
		txtarea.scrollTop = scrollPos;
	}


	var dynamicValues = $('<div class="dynamic-values"></div>');
	$.each(acffdv, function (i, group) {
		var sub_div = $('<div class="group-options"><span class="group-name">'+group['label']+'</span></div>');
		$(sub_div).appendTo(dynamicValues);
		var sub_select = $('<select class="dynamic-value-select"><option value="" selected><span class="field-name">-- Select One --</span></option></select>');
		$.each(group['options'], function (j, l) {				
			var sub_option = $('<option class="field-option '+j+'-option" value="['+j+']"><span class="field-name">'+l+'</span></option>');
			
			$(sub_option).appendTo(sub_select);
		});
		$(sub_select).appendTo(sub_div);
    });
	
	
	$(document).ready(function() {

		var currentDynamicField = ''; 
		
		// Close dropdowns when clicking anywhere
		$(document).on( 'click', function(e) {
			if( e.target.id != currentDynamicField && $(e.target).parents('.acf-field').id != currentDynamicField ){
				$('.dynamic-values').remove();
			}
		}); 
		
		$(document).on( 'change', '.dynamic-values select', function(e) {
			
			e.stopPropagation();
			
			var $option = $(this);
			
			var value = $option.val();
			
			var $editor = $option.parents('.acf-field').first().find('.wp-editor-area');
			
			// Check if we should insert into WYSIWYG field or a regular field
			if ( $editor.length > 0 ) {
				
				// WYSIWYG field
				var editor = tinymce.editors[ $editor.attr('id') ];
				editor.editorCommands.execCommand( 'mceInsertContent', false, value );
				$('.dynamic-values').remove();
				$dvOpened = false;
				
			} else {
				
				// Regular field
				var $input = $option.parents('.dynamic-values').siblings('input[type=text]');
				insert_at_caret( $input.get(0), value );
				
			}

			$option.removeProp('selected').closest('select').val('');

			
		});
		
		// Toggle dropdown
		$(document).on( 'input click', '.acf-field[data-dynamic_values] input', function(e) {			
			e.stopPropagation();
			
			var $this = $( this );

				$('.dynamic-values').remove();
				dynamicValues.find('.all_fields-option').addClass('acf-hidden');
				$this.after(dynamicValues);
			
		});

		var $dvOpened;
		$(document).on( 'click', '.acf-field[data-dynamic_values] .dynamic-value-options', function(e) {			
			e.stopPropagation();
			
			var $this = $( this );

			$('.dynamic-values').remove();
			if( $dvOpened != true ){
				$dvOpened = true;
				dynamicValues.find('.all_fields-option').removeClass('acf-hidden');
				$this.after(dynamicValues);
			}else{
				$dvOpened = false;
			}
			
		});
		

	});
	
	
})(jQuery);