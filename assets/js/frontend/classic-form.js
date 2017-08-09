jQuery(document).ready(function($){
	var classic_form = {
			init: function(){
				$(document.body).on('keyup', '.stripe-input-field', classic_form.key_up);
			},
			key_up: function(e){
				var span = $(this).prev('span');
				if($(this).val() != ''){
					if(! span.hasClass('active')){
						span.addClass('active');
					}
				}else{
					span.removeClass('active');
				}
			},
			field_blur: function(e){
				//$(this).closest('span').addClass('active');
			}
	}
	
	$(document.body).on('stripe_gateway_initialized', classic_form.init());
})