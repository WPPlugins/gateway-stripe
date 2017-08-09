<?php
/**
 * Payment form which is displayed on the add payment page.
*/
$card_forms = $this->get_credit_card_forms();
$card_form = $card_forms[stripe_manager()->get_option('credit_card_form')];
?>
<div id="stripe_gateway_card_container">
	<input type="hidden" id="stripe_gateway_initialized" value="false">
	<?php include $card_form['dir_path'];?>
</div>