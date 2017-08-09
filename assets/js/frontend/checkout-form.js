jQuery(document).ready(function($){
	var Stripe_Checkout = function(){};
	Stripe_Checkout.initialize = function(){
		var checkout = {
				init: function(){
					var val = $('#stripe_gateway_initialized').val();
					if(val == null){
						return;
					}
					if(val === 'true'){
						return;
					}
					$('#stripe_gateway_initialized').val('true');
					if(this.has_saved_methods()){
						this.display_saved_methods();
					}
					this.handler = this.configure_stripe();
					$('#stripe_checkout_button').off();
					$('#stripe_checkout_button').on('click', this.open_handler);
					this.$form = $('#stripe_gateway_card_container').closest('form');
					this.$form.on('checkout_place_order_' + stripe_checkout_vars.gateway, this.checkout_place_order);
					$(document.body).on('click', '.stripe-payment-method', this.saved_method_click);
					$(document.body).on('click', '.stripe-form-link.use-new', this.display_container);
					$(document.body).on('click', '.stripe-form-link.use-saved', this.display_saved_methods);
					$(document.body).on('checkout_error', this.checkout_error);
					this.$container = $('#stripe_gateway_card_container');
					this.$saved_methods = $('#stripe_gateway_saved_methods');
				},
				configure_stripe: function(){
					var options, handler;
					options = {};
					$.each(stripe_checkout_vars.options, function(key, value){
						options[key] = value;
					});
					options.token = checkout.payment_method_received;
					handler = StripeCheckout.configure(options);
					return handler;
				},
				
				payment_method_received: function(token, args){
					$('#stripe_payment_token').val(token.id);
					$('#stripe_payment_type').val(token.type);
					checkout.token_received = true;
					checkout.handler.close();
					checkout.$form.submit();
				},
				open_handler: function(e){
					var options = {};
					e.preventDefault();
					if($('#billing_email').length && $('#billing_email').val() != ''){
						options.email = $('#billing_email').val();
					}
					checkout.handler.open(options);
				},
				saved_method_selected: function(){
					return $('#stripe_saved_method_token').length && $('#stripe_saved_method_token').val().length > 0;
				},
				checkout_place_order: function(){
					if(checkout.saved_method_selected()){
						return true;
					}else{
						if(checkout.token_received){
							return true;
						}else{
							return false;
						}
					}
				},
				saved_method_click: function(){
					$('.stripe-payment-method').each(function(){
						$(this).removeClass('active');
					})
					$(this).addClass('active');
					checkout.update_saved_token($(this).attr('stripe-token'));
				},
				display_container: function(){
					checkout.$saved_methods.slideUp(400, 'linear', checkout.$container.slideDown());
					checkout.update_saved_token('');
				},
				update_saved_token: function(value){
					$('#stripe_saved_method_token').val(value);
				},
				display_saved_methods: function(){
					$('#stripe_gateway_card_container').slideUp(400, 'linear', $('#stripe_gateway_saved_methods').slideDown());
					checkout.update_saved_token($('.stripe-payment-method.active').attr('stripe-token'));
				},
				checkout_error: function(){
					checkout.token_receieved = false;
				},
				has_saved_methods: function(){
					return $('#stripe_gateway_saved_methods').length > 0;
				},
		}
		checkout.init();
	};
	setInterval(Stripe_Checkout.initialize, 1000);
})