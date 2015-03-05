(function( $ ) {
	"use strict";

	$(document ).ready(function(){

		/**
		 * Modal
		 **/

		// click on Manage button
		$(document).on('click', '.open_pixfields_modal', function(ev) {
			ev.preventDefault();
			open_modal();
		});

		// click on close button
		$(document).on('click', '.media-modal-close', function(ev) {
			ev.preventDefault();
			close_modal();
		});

		// add field
		$(document).on('click', '.add_new_pixfield .add_field', function( ev ){
			ev.preventDefault();
			var label = $(this).siblings('.label').find('input')[0];
			// do not allow fields without  a label
			// first force the label field to be required
			$(label).attr('required', 'required');
			if ( ! label.checkValidity() ) {
				label.reportValidity();
				return false;
			}
			$(label ).attr('required', false);

			var $list = $('.pix_fields_list' ),
				$new_field = $(this).parent('.pixfield'),
				post_type = $new_field.data('post_type' ),
				order = 0,
				filter = $($new_field).find('.filterable input')[0].checked;

			if ( typeof $list.get(0) !== 'undefined') {
				order = ( $list.get(0).childElementCount >= 0 ) ? $list.get(0).childElementCount : 1
			}

			// we only need the value
			var label_val = $(label).val();

			// do not allow an empty label input
			$list.append( get_field_template({post_type: post_type, order: order, label: label_val, filter: filter}) );

			// keep showing the last added field
			$list.parent().animate({ scrollTop: $list.parent().height() }, 300);

			// clear after append
			$(label).val('');
			$($new_field).find('.filterable input').prop('checked', false);
		});

		// delete field
		$(document ).on('click', '.pixfields_box .delete_field', function( ev, el){
			ev.preventDefault();

			var field = $(this ).siblings('.label' ).children('input' ).val();

			var response = confirm('Do you really want to delete the field '+field+'?');
			if ( ! response ) {
				return;
			}
			$(this ).parents('.pixfield').remove();
		});

		$( ".ui-sortable" ).sortable({
			connectToSortable: ".ui-sortable",
			revert: true,
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			dropOnEmpty: false,
			helper: "clone",
			handle: '.drag',
			scroll: false,
		});
		$( "ul.ui-sortable, .ui-sortable li" ).disableSelection();

		// update pixfields
		$(document).on('click', '.update_pixfields', function(ev) {
			ev.preventDefault();

			var $pixfields_container = $('#pixfields .inside'),
				pixfields = $(this).parents('.pixfields_form').find('.pix_fields_list input');

			var to_break = false;
			$(pixfields).each(function(ui, el){

				if ( $(el).attr('type') == 'text' ) {
					$(el).attr('required', true);
				}

				if ( ! el.checkValidity() ) {
					el.reportValidity();
					to_break = true;
				}

			});

			if ( to_break ) return false;

			$pixfields_container.addClass('ajax_running');
			var serialized_data = serialize_form(pixfields);

			$.ajax({
				url: pixfields_l10n.ajax_url,
				type : 'post',
				dataType : 'json',
				data: {
					action: 'save_pixfields',
					post_id: $('#post_ID' ).val(),
					fields: serialized_data
				},
				success: function (result) {
					if ( typeof result !== 'undefined' || result !== '' ) {
						if ( result.success ) {
							$('#pixfields .inside').html(result.data);
						}
					}
					$pixfields_container.removeClass('ajax_running');
				}
			});

			close_modal();
		});

		// Meta fields
		$( '.pixfield_value' ).each(function(){

			var the_value = $(this).val(),
				post_type = $(this ).parents('.pixfields' ).data('post_type' ),
				pixfield = $(this ).parents('.pixfield' ).data('pixfield');

			$(this).autocomplete({
				source: function( request, response ) {
					$.ajax({
						url: pixfields_l10n.ajax_url,
						dataType: "json",
						data: {
							action: 'pixfield_autocomplete',
							post_type: post_type,
							pixfield: pixfield,
							value: the_value
						},
						success: function( data ) {
							response($.map(data, function(v,i){
								return {
									label: v,
									value: v
								};
							}));
						}
					});
				},
				minLength: 2
				//select: function( event, ui ) {
				//	console.log( ui );
				//	log( ui.item ?
				//	"Selected: " + ui.item.label :
				//	"Nothing selected, input was " + this.value);
				//},
				//open: function() {
				//	//$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
				//},
				//close: function() {
				//	//$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
				//}
			});
		});
	});

	var close_modal = function() {
		// clear our classes
		$('#pixfields_manager' ).removeClass('active');

		// remove our atts before exit
		remove_required_atts();

		$('body' ).removeClass('pixfields_modal_visible');
	};

	var open_modal = function() {
		$('#pixfields_manager' ).addClass('active');

		// remove our atts before exit
		remove_required_atts();

		// let the body know about our modal
		$('body').addClass('pixfields_modal_visible');
	};

	var remove_required_atts = function() {
		$('.pixfields_wrapper' ).find('input').attr('required', false);
	};

	var get_field_template = function( args ) {

		var post_type = args.post_type,
			order = args.order,
			label = args.label,
			filter = args.filter;

		// if the filter field was checked then we should put it in the new template too
		if ( filter ) {
			filter = 'checked="checked"';
		} else {
			filter = '';
		}
		return '' +
			'<li class="pixfield" data-order="' + order + '">' +
				'<span class="drag"><i class="fa fa-arrows"></i></span>' +
				'<span class="label">' +
					'<input type="text" name="pixfields_list['+post_type+']['+ order +'][label]" value="' + label + '" />' +
				'</span>' +
				//'<span class="default_value">' +
				//	'<input type="text" name="pixfields_list['+post_type+']['+ order +'][default]" />' +
				//'</span>' +
				'<span class="filterable">' +
					'<input type="checkbox" name="pixfields_list['+post_type+']['+ order +'][filter]" ' + filter + '/>' +
				'</span>' +
				'<a href="#" class="delete_field">Delete</a>' +
			'</li>'
	};

	var serialize_form = function( form ) {
		if ( form.length > 0 ) {
			var serialized_data = $(form).serialize();
			return decodeURIComponent(serialized_data);
		}
		return false;
	};

	// @TODO this is a fail
	//$.fn.serializePixFields = function() {
	//	var data = {};
	//
	//	$(this).each( function( key, element ) {
	//
	//		var name = $(this ).attr('name').replace('pixfields_list[', '');
	//		name = name.substring(0, name.length - 1 );
	//
	//		var keys = name.split(']['),
	//			post_type = keys.shift(),
	//			counter = keys.shift(),
	//			field = keys.shift();
	//
	//		if ( typeof data.post_type === 'undefined' ) {
	//			data.post_type = {};
	//		}
	//
	//
	//		if ( typeof  data.post_type.counter === 'undefined' ) {
	//			data.post_type.counter = {};
	//		}
	//		//if ( typeof data.post_type.counter.field === 'undefined' ) {
	//		//	data.post_type.counter.field = {};
	//		//}
	//
	//		data.post_type.counter.field = $(this).val();
	//		console.log(post_type);
	//	});
	//
	//	return data;
	//};

	$( function() {

		/**
		 *  Checkbox value switcher
		 *  Any checkbox should switch between value 1 and 0
		 *  Also test if the checkbox needs to hide or show something under it.
		 */
		//$('#pixtypes_form input:checkbox').each(function(i,e){
		//	check_checkbox_checked(e);
		//	$(e).check_for_extended_options();
		//});
		//$('#pixtypes_form').on('click', 'input:checkbox', function(){
		//	check_checkbox_checked(this);
		//	$(this).check_for_extended_options();
		//});
		/** End Checkbox value switcher **/

		/* Ensure groups visibility */
		$( '.switch input[type=checkbox]' ).each( function() {

			if ( $( this ).data( 'show_group' ) ) {

				var show = false;
				if ( $( this ).attr( 'checked' ) ) {
					show = true
				}

				toggleGroup( $( this ).data( 'show_group' ), show );
			}
		} );

		$( '.switch ' ).on( 'change', 'input[type=checkbox]', function() {
			if ( $( this ).data( 'show_group' ) ) {
				var show = false;
				if ( $( this ).attr( 'checked' ) ) {
					show = true
				}
				toggleGroup( $( this ).data( 'show_group' ), show );
			}
		} );
	} );

	var toggleGroup = function( name, show ) {
		var $group = $( '#' + name );

		if ( show ) {
			$group.show();
		} else {
			$group.hide();
		}
	};

	/*
	 * Useful functions
	 */

	function check_checkbox_checked( input ) { // yes the name is an ironic
		if ( $( input ).attr( 'checked' ) === 'checked' ) {
			$( input ).siblings( 'input:hidden' ).val( 'on' );
		} else {
			$( input ).siblings( 'input:hidden' ).val( 'off' );
		}
	}

	/* End check_checkbox_checked() */

	$.fn.check_for_extended_options = function() {
		var extended_options = $( this ).siblings( 'fieldset.group' );
		if ( $( this ).data( 'show-next' ) ) {
			if ( extended_options.data( 'extended' ) === true ) {
				extended_options
					.data( 'extended', false )
					.css( 'height', '0' );
			} else if ( (typeof extended_options.data( 'extended' ) === 'undefined' && $( this ).attr( 'checked' ) === 'checked' ) || extended_options.data( 'extended' ) === false ) {
				extended_options
					.data( 'extended', true )
					.css( 'height', 'auto' );
			}
		}
	};

}( jQuery ));