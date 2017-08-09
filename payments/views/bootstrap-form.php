<div class="row">
	<div class="form-group col-xs-8">
		<label class="control-label"><?php echo stripe_manager()->get_option('card_number_label')?></label>
		<!--  Hosted Fields div container -->
		<input class="form-control stripe-input-field" id="stripe-gateway-card-number" data-stripe="number" placeholder="<?php echo stripe_manager()->get_option('card_number_placeholder')?>"/>
		<span class="helper-text"></span>
	</div>
	<div class="form-group col-xs-4">
		<div class="row">
			<div class="col-xs-6">
				<label class="control-label col-xs-12"><?php echo stripe_manager()->get_option('card_expiration_month_label')?></label>
				<input class="form-control stripe-input-field" id="stripe-gateway-exp-month" data-stripe="exp_month" placeholder="<?php echo stripe_manager()->get_option('card_expiration_month_placeholder')?>">
			</div>
			<div class="col-xs-6">
				<label class="control-label col-xs-12"><?php echo stripe_manager()->get_option('card_expiration_year_label')?></label>
				<input class="form-control stripe-input-field" id="stripe-gateway-exp-year" data-stripe="exp_year" placeholder="<?php echo stripe_manager()->get_option('card_expiration_year_placeholder')?>"/>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<?php if(stripe_manager()->is_active('cvv_field_enabled')){?>
		<div class="form-group col-xs-6">
			<label class="control-label"><?php echo stripe_manager()->get_option('card_cvv_label')?></label>
			<!--  Hosted Fields div container -->
			<input class="form-control stripe-input-field" id="stripe-gateway-cvv" data-stripe="cvc" placeholder="<?php echo stripe_manager()->get_option('card_cvv_placeholder')?>">
		</div>
	<?php }?>
	<?php if(stripe_manager()->is_active('postal_field_enabled')){?>
		<div class="form-group col-xs-6">
			<label class="control-label"><?php echo stripe_manager()->get_option('card_postal_label')?></label>
			<!--  Hosted Fields div container -->
			<input class="form-control stripe-input-field" id="stripe-gateway-postal-code" data-stripe="address_zip" placeholder="<?php echo stripe_manager()->get_option('card_postal_placeholder')?>">
		</div>
	<?php }?>
</div>
