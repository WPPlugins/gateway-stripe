<?php
/**
 * Payment for which displays the configured payment form and saved payment methods if they exist.
 */
$card_forms = $this->get_credit_card_forms();
$card_form = $card_forms[stripe_manager()->get_option('credit_card_form')];
$user_id = wp_get_current_user()->ID;
$has_methods = stripe_manager()->user_has_saved_methods($user_id);

if (stripe_manager()->get_option('payment_icons_location') === 'inside') {
    include STRIPE_GATEWAY_PAYMENTS . 'views/payment-method-icons.php';
}
?>

<div id="stripe_gateway_card_container" <?php if($has_methods){?>
	style="display: none" <?php }?>>
	<input type="hidden" id="stripe_gateway_initialized" value="false">
	<?php if($has_methods){?>
		<div class="stripe-form-link-container">
		<span class="stripe-form-link use-saved"><?php echo __('Saved Methods', 'stripe_gateway')?></span>
	</div>
	<?php }?>
	<?php include $card_form['dir_path'];?>
</div>