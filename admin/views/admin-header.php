<nav>
	<div class="nav-wrapper">
		<a href="#" class="brand-logo left">
			<img src="<?php echo STRIPE_GATEWAY_ASSETS . 'img/stripe-logo.png'?>"/>
		</a>
		<a href="#" data-activates="stripe-mobile-nav" class="button-collapse right"><i class="material-icons">menu</i></a>
		<ul class="hide-on-med-and-down right">
			<li class="<?php if($current_page === 'stripe-gateway-settings' && empty($current_tab)){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings'?>"><?php echo __('Gateway', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'checkout-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=checkout-settings'?>"><?php echo __('WooCommerce', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'subscription-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=subscription-settings'?>"><?php echo __('Subscriptions', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'donation-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=donation-settings'?>"><?php echo __('Donation Settings', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'license-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=license-settings'?>"><?php echo __('License', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_page === 'stripe-gateway-logs'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-logs'?>"><?php echo __('Log Entries', 'stripe_gateway')?></a>
			</li>
		</ul>
		<ul class="side-nav" id="stripe-mobile-nav">
			<li class="<?php if($current_page === 'stripe-gateway-settings' && empty($current_tab)){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings'?>"><?php echo __('Gateway', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'checkout-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=checkout-settings'?>"><?php echo __('WooCommerce Settings', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'subscription-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=subscription-settings'?>"><?php echo __('WooCommerce Subscriptions', 'stripe_gateway')?></a>
			</li>
			
			<li class="<?php if($current_tab === 'donation-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=donation-settings'?>"><?php echo __('Donation Settings', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_tab === 'license-settings'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=license-settings'?>"><?php echo __('License', 'stripe_gateway')?></a>
			</li>
			<li class="<?php if($current_page === 'stripe-gateway-logs'){?>active<?php }?>">
				<a href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-logs'?>"><?php echo __('Log Entries', 'stripe_gateway')?></a>
			</li>
		</ul>
	</div>
</nav>
<?php if(stripe_manager()->has_admin_notices()){?>
<div class="container">
	<div class="row">
		<div class="col s12">
			<?php stripe_manager()->display_admin_notices(true);?>
		</div>
	</div>
</div>
<?php }?>