(function($, undefined){
	
	// vars
	var CONTEXT = 'tab';
	
	var Field = acf.Field.extend({
		
		type: 'tab',
		
		wait: '',
		
		tabs: false,
		
		tab: false,
		
		events: {
			'duplicateField': 'onDuplicate'
		},

		findFields: function(){
			return this.$el.nextUntil('.acf-field-tab', '.acf-field');
		},
		
		getFields: function(){
			return acf.getFields( this.findFields() );
		},
		
		findTabs: function(){
			return this.$el.prevAll('.acf-tab-wrap:first');
		},
		
		findTab: function(){
			return this.$('.acf-tab-button');
		},
		
		initialize: function(){
			
			// bail early if is td
			if( this.$el.is('td') ) {
				this.events = {};
				return false;
			}
			
			// vars
			var $tabs = this.findTabs();
			var $tab = this.findTab();
			var settings = acf.parseArgs($tab.data(), {
				endpoint: false,
				placement: '',
				before: this.$el
			});
			
			// create wrap
			if( !$tabs.length || settings.endpoint ) {
				this.tabs = new Tabs( settings );
			} else {
				this.tabs = $tabs.data('acf');
			}
			
			// add tab
			this.tab = this.tabs.addTab($tab, this);
		},
		
		isActive: function(){
			return this.tab.isActive();
		},
		
		showFields: function(){
			
			// show fields
			this.getFields().map(function( field ){
				field.show( this.cid, CONTEXT );
				field.hiddenByTab = false;		
			}, this);
			
		},
		
		hideFields: function(){
			
			// hide fields
			this.getFields().map(function( field ){
				field.hide( this.cid, CONTEXT );
				field.hiddenByTab = this.tab;		
			}, this);
			
		},
		
		show: function( lockKey ){

			// show field and store result
			var visible = acf.Field.prototype.show.apply(this, arguments);
			
			// check if now visible
			if( visible ) {
				
				// show tab
				this.tab.show();
				
				// check active tabs
				this.tabs.refresh();
			}
						
			// return
			return visible;
		},
		
		hide: function( lockKey ){

			// hide field and store result
			var hidden = acf.Field.prototype.hide.apply(this, arguments);
			
			// check if now hidden
			if( hidden ) {
				
				// hide tab
				this.tab.hide();
				
				// reset tabs if this was active
				if( this.isActive() ) {
					this.tabs.reset();
				}
			}
						
			// return
			return hidden;
		},
		
		enable: function( lockKey ){

			// enable fields
			this.getFields().map(function( field ){
				field.enable( CONTEXT );		
			});
		},
		
		disable: function( lockKey ){
			
			// disable fields
			this.getFields().map(function( field ){
				field.disable( CONTEXT );		
			});
		},

		onDuplicate: function( e, $el, $duplicate ){
			if( this.isActive() ) {
				$duplicate.prevAll('.acf-tab-wrap:first').remove();
			}
		}
	});
	
	acf.registerFieldType( Field );
	
	
	/**
	*  tabs
	*
	*  description
	*
	*  @date	8/2/18
	*  @since	5.6.5
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	var i = 0;
	var Tabs = acf.Model.extend({
		
		tabs: [],
		
		active: false,
		
		actions: {
			'refresh': 'onRefresh'
		},
		
		data: {
			before: false,
			placement: 'top',
			index: 0,
			initialized: false,
		},
		
		setup: function( settings ){
			
			// data
			$.extend(this.data, settings);
			
			// define this prop to avoid scope issues
			this.tabs = [];
			this.active = false;
			
			// vars
			var placement = this.get('placement');
			var $before = this.get('before');
			var $parent = $before.parent();
			
			// add sidebar for left placement
			if( placement == 'left' && $parent.hasClass('acf-fields') ) {
				$parent.addClass('-sidebar');
			}
			
			// create wrap
			if( $before.is('tr') ) {
				this.$el = $('<tr class="acf-tab-wrap"><td colspan="2"><ul class="acf-hl acf-tab-group"></ul></td></tr>');
			} else {
				this.$el = $('<div class="acf-tab-wrap -' + placement + '"><ul class="acf-hl acf-tab-group"></ul></div>');
			}
			
			// append
			$before.before( this.$el );
			
			// set index
			this.set('index', i, true);
			i++;
		},
		
		initializeTabs: function(){
			
			// find first visible tab
			var tab = this.getVisible().shift();
			
			// remember previous tab state
			var order = acf.getPreference('this.tabs') || [];
			var groupIndex = this.get('index');
			var tabIndex = order[ groupIndex ];
			
			if( this.tabs[ tabIndex ] && this.tabs[ tabIndex ].isVisible() ) {
				tab = this.tabs[ tabIndex ];
			}
			
			// select
			if( tab ) {
				this.selectTab( tab );
			} else {
				this.closeTabs();
			}
			
			// set local variable used by tabsManager
			this.set('initialized', true);
		},
		
		getVisible: function(){
			return this.tabs.filter(function( tab ){
				return tab.isVisible();
			});
		},
		
		getActive: function(){
			return this.active;
		},
		
		setActive: function( tab ){
			return this.active = tab;
		},
		
		hasActive: function(){
			return (this.active !== false);
		},
		
		isActive: function( tab ){
			var active = this.getActive();
			return (active && active.cid === tab.cid);
		},
		
		closeActive: function(){
			if( this.hasActive() ) {
				this.closeTab( this.getActive() );
			}
		},
		
		openTab: function( tab ){
			
			// close existing tab
			this.closeActive();
			
			// open
			tab.open();
			
			// set active
			this.setActive( tab );
		},
		
		closeTab: function( tab ){
			
			// close
			tab.close();
			
			// set active
			this.setActive( false );
		},
		
		closeTabs: function(){
			this.tabs.map( this.closeTab, this );
		},
		
		selectTab: function( tab ){
			
			// close other tabs
			this.tabs.map(function( t ){
				if( tab.cid !== t.cid ) {
					this.closeTab( t );
				}
			}, this);
			
			// open
			this.openTab( tab );
			
		},
		
		addTab: function( $a, field ){
			
			// create <li>
			var $li = $('<li>' + $a.outerHTML() + '</li>');
			
			// append
			this.$('ul').append( $li );
			
			// initialize
			var tab = new Tab({
				$el: $li,
				field: field,
				group: this,
			});
			
			// store
			this.tabs.push( tab );
			
			// return
			return tab;
		},
		
		reset: function(){
			
			// close existing tab
			this.closeActive();
			
			// find and active a tab
			return this.refresh();
		},
		
		refresh: function(){
			
			// bail early if active already exists
			if( this.hasActive() ) {
				return false;
			}
			
			// find next active tab
			var tab = this.getVisible().shift();
			
			// open tab
			if( tab ) {
				this.openTab( tab );
			}
			
			// return
			return tab;
		},
		
		onRefresh: function(){
			
			// only for left placements
			if( this.get('placement') !== 'left' ) {
				return;
			}
			
			// vars
			var $parent = this.$el.parent();
			var $list = this.$el.children('ul');
			var attribute = $parent.is('td') ? 'height' : 'min-height';
			
			// find height (minus 1 for border-bottom)
			var height = $list.position().top + $list.outerHeight(true) - 1;
			
			// add css
			$parent.css(attribute, height);
		}	
	});
	
	var Tab = acf.Model.extend({
		
		group: false,
		
		field: false,
		
		events: {
			'click a': 'onClick'
		},
		
		index: function(){
			return this.$el.index();
		},
		
		isVisible: function(){
			return acf.isVisible( this.$el );
		},
		
		isActive: function(){
			return this.$el.hasClass('active');
		},
		
		open: function(){
			
			// add class
			this.$el.addClass('active');
			
			// show field
			this.field.showFields();
		},
		
		close: function(){
			
			// remove class
			this.$el.removeClass('active');
			
			// hide field
			this.field.hideFields();
		},
		
		onClick: function( e, $el ){
			
			// prevent default
			e.preventDefault();
			
			// toggle
			this.toggle();
		},
		
		toggle: function(){
			
			// bail early if already active
			if( this.isActive() ) {
				return;
			}
			
			// toggle this tab
			this.group.openTab( this );
		}			
	});
	
	var tabsManager = new acf.Model({
		
		priority: 50,
		
		actions: {
			'prepare':			'render',
			'append':			'render',
			'unload':			'onUnload',
			'invalid_field':	'onInvalidField'
		},
		
		findTabs: function(){
			return $('.acf-tab-wrap');
		},
		
		getTabs: function(){
			return acf.getInstances( this.findTabs() );
		},
		
		render: function( $el ){
			this.getTabs().map(function( tabs ){
				if( !tabs.get('initialized') ) {
					tabs.initializeTabs();
				}
			});
		},
		
		onInvalidField: function( field ){
			
			// bail early if busy
			if( this.busy ) {
				return;
			}
			
			// ignore if not hidden by tab
			if( !field.hiddenByTab ) {
				return;
			}
			
			// toggle tab
			field.hiddenByTab.toggle();
				
			// ignore other invalid fields
			this.busy = true;
			this.setTimeout(function(){
				this.busy = false;
			}, 100);
		},
		
		onUnload: function(){
			
			// vars
			var order = [];
			
			// loop
			this.getTabs().map(function( group ){
				var active = group.hasActive() ? group.getActive().index() : 0;
				order.push(active);
			});
			
			// bail if no tabs
			if( !order.length ) {
				return;
			}
			
			// update
			acf.setPreference('this.tabs', order);
		}
	});
	
})(jQuery);

(function($, undefined){
	
	var i = 0;
	
	var Field = acf.Field.extend({
		
		type: 'accordion',
		
		wait: '',
		
		$control: function(){
			return this.$('.acf-fields:first');
		},
		
		initialize: function(){
			
			// Bail early if this is a duplicate of an existing initialized accordion.
			if( this.$el.hasClass('acf-accordion') ) {
				return;
			}
			
			// bail early if is cell
			if( this.$el.is('td') ) return;
			
			// enpoint
			if( this.get('endpoint') ) {
				return this.remove();
			}
			
			// vars
			var $field = this.$el;
			var $label = this.$labelWrap()
			var $input = this.$inputWrap();
			var $wrap = this.$control();
			var $instructions = $input.children('.description');
			
			// force description into label
			if( $instructions.length ) {
				$label.append( $instructions );
			}
			
			// table
			if( this.$el.is('tr') ) {
				
				// vars
				var $table = this.$el.closest('table');
				var $newLabel = $('<div class="acf-accordion-title"/>');
				var $newInput = $('<div class="acf-accordion-content"/>');
				var $newTable = $('<table class="' + $table.attr('class') + '"/>');
				var $newWrap = $('<tbody/>');
				
				// dom
				$newLabel.append( $label.html() );
				$newTable.append( $newWrap );
				$newInput.append( $newTable );
				$input.append( $newLabel );
				$input.append( $newInput );
				
				// modify
				$label.remove();
				$wrap.remove();
				$input.attr('colspan', 2);
				
				// update vars
				$label = $newLabel;
				$input = $newInput;
				$wrap = $newWrap;
			}
			
			// add classes
			$field.addClass('acf-accordion');
			$label.addClass('acf-accordion-title');
			$input.addClass('acf-accordion-content');
			
			// index
			i++;
			
			// multi-expand
			if( this.get('multi_expand') ) {
				$field.attr('multi-expand', 1);
			}
			
			// open
			var order = acf.getPreference('this.accordions') || [];
			if( order[i-1] !== undefined ) {
				this.set('open', order[i-1]);
			}
			
			if( this.get('open') ) {
				$field.addClass('-open');
				$input.css('display', 'block'); // needed for accordion to close smoothly
			}
			
			// add icon
			$label.prepend( accordionManager.iconHtml({ open: this.get('open') }) );
			
			// classes
			// - remove 'inside' which is a #poststuff WP class
			var $parent = $field.parent();
			$wrap.addClass( $parent.hasClass('-left') ? '-left' : '' );
			$wrap.addClass( $parent.hasClass('-clear') ? '-clear' : '' );
			
			// append
			$wrap.append( $field.nextUntil('.acf-field-accordion', '.acf-field') );
			
			// clean up
			$wrap.removeAttr('data-open data-multi_expand data-endpoint');
		},
		
	});
	
	acf.registerFieldType( Field );


	/**
	*  accordionManager
	*
	*  Events manager for the acf accordion
	*
	*  @date	14/2/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	
	var accordionManager = new acf.Model({
		
		actions: {
			'unload':	'onUnload'
		},
		
		events: {
			'click .acf-accordion-title': 'onClick',
			'invalidField .acf-accordion':	'onInvalidField'
		},
		
		isOpen: function( $el ) {
			return $el.hasClass('-open');
		},
		
		toggle: function( $el ){
			if( this.isOpen($el) ) {
				this.close( $el );
			} else {
				this.open( $el );
			}
		},
		
		iconHtml: function( props ){
			
			// Use SVG inside Gutenberg editor.
			if( acf.isGutenberg() ) {
				if( props.open ) {
					return '<svg class="acf-accordion-icon" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g><path fill="none" d="M0,0h24v24H0V0z"></path></g><g><path d="M12,8l-6,6l1.41,1.41L12,10.83l4.59,4.58L18,14L12,8z"></path></g></svg>';
				} else {
					return '<svg class="acf-accordion-icon" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g><path fill="none" d="M0,0h24v24H0V0z"></path></g><g><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g></svg>';
				}
			} else {
				if( props.open ) {
					return '<i class="acf-accordion-icon dashicons dashicons-arrow-down"></i>';
				} else {
					return '<i class="acf-accordion-icon dashicons dashicons-arrow-right"></i>';
				}
			}
		},
		
		open: function( $el ){
			var duration = acf.isGutenberg() ? 0 : 300;
			
			// open
			$el.find('.acf-accordion-content:first').slideDown( duration ).css('display', 'block');
			$el.find('.acf-accordion-icon:first').replaceWith( this.iconHtml({ open: true }) );
			$el.addClass('-open');
			
			// action
			acf.doAction('show', $el);
			
			// close siblings
			if( !$el.attr('multi-expand') ) {
				$el.siblings('.acf-accordion.-open').each(function(){
					accordionManager.close( $(this) );
				});
			}
		},
		
		close: function( $el ){
			var duration = acf.isGutenberg() ? 0 : 300;
			
			// close
			$el.find('.acf-accordion-content:first').slideUp( duration );
			$el.find('.acf-accordion-icon:first').replaceWith( this.iconHtml({ open: false }) );
			$el.removeClass('-open');
			
			// action
			acf.doAction('hide', $el);
		},
		
		onClick: function( e, $el ){
			
			// prevent Defailt
			e.preventDefault();
			
			// open close
			this.toggle( $el.parent() );
			
		},
		
		onInvalidField: function( e, $el ){
			
			// bail early if already focused
			if( this.busy ) {
				return;
			}
			
			// disable functionality for 1sec (allow next validation to work)
			this.busy = true;
			this.setTimeout(function(){
				this.busy = false;
			}, 1000);
			
			// open accordion
			this.open( $el );
		},
		
		onUnload: function( e ){
			
			// vars
			var order = [];
			
			// loop
			$('.acf-accordion').each(function(){
				var open = $(this).hasClass('-open') ? 1 : 0;
				order.push(open);
			});
			
			// set
			if( order.length ) {
				acf.setPreference('this.accordions', order);
			}
		}
	});

})(jQuery);