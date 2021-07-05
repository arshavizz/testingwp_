var modalLevel = 1;
var narrowfy = 0;
var $controls = [];
(function($){
	acf.addFilter('relationship_ajax_data', function(ajaxData, $el){
		if( $el.$control().data('product_id') != '' ){
			ajaxData.product_id = $el.$control().data('product_id');
		}
		return ajaxData;
	});

	$('body').on('input', 'input.image-preview', function(e){
	var reader = new FileReader();
	var file_input = $(this);
	reader.onload = function()
	{
	file_input.parents('.hide-if-value').addClass('acfef-hidden').siblings('.show-if-value').addClass('show').find('img').attr('src',reader.result);
	}
	imagePreview = true;
	reader.readAsDataURL(e.target.files[0]);
	});
	
	$('body').on('click','a[data-name=remove]',function(e){
		if( typeof imagePreview != undefined ){
			$(this).parents('.show-if-value').removeClass('show').siblings('.hide-if-value').removeClass('acfef-hidden').find('input.image-preview').val('');
		}
	});   
	   
	
	$(document).on('elementor/popup/show',(event, id, instance)=>{
		acf.doAction('append',$('#elementor-popup-modal-' + id))
	});
		
	$("body").on('input','.pa-custom-name input',function(a){
		$(this).closest('.acf-fields').siblings('.acf-fc-layout-handle').find('.attr_name').text($(this).val());
	});

	$("body").on('change','.posts-select',function(a){
		var $select =$(this); 
		var $form =$select.parents('.acfef-form-posts').siblings('form');
		$select.parents('.acfef-form-posts').append('<span class="acf-loading"></span>');
		var widget = $form.data('widget');
		var formData = $form.find('input[name=_acf_form]').val();

		var ajaxData = {
			action:		'acfef/forms/change_form',
			draft: 		$(this).val(),
			form_data:	formData,
		};
		// get HTML
		$.ajax({
			url: acf.get('ajaxurl'),
			data: acf.prepareForAjax(ajaxData),
			type: 'post',
			dataType: 'json',
			cache: false,
			success: function(response){
				if(response.data.reload_form){
					$('body, html').animate({scrollTop:$form.offset().top}, 'slow');
					$form.siblings('.acfef-form-posts').remove();
					$form.replaceWith(response.data.reload_form);
					acf.doAction('append',$('.elementor-element-'+widget))
					$('.acf-loading').remove();
				}
			}
		});
	});
	$("body").on('click','span.close-msg',function(a){
		$(this).parents('.acfef-message').remove();
	});
	
	$("body").on('input click','.acf-fields,.acf-form-submit',function(a){$('.acfef-message').remove()});
	
		$('body').on('click','a.add-rel-post',function(){
			$el = $(this).parents('.acf-field');
			
			container = acf.showModal($el.data('key'),$el.data('form_width')-narrowfy);
			$controls[modalLevel] = $(this).parents('.acf-actions').siblings('.acf-relationship');
			modalLevel++;
			narrowfy+=20;

			acf.getForm( $el, 'add_post' );     
	
		});

		$('body').on('mouseenter','.choices a.edit-rel-post', function(event){
			var item = $(this).parents('.acf-rel-item');
			if( ! item.hasClass('disabled') ){
				item.addClass('disabled temporary');
			}
		});
		$('body').on('mouseleave','.choices a.edit-rel-post', function(event){
			var item = $(this).parents('.acf-rel-item');
			if( item.hasClass('temporary') ){
				item.removeClass('disabled temporary');
			}
		});

		$('body').on('click','a.edit-rel-post', function(event){
				event.preventDefault();				
				$el = $(this).parents('.acf-field');				
				container = acf.showModal($el.data('key'),$el.data('form_width')-narrowfy);
				var post = $(this).parents('.acf-rel-item').data('id');
				$controls[modalLevel] = $(this).parents('.acf-actions').siblings('.acf-relationship');
				modalLevel++;
				narrowfy+=20;
		
				acf.getForm( $el, post );
		});
		
	
	$('.post-slug-field input').on('input keyup', function() {
		var c = this.selectionStart,
			r = /[`~!@#$%^&*()|+=?;:..’“'"<>,€£¥•،٫؟»«\s\{\}\[\]\\\/]+/gi,
			v = $(this).val();
		$(this).val(v.replace(r,'').toLowerCase());
		this.setSelectionRange(c, c);
	  }); 
	
	$('body').on('click', 'button.edit-password', function(){
		$(this).addClass('acfef-hidden').siblings('.pass-strength-result').removeClass('acfef-hidden').parents('.acf-field-user-password').removeClass('edit_password').addClass('editing_password').siblings('.acf-field-user-password-confirm').removeClass('acfef-hidden');
		$(this).after('<input type="hidden" name="edit_user_password" value="1"/>');
	});
	
	$('body').on('click', 'button.cancel-edit', function(){
		$(this).siblings('button.edit-password').removeClass('acfef-hidden').siblings('.pass-strength-result').addClass('acfef-hidden').parents('.acf-field-user-password').addClass('edit_password').removeClass('editing_password').siblings('.acf-field-user-password-confirm').addClass('acfef-hidden');
		$(this).parents('acf-input-wrap').siblings('acf-notice');
		$(this).siblings('input[name=edit_user_password]').remove();
	});

	$(function(){
		var forceEdit = $('.acf-field-user-password-confirm').siblings('.acf-field-user-password').hasClass('edit_password');

		if( ! forceEdit ){
			$('.acf-field-user-password-confirm').removeClass( 'acfef-hidden' );
		}
	});
	
	acf.showModal = function( $key, $width ){
		$key = $key+'-'+modalLevel;
		var margin = 9+modalLevel;
		var modal = $('#modal_'+$key);
		if(modal.length){
			modal.removeClass('hide').addClass('show');
		}else{
			modal = $('<div id="modal_' + $key + '" class="modal edit-modal show" data-clear="1"><div class="modal-content" style="margin:' + margin + '% auto;width:' + parseInt($width) + 'px;max-width:80%"><div class="modal-inner"><span class="acf-icon -cancel close"></span><div class="content-container"><div class="loading"><span class="acf-loading"></span></div></div></div></div></div>');
			$('body').append(modal);
		}
		return modal;
	}

	$('body').on('click','.modal .close', function(e){
		var modal = $(this).parents('.modal');
		if(modal.data('clear')==1){
			modal.remove();
			modalLevel--;
			narrowfy-=20;
		}
	});
	
	acf.getForm = function( $el, $form_action ){
		var ajaxData = {
			action:		'acf_frontend/forms/add_form',
			field_key:	$el.data('key'),
			parent_form: $el.parents('form').attr('id'),
			form_action: $form_action,
		};
		// get HTML
		$.ajax({
			url: acf.get('ajaxurl'),
			data: acf.prepareForAjax(ajaxData),
			type: 'post',
			dataType: 'html',
			success: acfFrontendShowForm
		});
	}
	
	function acfFrontendShowForm( html ){	
		
		// update popup
		container.find('.content-container').html(html);  
		acf.doAction('append',container);  
	};

	acf.add_filter('validation_complete', function( json, $form ){

		// check errors
		if( json.errors ) {
			$('.acf-loading').remove();
			$form.find('input[type=submit]').removeClass('disabled');
			var formModal = $form.closest('div.edit-modal');
			if( typeof formModal != 'undefined' ){
				$(formModal).animate({scrollTop:$form.offset().top-50}, 'slow');
			}
		}
	
		// return
		return json;        
	});
	$('body').on('click','form.acfef-form.-submit input[type=submit]',function(a){
		$form=$(this).parents('form');
		$form.find('input[name=_acf_status]').val($(this).data('state'));
		if(! $(this).hasClass('disabled')){
			$(this).after('<span class="acf-loading"></span>');
		}
	});

	$('body').on('submit','form.-delete', function (event) {
		event.preventDefault();
		$form = $(this);
		$form.find('button[type=submit]').addClass('disabled').after('<span class="acf-loading"></span>');

		var formData = new FormData($form[0]);          
		formData.append('action','acfef/delete_object');
		$.ajax({
			url: acf.get('ajaxurl'),
			type: 'post',
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function(response){
				if(response.success) {	   				  
					if( response.data.redirect ){
					  window.location=response.data.redirect;
					}
				}
			}
		});
	});

	$('body').on('submit','form.-submit', function (event) {
		event.preventDefault();
		$form = $(this);
		$form.find('input[type=submit]').addClass('disabled');
		args = {
			form: $form,
			reset: false,
			success: function ($form) {
				let $fileInputs = $('input[type="file"]:not([disabled])', $form)
				$fileInputs.each(function (i, input) {
					if (input.files.length > 0) {
						return;
					}
					$(input).prop('disabled', true);
				})
	
				var formData = new FormData($form[0]);          
				formData.append('action','acfef/form_submit');
	
				// Re-enable empty file $fileInputs
				$fileInputs.prop('disabled', false);
	
				acf.lockForm($form);
	
			   $.ajax({
				  url: acf.get('ajaxurl'),
				  type: 'post',
				  data: formData,
				  cache: false,
				  processData: false,
				  contentType: false,
				  success: function(response){
					if(response.success) {	   				  
					  if( response.data.redirect ){
						if(response.data.reload_current){						
							$.ajax({
							   type: 'post',
							   url: response.data.redirect,
							   success: function(reloaded) {  
								   document.documentElement.innerHTML = reloaded;
							   }
						   });
						}else{
							window.location=response.data.redirect;
						}
					  }else{
						acf.unlockForm($form);	
						successMessage='<div class="acfef-message"><div class="acf-notice -success acf-success-message"><p class="success-msg">'+response.data.update_message+'</p><span class="acfef-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div></div>';
						if(response.data.append){
						  //modal window ajax form
						  modalFormSuccess( response );	
						  $form.parents('.modal').remove();         
						}else{
						  if(response.data.clear_form){
							var widget=$form.data('widget');
							$form.siblings('.acfef-form-posts').remove();
							var formID = $form.attr('id');
							$form.replaceWith(response.data.clear_form);
							$form=$('#' + formID);
							acf.doAction('append',$form);
							if(response.data.saved_message){
								$form.find('.save-progress-button').after('<p class="draft-saved">'+response.data.saved_message+'</p>');
								setTimeout(function(){$('body').find('.draft-saved').remove();}, 3000);
							}
							if(response.data.update_message){
								$form.prepend(successMessage);
							}
							$('body, html').animate({scrollTop:$form.offset().top-50}, 'slow');
							var formModal = $form.closest('div.edit-modal');
							if( typeof formModal != 'undefined' ){
								$(formModal).animate({scrollTop:$form.offset().top-50}, 'slow');
							}
						  }
						} 
						$form.find('.acfef-submit-button').attr('disabled',false).removeClass('acf-hidden disabled');
					    $form.find('.acf-loading').remove();
					  }
					}else{
						$form.before('<div style="margin-top:10px" class="acfef-message"><div class="acf-notice -error acf-error-message"><p class="error-msg">'+$form.find('.acf-form-data').data('error')+'</p><span class="acfef-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div></div>');
						$form.remove();				
						$('body, html').animate({scrollTop:$form.offset().top-50}, 'slow');
						var formModal = $form.closest('div.edit-modal');
						if( typeof formModal != 'undefined' ){
							$(formModal).animate({scrollTop:$form.offset().top-50}, 'slow');
						}		
					}					
				  }, 
				});  
	
			}
		}
	
		acf.validateForm(args);
	});

	function modalFormSuccess( response ){
		
		var postData = response.data.append; 
		var field_key = response.data.field_key;
		modalLevel--;
		narrowfy-=20;        
		if(postData.action == 'edit'){
			$('.acf-field div.values').find('span[data-id='+postData.id+']').html(postData.text+'<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>');
			$('.acf-field div.choices').find('span[data-id='+postData.id+']').html(postData.text);
		}else{
			
			var thisField = $controls[modalLevel];	
			
			thisField.find('div.values ul').append('<li><input type="hidden" name="' + thisField.find('div.selection').siblings('input').attr('name') + '[]" value="' + postData.id + '" /><span data-id="' + postData.id + '" class="acf-rel-item">' + postData.text + '<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a></span></li>');

			thisField.find('div.choices ul').prepend('<li><span class="acf-rel-item disabled" data-id="' + postData.id + '">' + postData.text + '<a href="#" class="acf-icon -pencil small dark edit-rel-post" data-name="edit_item"></a></span></li>');
			
		}       
	}

	var dynamicValueFields = $('div[data-default]');
	$.each( dynamicValueFields, function( key, value ){
		var fieldElement = $(value);
		var fieldSources = fieldElement.data('default');
		var fieldDynamicValue = fieldElement.data('dynamic_value');
		var fieldInput = fieldElement.find('input[type=text]');
		if( fieldSources.length > 0 ){
			var inputValue = fieldDynamicValue;

			$.each( fieldSources, function( index, fieldName ){
				var fieldData = acf_frontend_get_field_data(fieldName);               
				var sourceInput = acfef_get_field_element(fieldData[0], false);
				inputValue = acfef_get_field_input_value(inputValue, fieldData, sourceInput); 
				var sourceInput = acfef_get_field_element(fieldData[0], true);  
				sourceInput.on('input', function(){
					var returnValue = fieldDynamicValue;
					$.each( fieldSources, function( index, fieldName ){
					var fieldData = acf_frontend_get_field_data(fieldName);               
					var sourceInput = acfef_get_field_element(fieldData[0], false);
					returnValue = acfef_get_field_input_value(returnValue, fieldData, sourceInput);
					});
					fieldInput.val(returnValue);
				});      
				
			});
			fieldInput.val(inputValue);
			
			function acfef_get_field_input_value(returnValue, fieldData, sourceInput){
				var shortcode = '['+fieldData[0]+']';
				if( sourceInput.val() != '' ){
				var display = sourceInput.val();
				if(fieldData[1] == 'text'){
					var display = acfef_get_field_text(fieldData[0]);
				}
				returnValue = returnValue.replace(shortcode, display);
				}
				return returnValue;
			}
	
			function acfef_get_field_element(fieldName, all){
				var sourceField = fieldElement.siblings('div[data-name=' + fieldName + ']');
				var sourceInput = sourceField.find('input');
				if(sourceField.data('type') == 'radio'){
					if(all == true){
						sourceInput = sourceField.find('input');
					}else{
						sourceInput = sourceField.find('input:selected');
					}
				}
				if(sourceField.data('type') == 'select'){
					sourceInput = sourceField.find('select');
				}    
				return sourceInput;
			}
			function acf_frontend_get_field_data(fieldName){
				var fieldData = [ fieldName, 'value' ];
				if (~fieldName.indexOf(':')){
				var fieldData = fieldName.split(':');
				}

				return fieldData;
			}
			function acfef_get_field_text(fieldName){
				var sourceField = fieldElement.siblings('div[data-name=' + fieldName + ']');
				if(sourceField.data('type') == 'radio'){
					sourceInput = sourceField.find('.selected').text();
				}
				if(sourceField.data('type') == 'select'){
					sourceInput = sourceField.find(':selected').text();
				}    
				return sourceInput;
			}
		}
	});

	var Field = acf.Field.extend({
		
		type: 'upload_images',
		
		events: {
			'click .acf-gallery-add':			'onClickAdd',
			'click div.acf-gallery-upload':		'onClickUpload',
			'click .acf-gallery-edit':			'onClickEdit',
			'click .acf-gallery-remove':		'onClickRemove',
			'click .acf-gallery-attachment': 	'onClickSelect',
			'click .acf-gallery-close': 		'onClickClose',
			'change .acf-gallery-sort': 		'onChangeSort',
			'click .acf-gallery-update': 		'onUpdate',
			'mouseover': 						'onHover',
			'showField': 						'render',
			'input .images-preview': 			'imagesPreview',
		},
		
		actions: {
			'validation_begin': 	'onValidationBegin',
			'validation_failure': 	'onValidationFailure',
			'resize':				'onResize'
		},
		
		onValidationBegin: function(){
			acf.disable( this.$sideData(), this.cid );
		},
		
		onValidationFailure: function(){
			acf.enable( this.$sideData(), this.cid );
		},
		
		$control: function(){
			return this.$('.acf-gallery');
		},
		
		$collection: function(){
			return this.$('.acf-gallery-attachments');
		},
		
		$attachments: function(){
			return this.$('.acf-gallery-attachment:not(.not-valid)');
		},

		$clone: function(){
			return this.$('.image-preview-clone');
		},
		
		$attachment: function( id ){
			return this.$('.acf-gallery-attachment[data-id="' + id + '"]');
		},
		
		$active: function(){
			return this.$('.acf-gallery-attachment.active');
		},

		$inValid: function(){
			return this.$('.acf-gallery-attachment.not-valid');
		},
		
		$main: function(){
			return this.$('.acf-gallery-main');
		},
		
		$side: function(){
			return this.$('.acf-gallery-side');
		},
		
		$sideData: function(){
			return this.$('.acf-gallery-side-data');
		},
		
		isFull: function(){
			var max = parseInt( this.get('max') );
			var count = this.$attachments().length;
			return ( max && count >= max );
		},
		
		getValue: function(){
			
			// vars
			var val = [];
			
			// loop
			this.$attachments().each(function(){
				val.push( $(this).data('id') );
			});
			
			// return
			return val.length ? val : false;
		},
		
		addUnscopedEvents: function( self ){
			
			// invalidField
			this.on('change', '.acf-gallery-side', function(e){
				self.onUpdate( e, $(this) );
			});
		},
		
		addSortable: function( self ){
			
			// add sortable
			this.$collection().sortable({
				items: '.acf-gallery-attachment',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function (event, ui) {
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
	   			},
	   			update: function(event, ui) {
					self.$input().trigger('change');
		   		}
			});
			
			// resizable
			this.$control().resizable({
				handles: 's',
				minHeight: 200,
				stop: function(event, ui){
					acf.update_user_setting('gallery_height', ui.size.height);
				}
			});
		},
		
		initialize: function(){
			
			// add unscoped events
			this.addUnscopedEvents( this );
			
			// render
			this.render();
		},
		
		render: function(){
			
			// vars
			var $sort = this.$('.acf-gallery-sort');
			var $add = this.$('.acf-gallery-add');
			var count = this.$attachments().length;
			
			// disable add
			if( this.isFull() ) {
				$add.addClass('disabled');
			} else {
				$add.removeClass('disabled');
			}
			
			// disable select
			if( !count ) {
				$sort.addClass('disabled');
			} else {
				$sort.removeClass('disabled');
			}
			
			// resize
			this.resize();
		},
		
		resize: function(){
			
			// vars
			var width = this.$control().width();
			var target = 150;
			var columns = Math.round( width / target );
						
			// max columns = 8
			columns = Math.min(columns, 8);
			
			// update data
			this.$control().attr('data-columns', columns);
		},
		
		onResize: function(){
			this.resize();
		},
		
		openSidebar: function(){
			
			// add class
			this.$control().addClass('-open');
			
			// hide bulk actions
			// should be done with CSS
			//this.$main().find('.acf-gallery-sort').hide();
			
			// vars
			var width = this.$control().width() / 3;
			width = parseInt( width );
			width = Math.max( width, 350 );
			
			// animate
			this.$('.acf-gallery-side-inner').css({ 'width' : width-1 });
			this.$side().animate({ 'width' : width-1 }, 250);
			this.$main().animate({ 'right' : width }, 250);
		},
		
		closeSidebar: function(){
			
			// remove class
			this.$control().removeClass('-open');
			
			// clear selection
			this.$active().removeClass('active');
			
			// disable sidebar
			acf.disable( this.$side() );
			
			// animate
			var $sideData = this.$('.acf-gallery-side-data');
			this.$main().animate({ right: 0 }, 250);
			this.$side().animate({ width: 0 }, 250, function(){
				$sideData.html('');
			});
		},
		
		onClickAdd: function( e, $el ){

			this.$control().css('height','400');

			// validate
			if( this.isFull() ) {
				this.showNotice({
					text: acf.__('Maximum selection reached'),
					type: 'warning'
				});
				return;
			}
			
			// new frame
			var frame = acf.newMediaPopup({
				mode:			'select',
				title:			acf.__('Add Image to Gallery'),
				field:			this.get('key'),
				multiple:		'add',
				library:		this.get('library'),
				allowedTypes:	this.get('mime_types'),
				selected:		this.val(),
				select:			$.proxy(function( attachment, i ) {
					this.appendAttachment( attachment, i );
				}, this)
			});
		},
		imagesPreview: function( e, $el ){
			this.$control().css('height','400');
	
			var numAttachments = this.$attachments().length;
			var maxNum = this.$control().data('max');
	
			const files = e.currentTarget.files;
			Object.keys(files).forEach(i=>{
				if(maxNum>0 && numAttachments>=maxNum){
					return false;
				}
				const file = files[i];
				const reader = new FileReader();
				reader.onload = (e) => {
					var container = this.$clone().clone();
					container.removeClass('acf-hidden image-preview-clone').addClass('acf-gallery-attachment acf-uploading').find('img').attr('src',reader.result);
					container.appendTo(this.$collection());
					if(file.type == 'application/pdf'){
						container.find('.margin').append('<span class="gi-file-name">'+file.name+'</span>');					}
					this.uploadImage(file,container);
				}
				numAttachments++;
				reader.readAsDataURL(e.target.files[i]);
			});
			if(numAttachments>=maxNum && maxNum>0){
				this.$('input.images-preview').prop('disabled',true);
			}
		},

		uploadImage: function(file,container){
			var progPrc = container.find('.uploads-progress .percent');
			var progBar = container.find('.uploads-progress .bar');
			progPrc.text('33%');
			progBar.css('width','33%');
			var nonce = this.$el.parents('form').find('input[name=_acf_nonce]').val();
			var fieldKey = this.get('key');
			var fileData = new FormData();
			fileData.append('action','acf/fields/upload_images/add_attachment');
			fileData.append('file',file);
			fileData.append('field_key',fieldKey);
			fileData.append('nonce',nonce);
			
			$.ajax({
				url: acf.get('ajaxurl'),
				data: acf.prepareForAjax(fileData),
				type: 'post',
				processData: false, 
				contentType: false, 
				cache: false
			}).done(function(response){
				if(response.success){
					progPrc.text('100%');
					progBar.css('width','100%');
					if(response.data.src){
						container.find('img').attr('src',response.data.src);
					}
					container.attr('data-id',response.data.id).find('.acf-gallery-remove').attr('data-id',response.data.id);
					var idInput = $('<input>').attr({
						type:"hidden",
						name:"acf["+fieldKey+"][]",
						value:response.data.id
					});
					container.prepend(idInput).removeClass('acf-uploading');
					setTimeout(function(){
						container.find('.uploads-progress').remove();
					  }, 5000);
				}else{
					container.find('.uploads-progress').remove();
					container.addClass('not-valid').append('<p class="errors">'+response.data+'</p>').find('.margin').append('<p class="upload-fail">x</p>');
				}
			  } 
			);
		},

		onClickUpload: function( e, $el ){
						
			// validate
			if( this.isFull() ) {
				this.showNotice({
					text: acf.__('Maximum selection reached: '+this.$control().data('max')),
					type: 'warning'
				});
				return;
			}
			if(this.$inValid()){
				this.$inValid().remove();
				this.$('input.images-preview').prop('disabled',false);
			}

			
		},
		
		appendAttachment: function( attachment, i ){
			
			// vars
			attachment = this.validateAttachment( attachment );
			
			// bail early if is full
			if( this.isFull() ) {
				return;
			}
			
			// bail early if already exists
			if( this.$attachment( attachment.id ).length ) {
				return;
			}
			
			// html
			var html = [
			'<div class="acf-gallery-attachment" data-id="' + attachment.id + '">',
				'<input type="hidden" value="' + attachment.id + '" name="' + this.getInputName() + '[]">',
				'<div class="margin" title="">',
					'<div class="thumbnail">',
						'<img src="" alt="">',
					'</div>',
					'<div class="filename"></div>',
				'</div>',
				'<div class="actions">',
					'<a href="#" class="acf-icon -cancel dark acf-gallery-remove" data-id="' + attachment.id + '"></a>',
				'</div>',
			'</div>'].join('');
			var $html = $(html);
			
			// append
			this.$collection().append( $html );
			
			// move to beginning
			if( this.get('insert') === 'prepend' ) {
				var $before = this.$attachments().eq( i );
				if( $before.length ) {
					$before.before( $html );
				}
			}
			
			// render attachment
			this.renderAttachment( attachment );
			
			// render
			this.render();
			
			// trigger change
			this.$input().trigger('change');
		},
		
		validateAttachment: function( attachment ){
			
			// defaults
			attachment = acf.parseArgs(attachment, {
				id: '',
				url: '',
				alt: '',
				title: '',
				filename: '',
				type: 'image'
			});
			
			// WP attachment
			if( attachment.attributes ) {
				attachment = attachment.attributes;
				
				// preview size
				var url = acf.isget(attachment, 'sizes', this.get('preview_size'), 'url');
				if( url !== null ) {
					attachment.url = url;
				}
			}
			
			// return
			return attachment;
		},
		
		renderAttachment: function( attachment ){
			
			// vars
			attachment = this.validateAttachment( attachment );
			
			// vars
			var $el = this.$attachment( attachment.id );
			
			// Image type.
			if( attachment.type == 'image' ) {
				
				// Remove filename.
				$el.find('.filename').remove();
			
			// Other file type.	
			} else {	
				
				// Check for attachment featured image.
				var image = acf.isget(attachment, 'image', 'src');
				if( image !== null ) {
					attachment.url = image;
				}
				
				// Update filename text.
				$el.find('.filename').text( attachment.filename );
			}
			
			// Default to mimetype icon.
			if( !attachment.url ) {
				attachment.url = acf.get('mimeTypeIcon');
				$el.addClass('-icon');
			}
			
			// update els
		 	$el.find('img').attr({
			 	src:	attachment.url,
			 	alt:	attachment.alt,
			 	title:	attachment.title
			});
		 	
			// update val
		 	acf.val( $el.find('input'), attachment.id );
		},
		
		editAttachment: function( id ){
			
			// new frame
			var frame = acf.newMediaPopup({
				mode:		'edit',
				title:		acf.__('Edit Image'),
				button:		acf.__('Update Image'),
				attachment:	id,
				field:		this.get('key'),
				select:		$.proxy(function( attachment, i ) {
					this.renderAttachment( attachment );
					// todo - render sidebar
				}, this)
			});
		},
		
		onClickEdit: function( e, $el ){
			var id = $el.data('id');
			if( id ) {
				this.editAttachment( id );
			}
		},
		
		removeAttachment: function( id ){
			
			// close sidebar (if open)
			this.closeSidebar();
			
			// remove attachment
			this.$attachment( id ).remove();
			
			// render
			this.render();
			
			// trigger change
			this.$input().trigger('change');
		},
		
		onClickRemove: function( e, $el ){
			
			// prevent event from triggering click on attachment
			e.preventDefault();
			e.stopPropagation();
			
			//remove
			var id = $el.data('id');
			if( id ) {
				this.removeAttachment( id );
			}else{
				$el.parents('.acf-gallery-attachment').remove();     
			}
			var numAttachments = this.$attachments().length;
			var maxNum = this.$control().data('max');
			if(numAttachments<maxNum){
				this.$('input.images-preview').prop('disabled',false);
			}
		},
		
		selectAttachment: function( id ){
			
			// vars
			var $el = this.$attachment( id );
			
			// bail early if already active
			if( $el.hasClass('active') ) {
				return;
			}
			
			// step 1
			var step1 = this.proxy(function(){
				
				// save any changes in sidebar
				this.$side().find(':focus').trigger('blur');
				
				// clear selection
				this.$active().removeClass('active');
				
				// add selection
				$el.addClass('active');
				
				// open sidebar
				this.openSidebar();
				
				// call step 2
				step2();
			});
			
			// step 2
			var step2 = this.proxy(function(){
				
				// ajax
				var ajaxData = {
					action: 'acf/fields/gallery/get_attachment',
					field_key: this.get('key'),
					id: id
				};
				
				// abort prev ajax call
				if( this.has('xhr') ) {
					this.get('xhr').abort();
				}
				
				// loading
				acf.showLoading( this.$sideData() );
				
				// get HTML
				var xhr = $.ajax({
					url: acf.get('ajaxurl'),
					data: acf.prepareForAjax(ajaxData),
					type: 'post',
					dataType: 'html',
					cache: false,
					success: step3
				});
				
				// update
				this.set('xhr', xhr);
			});
			
			// step 3
			var step3 = this.proxy(function( html ){
				
				// bail early if no html
				if( !html ) {
					return;
				}
				
				// vars
				var $side = this.$sideData();
				
				// render
				$side.html( html );
				
				// remove acf form data
				$side.find('.compat-field-acf-form-data').remove();
				
				// merge tables
				$side.find('> table.form-table > tbody').append( $side.find('> .compat-attachment-fields > tbody > tr') );	
								
				// setup fields
				acf.doAction('append', $side);
			});
			
			// run step 1
			step1();
		},
		
		onClickSelect: function( e, $el ){
			var id = $el.data('id');
			if( id ) {
				this.selectAttachment( id );
			}
		},
		
		onClickClose: function( e, $el ){
			this.closeSidebar();
		},
		
		onChangeSort: function( e, $el ){
			
			// Bail early if is disabled.
			if( $el.hasClass('disabled') ) {
				return;
			}
			
			// Get sort val.
			var val = $el.val();
			if( !val ) {
				return;
			}
			
			// find ids
			var ids = [];
			this.$attachments().each(function(){
				ids.push( $(this).data('id') );
			});
			
			
			// step 1
			var step1 = this.proxy(function(){
				
				// vars
				var ajaxData = {
					action: 'acf/fields/gallery/get_sort_order',
					field_key: this.get('key'),
					ids: ids,
					sort: val
				};
				
				
				// get results
			    var xhr = $.ajax({
			    	url:		acf.get('ajaxurl'),
					dataType:	'json',
					type:		'post',
					cache:		false,
					data:		acf.prepareForAjax(ajaxData),
					success:	step2
				});
			});
			
			// step 2
			var step2 = this.proxy(function( json ){
				
				// validate
				if( !acf.isAjaxSuccess(json) ) {
					return;
				}
				
				// reverse order
				json.data.reverse();
				
				// loop
				json.data.map(function(id){
					this.$collection().prepend( this.$attachment(id) );
				}, this);
			});
			
			// call step 1
			step1();
		},
		
		onUpdate: function( e, $el ){
			
			// vars
			var $submit = this.$('.acf-gallery-update');
			
			// validate
			if( $submit.hasClass('disabled') ) {
				return;
			}
			
			// serialize data
			var ajaxData = acf.serialize( this.$sideData() );
			
			// loading
			$submit.addClass('disabled');
			$submit.before('<i class="acf-loading"></i> ');
			
			// append AJAX action		
			ajaxData.action = 'acf/fields/gallery/update_attachment';
			
			// ajax
			$.ajax({
				url: acf.get('ajaxurl'),
				data: acf.prepareForAjax(ajaxData),
				type: 'post',
				dataType: 'json',
				complete: function(){
					$submit.removeClass('disabled');
					$submit.prev('.acf-loading').remove();
				}
			});
		},
		
		onHover: function(){
			
			// add sortable
			this.addSortable( this );
			
			// remove event
			this.off('mouseover');
		}
	});
	
	acf.registerFieldType( Field );
	
	// register existing conditions
	acf.registerConditionForFieldType('hasValue', 'upload_images');
	acf.registerConditionForFieldType('hasNoValue', 'upload_images');
	acf.registerConditionForFieldType('selectionLessThan', 'upload_images');
	acf.registerConditionForFieldType('selectionGreaterThan', 'upload_images');

	
	var Field = acf.models.RelationshipField.extend({		   
		type: 'product_grouped',
	});
	acf.registerFieldType( Field );	
	var Field = acf.models.RelationshipField.extend({		   
		type: 'product_cross_sells',
	});
	acf.registerFieldType( Field );	
	var Field = acf.models.RelationshipField.extend({		   
		type: 'product_upsells',
	});
	acf.registerFieldType( Field );	

	var Field = acf.models.ImageField.extend({		   
		type: 'upload_image',
	});
	acf.registerFieldType( Field );

	var Field = acf.models.ImageField.extend({		   
		type: 'featured_image',
	});
	acf.registerFieldType( Field );
	var Field = acf.models.ImageField.extend({		   
		type: 'site_logo',
	});
	acf.registerFieldType( Field );
	var Field = acf.models.ImageField.extend({		   
		type: 'main_image',
	});
	acf.registerFieldType( Field );
})(jQuery);


(function($, undefined){
	
	var Field = acf.Field.extend({
		
		type: 'related_terms',
		
		data: {
			'ftype': 'select'
		},
		
		select2: false,
		
		wait: 'load',
		
		events: {
			'click a[data-name="add"]': 'onClickAdd',
			'click input[type="radio"]': 'onClickRadio',
		},
		
		$control: function(){
			return this.$('.acf-related-terms-field');
		},
		
		$input: function(){
			return this.getRelatedPrototype().$input.apply(this, arguments);
		},
		
		getRelatedType: function(){
			
			// vars
			var fieldType = this.get('ftype');
			
			// normalize
			if( fieldType == 'multi_select' ) {
				fieldType = 'select';
			}

			// return
			return fieldType;
			
		},
		
		getRelatedPrototype: function(){
			return acf.getFieldType( this.getRelatedType() ).prototype;
		},
		
		getValue: function(){
			return this.getRelatedPrototype().getValue.apply(this, arguments);
		},
		
		setValue: function(){
			return this.getRelatedPrototype().setValue.apply(this, arguments);
		},
		
		initialize: function(){
		
			// vars
			var $select = this.$input();
			
			// inherit data
			this.inherit( $select );
			
			// select2
			if( this.get('ui') ) {
				
				// populate ajax_data (allowing custom attribute to already exist)
				ajaxAction = 'acf/fields/related_terms/query';
				
				// select2
				this.select2 = acf.newSelect2($select, {
					field: this,
					ajax: this.get('ajax'),
					multiple: this.get('multiple'),
					placeholder: this.get('placeholder'),
					allowNull: this.get('allow_null'),
					ajaxAction: ajaxAction,
				});
			}
		},
		
		onRemove: function(){
			if( this.select2 ) {
				this.select2.destroy();
			}
		},
		
		onClickAdd: function( e, $el ){
			
			// vars
			var field = this;
			var popup = false;
			var $form = false;
			var $name = false;
			var $parent = false;
			var $button = false;
			var $message = false;
			var notice = false;
			
			// step 1.
			var step1 = function(){
				
				// popup
				popup = acf.newPopup({
					title: $el.attr('title'),
					loading: true,
					width: '300px'
				});
				
				// ajax
				var ajaxData = {
					action:		'acf/fields/related_terms/add_term',
					field_key:	field.get('key'),
					taxonomy:	field.get('taxonomy'),
				};
				
				// get HTML
				$.ajax({
					url: acf.get('ajaxurl'),
					data: acf.prepareForAjax(ajaxData),
					type: 'post',
					dataType: 'html',
					success: step2
				});
			};
			
			// step 2.
			var step2 = function( html ){
				
				// update popup
				popup.loading(false);
				popup.content(html);
				
				// vars
				$form = popup.$('form');
				$name = popup.$('input[name="term_name"]');
				$parent = popup.$('select[name="term_parent"]');
				$button = popup.$('.acf-submit-button');
				
				// focus
				$name.focus();
				
				// submit form
				popup.on('submit', 'form', step3);
			};
			
			// step 3.
			var step3 = function( e, $el ){
				
				// prevent
				e.preventDefault();
				e.stopImmediatePropagation();
				
				// basic validation
				if( $name.val() === '' ) {
					$name.focus();
					return false;
				}
				
				// disable
				acf.startButtonLoading( $button );
				
				// ajax
				var ajaxData = {
					action: 		'acf/fields/related_terms/add_term',
					field_key:		field.get('key'),
					taxonomy: 		field.get('taxonomy'),
					term_name:		$name.val(),
					term_parent:	$parent.length ? $parent.val() : 0
				};
				
				$.ajax({
					url: acf.get('ajaxurl'),
					data: acf.prepareForAjax(ajaxData),
					type: 'post',
					dataType: 'json',
					success: step4
				});
			};
			
			// step 4.
			var step4 = function( json ){
				
				// enable
				acf.stopButtonLoading( $button );
				
				// remove prev notice
				if( notice ) {
					notice.remove();
				}
				
				// success
				if( acf.isAjaxSuccess(json) ) {
					
					// clear name
					$name.val('');
					
					// update term lists
					step5( json.data );
					
					// notice
					notice = acf.newNotice({
						type: 'success',
						text: acf.getAjaxMessage(json),
						target: $form,
						timeout: 2000,
						dismiss: false
					});
					
				} else {
					
					// notice
					notice = acf.newNotice({
						type: 'error',
						text: acf.getAjaxError(json),
						target: $form,
						timeout: 2000,
						dismiss: false
					});
				}
				
				// focus
				$name.focus();
			};
			
			// step 5.
			var step5 = function( term ){
				
				// update parent dropdown
				var $option = $('<option value="' + term.term_id + '">' + term.term_label + '</option>');
				if( term.term_parent ) {
					$parent.children('option[value="' + term.term_parent + '"]').after( $option );
				} else {
					$parent.append( $option );
				}
				
				// add this new term to all taxonomy field
				var fields = acf.getFields({
					type: 'related_terms'
				});
				
				fields.map(function( otherField ){
					if( otherField.get('taxonomy') == field.get('taxonomy') ) {
						otherField.appendTerm( term );
					}
				});
				
				// select
				field.selectTerm( term.term_id );
			};
			
			// run
			step1();	
		},
		
		appendTerm: function( term ){
			
			if( this.getRelatedType() == 'select' ) {
				this.appendTermSelect( term );
			} else {
				this.appendTermCheckbox( term );
			}
		},
		
		appendTermSelect: function( term ){
			
			this.select2.addOption({
				id:			term.term_id,
				text:		term.term_label
			});
			
		},
		
		appendTermCheckbox: function( term ){
			
			// vars
			var name = this.$('[name]:first').attr('name');
			var $ul = this.$('ul:first');
			
			// allow multiple selection
			if( this.getRelatedType() == 'checkbox' ) {
				name += '[]';
			}
			
			// create new li
			var $li = $([
				'<li data-id="' + term.term_id + '">',
					'<label>',
						'<input type="' + this.get('ftype') + '" value="' + term.term_id + '" name="' + name + '" /> ',
						'<span>' + term.term_name + '</span>',
					'</label>',
				'</li>'
			].join(''));
			
			// find parent
			if( term.term_parent ) {
				
				// vars
				var $parent = $ul.find('li[data-id="' + term.term_parent + '"]');
				
				// update vars
				$ul = $parent.children('ul');
				
				// create ul
				if( !$ul.exists() ) {
					$ul = $('<ul class="children acf-bl"></ul>');
					$parent.append( $ul );
				}
			}
			
			// append
			$ul.append( $li );
		},
		
		selectTerm: function( id ){
			if( this.getRelatedType() == 'select' ) {
				this.select2.selectOption( id );
			} else {
				var $input = this.$('input[value="' + id + '"]');
				$input.prop('checked', true).trigger('change');
			}
		},
		
		onClickRadio: function( e, $el ){
			
			// vars
			var $label = $el.parent('label');
			var selected = $label.hasClass('selected');
			
			// remove previous selected
			this.$('.selected').removeClass('selected');
			
			// add active class
			$label.addClass('selected');
			
			// allow null
			if( this.get('allow_null') && selected ) {
				$label.removeClass('selected');
				$el.prop('checked', false).trigger('change');
			}
		}
	});
	
	acf.registerFieldType( Field );
		
})(jQuery);

acf.add_filter('select2_ajax_data', function( data, args, $input, field, instance ){

    if(field != false){
		$field_taxonomy = field.find('.acf-related-terms-field').data('taxonomy');
		data.taxonomy = $field_taxonomy;
	}
    return data;

});

(function($, undefined){
	
	var Field = acf.Field.extend({
		
		type: 'display_name',
		
		select2: false,
		
		wait: 'load',
		
		events: {
			'removeField': 'onRemove',
			'duplicateField': 'onDuplicate'
		},
		
		$input: function(){
			return this.$('select');
		},
		
		initialize: function(){
			
			// vars
			var $select = this.$input();
			
			// inherit data
			this.inherit( $select );
			
			// select2
			if( this.get('ui') ) {
				
				// populate ajax_data (allowing custom attribute to already exist)
				var ajaxAction = this.get('ajax_action');
				if( !ajaxAction ) {
					ajaxAction = 'acf/fields/' + this.get('type') + '/query';
				}
				
				// select2
				this.select2 = acf.newSelect2($select, {
					field: this,
					ajax: this.get('ajax'),
					multiple: this.get('multiple'),
					placeholder: this.get('placeholder'),
					allowNull: this.get('allow_null'),
					ajaxAction: ajaxAction,
				});
			}
		},

		
		onRemove: function(){
			if( this.select2 ) {
				this.select2.destroy();
			}
		},
		
		onDuplicate: function( e, $el, $duplicate ){
			if( this.select2 ) {
				$duplicate.find('.select2-container').remove();
				$duplicate.find('select').removeClass('select2-hidden-accessible');
			}
		}
	});
	
	acf.registerFieldType( Field );

	var tfFields = ['allow_comments'];

	$.each(tfFields, function(index, value){
		var Field = acf.models.TrueFalseField.extend({		   
			type: value,
		});
		acf.registerFieldType( Field );	
		acf.registerConditionForFieldType('equalTo', value);
		acf.registerConditionForFieldType('notEqualTo', value);
	});

	var Field = acf.Field.extend({
        
        type: 'list_items',
        wait: '',
        
        events: {
            'click a[data-event="add-row"]': 		'onClickAdd',
            'click a[data-event="duplicate-row"]':	'onClickDuplicate',
            'click a[data-event="remove-row"]': 	'onClickRemove',
			'click a[data-event="collapse-row"]': 	'onClickCollapse',
            'showField':							'onShow',
            'unloadField':							'onUnload',
            'mouseover': 							'onHover',
        },
            
        $control: function(){
            return this.$('.acf-repeater:first');
        },
        
        $table: function(){
            return this.$('table:first');
        },
        
        $tbody: function(){
            return this.$('tbody:first');
        },
        
        $rows: function(){
            return this.$('tbody:first > tr').not('.acf-clone');
        },
        
        $row: function( index ){
            return this.$('tbody:first > tr:eq(' + index + ')');
        },
        
        $clone: function(){
            return this.$('tbody:first > tr.acf-clone');
        },
        
        $actions: function(){
            return this.$('.acf-actions:last');
        },
        
        $button: function(){
            return this.$('.acf-actions:last .button');
        },
        
        getValue: function(){
            return this.$rows().length;
        },
        
        allowRemove: function(){
            var min = parseInt( this.get('min') );
            return ( !min || min < this.val() );
        },
        
        allowAdd: function(){
            var max = parseInt( this.get('max') );
            return ( !max || max > this.val() );
        },
        
        addSortable: function( self ){
            
            // bail early if max 1 row
            if( this.get('max') == 1 ) {
                return;
            }
            
            // add sortable
            this.$tbody().sortable({
                items: '> tr',
                handle: '> td.order',
                forceHelperSize: true,
                forcePlaceholderSize: true,
                scroll: true,
                    stop: function(event, ui) {
                    self.render();
                    },
                    update: function(event, ui) {
                    self.$input().trigger('change');
                    }
            });
        },
        
        addCollapsed: function(){
            
            // vars
            var indexes = preference.load( this.get('key') );
            
            // bail early if no collapsed
            if( !indexes ) {
                return false;
            }
            
            // loop
            this.$rows().each(function( i ){
                if( indexes.indexOf(i) > -1 ) {
                    $(this).addClass('-collapsed');
                }
            });
        },
        
        addUnscopedEvents: function( self ){
            
            // invalidField
            this.on('invalidField', '.acf-row', function(e){
                var $row = $(this);
                if( self.isCollapsed($row) ) {
                    self.expand( $row );
                }
            });
        },
                
        initialize: function(){
            
            // add unscoped events
            this.addUnscopedEvents( this );
            
            // add collapsed
            //this.addCollapsed();
            
            // disable clone
            acf.disable( this.$clone(), this.cid );
            
            // render
            this.render();
        },
        
        render: function(){
            
            // update order number
            this.$rows().each(function( i ){
                $(this).find('> .order > span').html( i+1 );
            });
            
            // Extract vars.
            var $controll = this.$control();
            var $button = this.$button();
            
            // empty
            if( this.val() == 0 ) {
                $controll.addClass('-empty');
            } else {
                $controll.removeClass('-empty');
            }
            
            // Reached max rows.
            if( !this.allowAdd() ) {
                $controll.addClass('-max');
                $button.addClass('disabled');
            } else {
                $controll.removeClass('-max');
                $button.removeClass('disabled');
            }
            
            // Reached min rows (not used).
            //if( !this.allowRemove() ) {
            //	$controll.addClass('-min');
            //} else {
            //	$controll.removeClass('-min');
            //}	
        },
        
        validateAdd: function(){
            
            // return true if allowed
            if( this.allowAdd() ) {
                return true;
            }
            
            // vars
            var max = this.get('max');
            var text = acf.__('Maximum rows reached ({max} rows)');
            
            // replace
            text = text.replace('{max}', max);
            
            // add notice
            this.showNotice({
                text: text,
                type: 'warning'
            });
            
            // return
            return false;
        },
        
        onClickAdd: function( e, $el ){
            
            // validate
            if( !this.validateAdd() ) {
                return false;
            }
            
            // add above row
            if( $el.hasClass('acf-icon') ) {
                this.add({
                    before: $el.closest('.acf-row')
                });
            
            // default
            } else {
                this.add();
            }
        },
        
        add: function( args ){
            
            // validate
            if( !this.allowAdd() ) {
                return false;
            }
            
            // defaults
            args = acf.parseArgs(args, {
                before: false
            });
            
            // add row
            var $el = acf.duplicate({
                target: this.$clone(),
                append: this.proxy(function( $el, $el2 ){
                    
                    // append
                    if( args.before ) {
                        args.before.before( $el2 );
                    } else {
                        $el.before( $el2 );
                    }
                    
                    // remove clone class
                    $el2.removeClass('acf-clone');
                    
                    // enable
                    acf.enable( $el2, this.cid );
                    
                    // render
                    this.render();
                })
            });
            
            // trigger change for validation errors
            this.$input().trigger('change');
            
            // return
            return $el;
        },
        
        onClickDuplicate: function( e, $el ){
            
            // Validate with warning.
            if( !this.validateAdd() ) {
                return false;
            }
            
            // get layout and duplicate it.
            var $row = $el.closest('.acf-row');
            this.duplicateRow( $row );
        },

        duplicateRow: function( $row ){
            
            // Validate without warning.
            if( !this.allowAdd() ) {
                return false;
            }
            
            // Vars.
            var fieldKey = this.get('key');
            
            // Duplicate row.
            var $el = acf.duplicate({
                target: $row,
                
                // Provide a custom renaming callback to avoid renaming parent row attributes.
                rename: function( name, value, search, replace ){
                    
                    // Rename id attributes from "field_1-search" to "field_1-replace".
                    if( name === 'id' ) {
                        return value.replace( fieldKey + '-' + search, fieldKey + '-' + replace );
                    
                    // Rename name and for attributes from "[field_1][search]" to "[field_1][replace]".
                    } else {
                        return value.replace( fieldKey + '][' + search, fieldKey + '][' + replace );
                    }
                },
                before: function( $el ){
                    acf.doAction('unmount', $el);
                },
                after: function( $el, $el2 ){
                    acf.doAction('remount', $el);
                },
            });
            
            // trigger change for validation errors
            this.$input().trigger('change');
            
            // Update order numbers.
            this.render();

            // Focus on new row.
            acf.focusAttention( $el );
                    
            // Return new layout.
            return $el;
        },

        validateRemove: function(){
            
            // return true if allowed
            if( this.allowRemove() ) {
                return true;
            }
            
            // vars
            var min = this.get('min');
            var text = acf.__('Minimum rows reached ({min} rows)');
            
            // replace
            text = text.replace('{min}', min);
            
            // add notice
            this.showNotice({
                text: text,
                type: 'warning'
            });
            
            // return
            return false;
        },
        
        onClickRemove: function( e, $el ){
            var $row = $el.closest('.acf-row');
            
            // Bypass confirmation when holding down "shift" key.
            if( e.shiftKey ) {
                return this.remove( $row );
            }

            // add class
            $row.addClass('-hover');
            
            // add tooltip
            var tooltip = acf.newTooltip({
                confirmRemove: true,
                target: $el,
                context: this,
                confirm: function(){
                    this.remove( $row );
                },
                cancel: function(){
                    $row.removeClass('-hover');
                }
            });
        },

        remove: function( $row ){
            
            // reference
            var self = this;
            
            // remove
            acf.remove({
                target: $row,
                endHeight: 0,
                complete: function(){
                    
                    // trigger change to allow attachment save
                    self.$input().trigger('change');
                
                    // render
                    self.render();
                    
                    // sync collapsed order
                    //self.sync();
                }
            });
        },
        
        isCollapsed: function( $row ){
            return $row.hasClass('-collapsed');
        },
        
        collapse: function( $row ){
            $row.addClass('-collapsed');
            acf.doAction('hide', $row, 'collapse');
        },
        
        expand: function( $row ){
            $row.removeClass('-collapsed');
            acf.doAction('show', $row, 'collapse');
        },
        
        onClickCollapse: function( e, $el ){
            
            // vars
            var $row = $el.closest('.acf-row');
            var isCollpased = this.isCollapsed( $row );
            
            // shift
            if( e.shiftKey ) {
                $row = this.$rows();
            }
            
            // toggle
            if( isCollpased ) {
                this.expand( $row );
            } else {
                this.collapse( $row );
            }	
        },
        
        onShow: function( e, $el, context ){
            
            // get sub fields
            var fields = acf.getFields({
                is: ':visible',
                parent: this.$el,
            });
            
            // trigger action
            // - ignore context, no need to pass through 'conditional_logic'
            // - this is just for fields like google_map to render itself
            acf.doAction('show_fields', fields);
        },
        
        onUnload: function(){
            
            // vars
            var indexes = [];
            
            // loop
            this.$rows().each(function( i ){
                if( $(this).hasClass('-collapsed') ) {
                    indexes.push( i );
                }
            });
            
            // allow null
            indexes = indexes.length ? indexes : null;
            
            // set
            preference.save( this.get('key'), indexes );
        },
        
        onHover: function(){
            
            // add sortable
            this.addSortable( this );
            
            // remove event
            this.off('mouseover');
        }
    });
    
    acf.registerFieldType( Field );
	acf.registerConditionForFieldType('hasValue', 'list_items');
    acf.registerConditionForFieldType('hasNoValue', 'list_items');
    acf.registerConditionForFieldType('lessThan', 'list_items');
    acf.registerConditionForFieldType('greaterThan', 'list_items');
	
})(jQuery);

