jQuery(document).ready(function($){
	Stripe_Checkout = function(){};
	Stripe_Checkout.initialize = function(){
		var stripe_checkout = {
				card_types: 'elo maestro forbrugsforeningen dankort visa amex mastercard dinersclub discover jcb',
				form: $('#stripe_gateway_card_container').closest('form'),
				$container: $('#stripe_gateway_card_container'),
				$saved_methods: $('#stripe_gateway_saved_methods'),
				$initialized_input: $('#stripe_gateway_initialized'),
				display_container: function(){
					stripe_checkout.$saved_methods.slideUp(400, 'linear', stripe_checkout.$container.slideDown());
					stripe_checkout.update_saved_token('');
				},
				display_saved_methods: function(){
					stripe_checkout.$container.slideUp(400, 'linear', stripe_checkout.$saved_methods.slideDown());
					stripe_checkout.update_saved_token($('.stripe-payment-method.active').attr('stripe-token'));
				},
				is_initialized: function(){
					return stripe_checkout.$initialized_input.val() === 'true'
				},
				gateway_initialized: function()
				{
					stripe_checkout.$initialized_input.val('true');
					$(document.body).trigger('stripe_gateway_initialized');
				},
				saved_method_selected: function(){
					return $('#stripe_saved_method_token').length && $('#stripe_saved_method_token').val().length > 0;
				},
				checkout_place_order: function(){
					if(stripe_checkout.is_gateway_selected()){
						if(stripe_checkout.saved_method_selected()){
							return true;
						}else{
							if(stripe_checkout.token_received){
								return true;
							}else{
								return false;
							}
						}
					}
				},
				create_token: function(e){
					if(stripe_checkout.is_gateway_selected()){
						e.preventDefault();
						if(stripe_checkout.saved_method_selected()){
							stripe_checkout.form.submit();
						}
						stripe_checkout.has_errors = false;
						if(stripe_checkout.is_form_valid()){
							stripe_checkout.display_processing();
							stripe_checkout.create_billing_name();
							try{
								Stripe.card.createToken(stripe_checkout.form, stripe_checkout.on_token_received);
							}catch(err){
								stripe_checkout.submit_error(err.message);
							}
						}else{
							return false;
						}
					}
				},
				is_form_valid: function(){
					var isValid = true;
					$('#stripe-gateway-card-number').toggleInputError(!stripe_checkout.validate_card_number());
					$('#stripe-gateway-cvv').toggleInputError(!stripe_checkout.validate_cvv());
					stripe_checkout.toggle_exp_error(!stripe_checkout.validate_exp());
					$('#stripe-gateway-postal-code').toggleInputError(!stripe_checkout.validate_postal());
					return !stripe_checkout.has_errors;
				},
				validate_card_number: function(){
					var result;
					if((result = $.payment.validateCardNumber($('#stripe-gateway-card-number').val())) == false){
						stripe_checkout.has_errors = true;
					}
					return result;
				},
				validate_cvv: function(){
					var result = true;
					if($('#stripe-gateway-cvv').length){
						if((result = $.payment.validateCardCVC($('#stripe-gateway-cvv').val())) == false){
							stripe_checkout.has_errors = true;
						}
					}
					return result;
				},
				validate_exp: function(){
					var result;
					 if((result = $.payment.validateCardExpiry(stripe_checkout.get_exp_value())) == false){
						 stripe_checkout.has_errors = true;
					 }
					 return result;
				},
				validate_postal: function(){
					var result = true;
					if($('#stripe-gateway-postal-code').length > 0){
						var value = $('#stripe-gateway-postal-code').val().replace(/\s/g, '');
						if(value === ''){
							result = false;
							stripe_checkout.has_errors = true;
						}
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
					if(stripe_checkout.card_fields.$exp_date.length > 0){
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
					return stripe_checkout.$saved_methods.length > 0;
				},
				init: function(){
					if(this.is_initialized()){
						return;
					}
					if(this.has_saved_methods()){
						this.display_saved_methods();
					}
					$('form.checkout').on('checkout_place_order', this.checkout_place_order);
					$(document.body).on('click', '#place_order', this.create_token);
					$(document.body).on('checkout_error', this.checkout_error);
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
					$(this.card_fields.$postal_code).on('focus', function(e){
						$(this).removeInputError();
					});
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
					var width = $('.payment_methods').width();
					if(width < 475){
						stripe_checkout.$container.addClass('small-container');
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
						$(this).closest('div').removeClass(stripe_checkout.card_types);
					}
				},
				initialize_stripe: function(){
					Stripe.setPublishableKey(stripe_gateway_checkout_vars.publishable_key);
				},
				on_token_received: function(status, response){
					if(response.error){
						stripe_checkout.submit_error(response.error.message);
					}else{
						stripe_checkout.token_received = true;
						stripe_checkout.create_token_input(response.id);
						stripe_checkout.form.submit();
					}
				},
				submit_error: function(message){
					stripe_checkout.token_received = false;
					$( '.woocommerce-error, .woocommerce-message' ).remove();
					stripe_checkout.form.prepend( '<div class="woocommerce-error">' + message + '</div>');
					stripe_checkout.form.removeClass( 'processing' ).unblock();
					stripe_checkout.form.find( '.input-text, select, input:checkbox' ).blur();
					$( 'html, body' ).animate({
						scrollTop: ( stripe_checkout.form.offset().top - 100 )
					}, 1000 );
					$( document.body ).trigger( 'checkout_error' );
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
						stripe_checkout.form.append(input);
					}else{
						$('#stripe_payment_token').val(token);
					}
				},
				create_billing_name: function(){
					var firstname = $('#billing_first_name').val();
					var lastname = $('#billing_last_name').val();
					var fullname = firstname + ' ' + lastname;
					if($('#stripe_billing_name').length == 0){
						var input = document.createElement('input');
						input.type = 'hidden';
						input.id = 'stripe_billing_name';
						input.value = fullname;
						input.setAttribute('data-stripe', 'name');
						stripe_checkout.form.append(input);
					}else{
						$('#stripe_billing_name').val(fullname);
					}
				},
				display_processing: function(){
					var options = stripe_gateway_checkout_vars.card_form_options;
					if(options.card_loader_enabled){
						$('#stripe_gateway_card_container').block({
							message: options.card_loader_html,
							css: options.card_loader_css,
							overlayCSS: {
								background: '#000',
								opacity: 0.6
							}
						})
					}
				},
				hide_processing: function(){
					$('#stripe_gateway_card_container').unblock();
				},
				checkout_error: function(){
					stripe_checkout.hide_processing();
				},
				saved_method_click: function(){
					$('.stripe-payment-method').each(function(){
						$(this).removeClass('active');
					})
					$(this).addClass('active');
					stripe_checkout.update_saved_token($(this).attr('stripe-token'));
				},
				update_saved_token: function(value){
					$('#stripe_saved_method_token').val(value);
				}
		}
		stripe_checkout.init();
	}
	
	$(document.body).on('updated_checkout', Stripe_Checkout.initialize);
	//$(document.body).on('init_add_payment_method', Stripe_Checkout.initialize);
	setInterval(Stripe_Checkout.initialize, 1000);
});