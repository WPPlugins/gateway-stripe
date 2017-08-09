<?php
/**
 * Payment for which displays the configured payment form and saved payment methods if they exist.
 */
$user_id = wp_get_current_user()->ID;
$has_methods = stripe_manager()->user_has_saved_methods($user_id);

if (stripe_manager()->get_option('payment_icons_location') === 'inside') {
    include STRIPE_GATEWAY_PAYMENTS . 'views/payment-method-icons.php';
}
?>

<div id="stripe_gateway_card_container" <?php if($has_methods){?>
	style="display: none" <?php }?>>
	<input type="hidden" id="stripe_gateway_initialized" value="false">
	<input type="hidden" id="stripe_payment_token" name="stripe_payment_token">
	<input type="hidden" id="stripe_payment_type" name="stripe_payment_type">
	<?php if($has_methods){?>
		<div class="stripe-form-link-container">
			<span class="stripe-form-link use-saved"><?php echo __('Saved Methods', 'stripe_gateway')?></span>
		</div>
	<?php }?>
	<div class="stripe-checkout-button">
		<button id="stripe_checkout_button" class="stripe-button-el">
			<span><?php echo stripe_manager()->get_option('checkout_button_label')?></span>
		</button>
	</div>
</div>