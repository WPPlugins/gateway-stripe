jQuery(document).ready(function($){
	var order_edit = {
			maybe_show_capture_box: function(e){
				if($(this).val() === 'capture_stripe_charge'){
					$('#stripe-capture-order-charge').slideDown();
				}else{
					$('#stripe-capture-order-charge').slideUp();
				}
			},
			init: function(){
				$(document.body).on('change', 'select[name="wc_order_action"]', this.maybe_show_capture_box);
			}
	}
	order_edit.init();
});