<div>
	<h3><?php echo __('Charge Amount')?></h3>
	<p>
		<input type="text" name="stripe_capture_amount"
			value="<?php echo $order->get_total()?>" placeholder="<?php echo $order->get_total()?>">
	</p>
</div>
