<div>
	<p>
		<?php echo __('If after reading through the help text and following the instructions on each settings page of the plugin you have questions, please contact us at <a href="mailto:support@paymentplugins.com">support@paymentplugins.com</a>', 'stripe_gateway')?>
	</p>
</div>
<div>
	<ol>
		<li>
			<h4><?php echo __('Gateway Settings', 'stripe_gateway')?></h4>
			<p>
				<?php echo __('On this page, you can configure your Stripe API keys. These API keys are required in order for your Wordpress site to communicate with Stripe. This plugin allows you to configure your Live and Test api keys. If you have enabled Test mode in Stripe, you can run test transactions while setting up your installation.', 'stripe_gateway')?>
			</p> <span> <a class="button-primary" target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings'?>"><?php echo __('Gateway Settings', 'stripe_gateway')?></a>
		</span><span><a class="button" href="https://stripe.com/docs/testing#cards" target="_blank"><?php echo __('Stripe Test Cards', 'stripe_gateway')?></a></span>
		</li>
		<li>
			<h4><?php echo __('WooCommerce Settings')?></h4>
			<p>
				<?php echo __('On this page, you can maintain any settings that are specific to WooCommerce. These settings include the credit card form options, text that displays on the checkout page, etc.', 'stripe_gateway')?>
			</p> <span> <a class="button-primary" target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=checkout-settings'?>"><?php echo __('WooCommerce Settings', 'stripe_gateway')?></a>
		</span>
		</li>
		<li>
			<h4><?php echo __('WooCommerce Subscriptions', 'stripe_gateway')?></h4>
			<p>
				<?php echo __('On this page, you can maintain any settings specific to WooCommerce Subscriptions. If you don\'t have that plugin installed or don\'t sell subscriptions, then disregard this page.', 'stripe_gateway')?>
			</p> <span> <a class="button-primary" target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=subscription-settings'?>"><?php echo __('WooCommerce Subscriptions', 'stripe_gateway')?></a>
		</span>
		</li>
		<li>
			<h4><?php echo __('Debug Logs', 'stripe_gateway')?></h4>
			<p>
				<?php echo __('On this page, you can view logs which contains captured information pertaining to transactions, customer creation, etc. This log is very useful when troubleshooting any errors you might encounter.', 'stripe_gateway')?>
			</p> <span> <a class="button-primary" target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-logs'?>"><?php echo __('Log Entries', 'stripe_gateway')?></a>
		</span>
		</li>
		<li>
			<h4><?php echo __('Donation Settings', 'stripe_gateway')?></h4>
			<p>
				<?php echo __('On this page you can configure donation settings such as the type of payment form you want to render, the fields that should appear, etc. Use short code <strong>[stripe_gateway_donations]</strong> on any page that you want to accept donations.If you don\'t accept donations on your site then disregard this page.', 'stripe_gateway')?>
			</p> <span> <a class="button-primary" target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=donation-settings'?>"><?php echo __('Donation Settings', 'stripe_gateway')?></a>
		</span>
		</li>
		<li>
			<h4><?php echo __('Activate License', 'stripe_gateway')?></h4>
			<p>
				<?php echo __('On this page you can activate your license purchased from <a href="https://wordpress.paymentplugins.com/product/stripe-gateway/" target="_blank">Payment Plugins</a>. Once activated, your can being to accept live transactions. Without a license you can test using your Stripe test environment.', 'stripe_gateway')?>
			</p> <span> <a class="button-primary" target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=stripe-gateway-settings&tab=license-settings'?>"><?php echo __('License Settings', 'stripe_gateway')?></a>
		</span>
		</li>
	</ol>
</div>
