<?php
/**
 * Payment for which displays the configured payment form and saved payment methods if they exist.
 */

if (stripe_manager()->get_option('payment_icons_location') === 'inside') {
    include STRIPE_GATEWAY_PAYMENTS . 'views/payment-method-icons.php';
}
?>

<div id="stripe_gateway_card_container">
	<input type="hidden" id="stripe_gateway_initialized" value="false">
	<input type="hidden" id="stripe_payment_token" name="stripe_payment_token">
	<input type="hidden" id="stripe_payment_type" name="stripe_payment_type">
	<div class="stripe-checkout-button">
		<button id="stripe_checkout_button" class="stripe-button-el">
			<span><?php echo stripe_manager()->get_option('checkout_add_payment_button_label')?></span>
		</button>
	</div>
</div>