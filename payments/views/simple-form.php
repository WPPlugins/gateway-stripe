<div class="simple-form">
	<div class="form-group">
		<label><?php echo __(stripe_manager()->get_option('card_number_label'))?></label>
		<input id="stripe-gateway-card-number" class="input-field" data-stripe="number" placeholder="<?php echo stripe_manager()->get_option('card_number_placeholder')?>"/>
	</div>
	<div class="form-group">
		<label><?php echo __(stripe_manager()->get_option('card_expiration_date_label'))?></label>
		<input id="stripe-gateway-exp-date" class="input-field" data-stripe="exp" placeholder="<?php echo stripe_manager()->get_option('card_expiration_date_placeholder')?>"/>
	</div>
	<?php if(stripe_manager()->is_active('cvv_field_enabled')){?>
		<div class="form-group">
			<label><?php echo __(stripe_manager()->get_option('card_cvv_label'))?></label>
			<input id="stripe-gateway-cvv" class="input-field"  data-stripe="cvc" placeholder="<?php echo stripe_manager()->get_option('card_cvv_placeholder')?>"/>
		</div>
	<?php }?>
	<?php if(stripe_manager()->is_active('postal_field_enabled')){?>
		<div class="form-group">
			<label><?php echo __(stripe_manager()->get_option('card_postal_label'))?></label>
			<input id="stripe-gateway-postal-code" class="input-field" data-stripe="address_zip" placeholder="<?php echo stripe_manager()->get_option('card_postal_placeholder')?>"/>
		</div>
	<?php }?>
</div>