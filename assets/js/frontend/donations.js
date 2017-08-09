jQuery(document).ready(function($){
	Stripe_Checkout = function(){};
	Stripe_Checkout.initialize = function(){
		var donation = {
				card_types: 'elo maestro forbrugsforeningen dankort visa amex mastercard dinersclub discover jcb',
				form: $('#stripe_gateway_card_container').closest('form'),
				$container: $('#stripe_gateway_card_container'),
				$saved_methods: $('#stripe_gateway_saved_methods'),
				$initialized_input: $('#stripe_gateway_initialized'),
				display_container: function(){
					donation.$saved_methods.slideUp(400, 'linear', donation.$container.slideDown());
					donation.update_saved_token('');
				},
				display_saved_methods: function(){
					donation.$container.slideUp(400, 'linear', donation.$saved_methods.slideDown());
					donation.update_saved_token($('.stripe-payment-method.active').attr('stripe-token'));
				},
				is_initialized: function(){
					return donation.$initialized_input.val() === 'true'
				},
				gateway_initialized: function()
				{
					donation.$initialized_input.val('true');
					$(document.body).trigger('stripe_gateway_initialized');
				},
				saved_method_selected: function(){
					return $('#stripe_saved_method_token').length && $('#stripe_saved_method_token').val().length > 0;
				},
				checkout_place_order: function(){
					if(donation.is_gateway_selected()){
						if(donation.saved_method_selected()){
							return true;
						}else{
							if(donation.token_received){
								return true;
							}else{
								return false;
							}
						}
					}
				},
				create_token: function(e){
					e.preventDefault();
					if(donation.saved_method_selected()){
						donation.form.submit();
					}
					donation.has_errors = false;
					if(donation.is_form_valid()){
						donation.display_processing();
						donation.create_billing_name();
						try{
							Stripe.card.createToken(donation.form, donation.on_token_received);
						}catch(err){
							donation.submit_error([err.message]);
						}
					}else{
						return false;
					}
				},
				is_form_valid: function(){
					var isValid = true;
					$('#stripe-gateway-card-number').toggleInputError(!donation.validate_card_number());
					$('#stripe-gateway-cvv').toggleInputError(!donation.validate_cvv());
					donation.toggle_exp_error(!donation.validate_exp());
					return !donation.has_errors;
				},
				validate_card_number: function(){
					var result;
					if((result = $.payment.validateCardNumber($('#stripe-gateway-card-number').val())) == false){
						donation.has_errors = true;
					}
					return result;
				},
				validate_cvv: function(){
					var result = true;
					if($('#stripe-gateway-cvv').length){
						if((result = $.payment.validateCardCVC($('#stripe-gateway-cvv').val())) == false){
							donation.has_errors = true;
						}
					}
					return result;
				},
				validate_exp: function(){
					var result;
					 if((result = $.payment.validateCardExpiry(donation.get_exp_value())) == false){
						 donation.has_errors = true;
					 }
					 return result;
				},
				get_exp_value: function(){
					var exp = '';
					if($('#stripe-gateway-exp-date').length > 0){
						exp = $('#stripe-gateway-exp-date').val();
					}else{
						var month = $('#stripe-gateway-exp-month').val();
						var year = $('#stripe-gateway-exp-year').val();
						exp = month + ' / ' + year;
					}
					return $.payment.cardExpiryVal(exp);
				},
				toggle_exp_error: function(error){
					if(donation.card_fields.$exp_date.length > 0){
						$('#stripe-gateway-exp-date').toggleInputError(error)
					}else{
						$('#stripe-gateway-exp-month').toggleInputError(error);
						$('#stripe-gateway-exp-year').toggleInputError(error);
					}
				},
				is_gateway_selected: function(){
					return $('#payment_method_stripe_payment_gateway').is(':checked');
				},
				has_saved_methods: function(){
					return donation.$saved_methods.length > 0;
				},
				init: function(){
					if(this.is_initialized()){
						return;
					}
					if(this.has_saved_methods()){
						this.display_saved_methods();
					}
					$('form.stripe-donation-form').on('submit', this.submit_donation);
					$(document.body).on('click', '#donation_submit', this.create_token);
					$(document.body).on('click', '.stripe-payment-method', this.saved_method_click);
					$(document.body).on('click', '.stripe-form-link.use-new', this.display_container);
					$(document.body).on('click', '.stripe-form-link.use-saved', this.display_saved_methods);
					$(this.card_fields.$card_number).payment('formatCardNumber');
					$(this.card_fields.$card_number).on('payment.cardType', this.update_card_type_for_container);
					$(this.card_fields.$card_number).fieldValidation('validateCard'); //custom validation
					$(this.card_fields.$exp_month).fieldValidation('validateExpMonth');
					$(this.card_fields.$exp_year).fieldValidation('validateExpYear');
					$(this.card_fields.$exp_date).fieldValidation('validateExpDate');
					$(this.card_fields.$exp_date).payment('formatCardExpiry');
					$(this.card_fields.$cvv).payment('formatCardCVC');
					$.each(this.card_fields, function(){
						$(this).fieldValidation('fieldFocused');
						$(this).fieldValidation('fieldBlur');
						$(this).fieldValidation('setCardField');
					});
					this.initialize_stripe();
					this.gateway_initialized();
					this.check_container_size();
				},
				check_container_size: function(){
					var width = $('.stripe-donation-form').width();
					if(width < 475){
						donation.$container.addClass('small-container');
					}
				},
				remove_invalid: function(){
					$(this).removeInputError();
				},
				update_card_type_for_container: function(e, cardType){
					if(cardType !== 'unknown'){
						if($(this).hasClass(cardType)){
							$(this).closest('div').addClass(cardType);
						}
					}else{
						$(this).closest('div').removeClass(donation.card_types);
					}
				},
				initialize_stripe: function(){
					Stripe.setPublishableKey(stripe_donation_vars.publishable_key);
				},
				on_token_received: function(status, response){
					if(response.error){
						donation.submit_error(response.error.message);
					}else{
						donation.token_received = true;
						donation.create_token_input(response.id);
						donation.form.submit();
					}
				},
				submit_error: function(messages){
					donation.token_received = false;
					$( '.donation-error' ).remove();
					$( '.donation-success' ).remove();
					var messages_html = '<ul class="donation-error-ul">';
					$.each(messages, function(index, value){
						messages_html += '<li>' + value + '</li>';
					})
					messages_html += '</ul>';
					donation.form.prepend( '<div class="donation-error">' + messages_html + '</div>');
					donation.form.removeClass( 'processing' ).unblock();
					donation.form.find( '.input-text, select, input:checkbox' ).blur();
					$( 'html, body' ).animate({
						scrollTop: ( $( 'form.stripe-donation-form' ).offset().top - 100 )
					}, 1000 );
				},
				submit_success: function(message){
					$( '.donation-error' ).remove();
					$( '.donation-success' ).remove();
					donation.form.prepend('<div class="donation-success">' + message + '</div>');
					$( 'html, body' ).animate({
						scrollTop: ( $( 'form.stripe-donation-form' ).offset().top - 100 )
					}, 1000 );
				},
				card_fields: {
					$card_number: $('#stripe-gateway-card-number'),
					$exp_date: $('#stripe-gateway-exp-date'),
					$exp_month: $('#stripe-gateway-exp-month'),
					$exp_year: $('#stripe-gateway-exp-year'),
					$postal_code: $('#stripe-gateway-postal-code'),
					$cvv: $('#stripe-gateway-cvv')
				},
				create_token_input: function(token){
					if($('#stripe_payment_token').length == 0){
						var input = document.createElement('input');
						input.type = 'hidden';
						input.id = 'stripe_payment_token';
						input.name = 'stripe_payment_token';
						input.value = token;
						donation.form.append(input);
					}else{
						$('#stripe_payment_token').val(token);
					}
				},
				create_billing_name: function(){
					var firstname = '';var lastname = '';
					if($('#first_name').length){
						firstname = $('#first_name').val();
					}
					if($('#last_name').length){
						lastname = $('#last_name').val();
					}
					var fullname = firstname + ' ' + lastname;
					if($('#stripe_billing_name').length == 0){
						var input = document.createElement('input');
						input.type = 'hidden';
						input.id = 'stripe_billing_name';
						input.value = fullname;
						input.setAttribute('data-stripe', 'name');
						donation.form.append(input);
					}else{
						$('#stripe_billing_name').val(fullname);
					}
				},
				display_processing: function(){
					var options = stripe_donation_vars.card_form_options;
					if(options.donation_card_loader_enabled){
						donation.form.block({
							message: options.donation_card_loader_html,
							css: options.donation_card_loader_css,
							overlayCSS: {
								background: '#fff',
								opacity: 0.8
							}
						})
					}
				},
				hide_processing: function(){
					donation.form.unblock();
				},
				checkout_error: function(){
					donation.hide_processing();
				},
				saved_method_click: function(){
					$('.stripe-payment-method').each(function(){
						$(this).removeClass('active');
					})
					$(this).addClass('active');
					donation.update_saved_token($(this).attr('stripe-token'));
				},
				update_saved_token: function(value){
					$('#stripe_saved_method_token').val(value);
				},
				submit_donation: function(e){
					e.preventDefault();
					data = donation.form.serialize();
					$.ajax({
						dataType: 'json',
						method: 'post',
						data: data,
						url: stripe_donation_vars.ajax_url,
						success: function(response){
							donation.hide_processing();
							if(response.result === 'success'){
								donation.submit_success(response.message);
							}else{
								donation.submit_error(response.messages);
							}
						},
						error: function( jqXHR, textStatus, errorThrown ){
							donation.hide_processing();
							donation.submit_error([errorThrown]);
						}
					})
				}
		}
		donation.init();
	}

	setInterval(Stripe_Checkout.initialize, 1000);
});