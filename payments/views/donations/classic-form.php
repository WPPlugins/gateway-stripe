<div class="classic-form-container">
	<div class="card-number-wrapper">
		<span class="field-label"><?php echo stripe_manager()->get_option('donation_card_number_label')?></span>
		<input id="stripe-gateway-card-number" class="stripe-input-field" data-stripe="number" placeholder="<?php echo stripe_manager()->get_option('card_number_placeholder')?>">
		<span class="card-type"></span>
	</div>
	<div class="form-group-wrapper multi-fields <?php if(!stripe_manager()->is_active('donation_cvv_field_enabled')){echo 'no-cvv-field';}?>">
		<div class="exp-date-field">
			<span class="field-label"><?php echo stripe_manager()->get_option('donation_card_expiration_date_label')?></span>
			<input id="stripe-gateway-exp-date" class="stripe-input-field exp-date-field" data-stripe="exp" placeholder="<?php echo stripe_manager()->get_option('donation_card_expiration_date_placeholder')?>">
		</div>
		<?php if(stripe_manager()->is_active('donation_cvv_field_enabled')){?>
			<div class="cvv-field">
				<span class="field-label"><?php echo stripe_manager()->get_option('donation_card_cvv_label')?></span>
				<input id="stripe-gateway-cvv" class="stripe-input-field cvv-field" data-stripe="cvc" placeholder="<?php echo stripe_manager()->get_option('donation_card_cvv_placeholder')?>">
				<span class="cvv-image"></span>
			</div>
		<?php }?>
	</div>
	<?php if(stripe_manager()->is_active('donation_postal_field_enabled')){?>
		<div class="form-group-wrapper">
			<div class="postal-field">
				<span class="field-label"><?php echo stripe_manager()->get_option('card_postal_label')?></span>
				<input id="stripe-gateway-postal-code" class="stripe-input-field" data-stripe="address_zip" placeholder="<?php echo stripe_manager()->get_option('donation_card_postal_placeholder')?>">
			</div>
		</div>
	<?php }?>
</div>