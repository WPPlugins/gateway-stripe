<?php
use Stripe\Card;
use Stripe\BankAccount;
if (is_user_logged_in()) {
    $user_id = wp_get_current_user()->ID;
    $methods = stripe_manager()->get_stripe_source_objects($user_id);
    $last_used = stripe_manager()->get_last_used_payment_method($user_id);
    if (!empty($methods)) {
        ?>
        <div class="" id="stripe_gateway_saved_methods">
        	<input type="hidden" id="stripe_saved_method_token" name="stripe_saved_method_token"/>
        	<div class="stripe-form-link-container">
        		<span class="stripe-form-link use-new"><?php echo __('Use New', 'stripe_gateway')?></span>
        	</div>
        <?php
        foreach ($methods as $method) {
          if($method instanceof Card){
              ?>
              <div class="stripe-payment-method <?php if($method->id === $last_used){echo 'active';}?>" stripe-token="<?php echo $method->id?>">
              	<span class="payment-method <?php echo $method->brand?>">
              		<img src="<?php echo STRIPE_GATEWAY_ASSETS . 'img/' . preg_replace('/\s/', '', $method->brand) . '.png'?>"/>
              	</span>
              	<span class="method-description"><?php echo sprintf('******** %s', $method->last4)?></span>
              </div>
              <?php
          }elseif ($method instanceof BankAccount){
              ?>
        	  <div class="stripe-payment-method <?php if($method->id === $last_used){echo 'active';}?>" stripe-token="<?php echo $method->id?>">
        		<span class="payment-method">bank</span> <span
        			class="method-description"><?php echo sprintf('%s ***** %s', $method->bank_name, $method->last4)?></span>
        	  </div>
        	  <?php
	       }
        }
        ?>
        </div>
        <?php
    }
}