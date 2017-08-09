jQuery(document).ready(function($){
	var settings = {
			live_mode_selected: function(e){
				if($(this).is(':checked')){
					$('.test-mode').prop('checked', false);
				}else{
					$('.test-mode').prop('checked', true);
				}
			},
			test_mode_selected: function(e){
				if($(this).is(':checked')){
					$('.live-mode').prop('checked', false);
				}else{
					$('.live-mode').prop('checked', true);
				}
			},
			close_admin_notice: function(e){
				e.preventDefault();
				$(this).parent('div').remove();
			},
			init: function(){
				$(document.body).on('change', '.live-mode', this.live_mode_selected);
				$(document.body).on('change', '.test-mode', this.test_mode_selected);
				$('.button-collapse').sideNav();
				$('select').material_select();
				$('.modal-trigger').leanModal();
				$('.close-stripe-admin-notice').on('click', this.close_admin_notice);
				$('.add-chip-setting').on('click', this.add_chip_setting);
				$('.remove-settings-chip').on('click', this.close_meta_chip);
				if($('#stripe_gateway_license_status').val() !== 'active'){
					$('.live-option').prop('disabled', true);
				}
				$('#stripe_checkout_settings_checkout_flow').on('change', this.display_checkout_flow_settings);
				this.display_checkout_flow_settings();
			},
			remove_settings_chip: function(){
				$(this).parent('div').remove();
			},
			add_chip_setting: function(e){
				e.preventDefault();
				var option, html, title, fieldKey;
				fieldKey = $(this).attr('field-key');
				option = $($(this).attr('select-id')).val();
				title = stripe_settings_vars.keys[fieldKey].options[option];
				html = stripe_settings_vars.keys[fieldKey].html.replace('%title%', title);
				html = html.replace('%name%', option);
				if($('input[name="'+fieldKey + '['+option+']"]').length > 0){
					Materialize.toast(stripe_settings_vars.keys[fieldKey].toast.replace('%s', title), 2000, 'red lighten-2');
					return;
				}
				var parent = $(this).parent().parent('.row');
				var next = parent.find('div.chip-settings-container');
				next.append(html);
				$('.remove-settings-chip').on('click', this.close_meta_chip);
			},
			display_checkout_flow_settings: function(){
				var val = $('#stripe_checkout_settings_checkout_flow').val();
				if(val === 'custom_form'){
					$('.custom-form-subitem').closest('tr').show();
					$('.checkout-form-subitem').closest('tr').hide();
				}else{
					$('.checkout-form-subitem').closest('tr').show();
					$('.custom-form-subitem').closest('tr').hide();
				}
			}
	}
	settings.init();
})