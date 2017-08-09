<?php 
$method_icons = stripe_manager()->get_option('card_form_icons');
?>
<div id="stripe_accepted_methods">
	<?php if(!empty($method_icons)){
    	foreach($method_icons as $k => $v){?>
    		<span class="stripe-card-icon <?php echo $k?>">
    			<img src="<?php echo STRIPE_GATEWAY_ASSETS . 'img/cards/' . $k . '.png'?>"/>
    		</span>
    	<?php 
    	}
	}?>
</div>