
<a href="#stripe-card-form-modal" class="modal-trigger medium-text <?php echo $data['class']?>"><?php echo __('Custom Form Options', 'stripe_gateway')?></a>
<div class="modal" id="stripe-card-form-modal">
	<div class="modal-content">
		<h4 class="thin"><?php echo __('Credit Card Form Options', 'stripe_gateway')?></h4>
		<table class="">
			<tbody>
			<?php $this->generate_settings_html(true)?>
			</tbody>
		</table>
	</div>
	<div class="modal-footer">
		<a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat"><?php echo __('Close', 'stripe_gateway')?></a>
	</div>
</div>
<?php
