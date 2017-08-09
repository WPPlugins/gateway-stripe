<?php
$enabled_fields = $this->get_enabled_donation_fields();

$payment_form = stripe_manager()->get_option('donation_credit_card_form');
$form = $this->donation_payment_forms()[$payment_form];
$accepted_methods = stripe_manager()->get_option('donation_form_card_icons');
$input_field = $this->get_amount_input_field($attrs);
?>
<form method="post" class="stripe-donation-form">
	<input type="hidden" id="stripe_gateway_initialized">
	<div class="donation-fields">
		<?php echo $this->generate_donation_fields($enabled_fields)?>
		<?php echo $this->{'generate_' . $input_field['type'] . '_html'}('donation_amount', $input_field)?>
	</div>
	<div class="stripe-payment-container">
    	<?php if(!empty($accepted_methods)){?>
        	<div class="accepted-payment-methods">
        		<?php foreach($accepted_methods as $k=> $v){?>
        			<span class="stripe-card-icon <?php echo $k?>">
        				<img src="<?php echo STRIPE_GATEWAY_ASSETS . 'img/cards/' . $k . '.png'?>"/>
        			</span>
        		<?php }?>
        	</div>
        <?php }?>
    	<div id="stripe_gateway_card_container">
    		<div class="">
        		<?php include $form['dir_path']?>
        	</div>
    	</div>
    	<div class="donation-submit">
    		<button class="button" id="donation_submit" style="<?php echo stripe_manager()->get_option('donation_button_style')?>"><?php echo stripe_manager()->get_option('donation_button_text')?></button>
    	</div>
    </div>
</form>
