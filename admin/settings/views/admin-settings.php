<?php include STRIPE_GATEWAY_ADMIN . 'views/admin-header.php';?>
<main>
	<?php echo $this->display_settings_title()?>
	<div class="container">
		<div class="card">
			<div class="card-content">
				<div class="row">
        			<form class="col s12 m12 l12" method="POST">
        				<div class="row">
        					<div class="col s12 m12 l12">
                				<table class="stripe-settings-table">
                					<tbody>
                						<?php
                						if (! empty($current_tab)) {
            						      do_action('output_stripe_settings_page_' . $current_tab, true);
        								}else{
        								    echo $this->generate_settings_html(false);
        								}
        								?>
                					</tbody>
                				</table>
                			</div>
                		</div>
        				<div class="row">
        					<div class="input-field col s12 m12 l12">
        						<button class="waves-effect waves-light btn"><?php echo __('Save', 'stripe_gateway')?></button>
        					</div>
        				</div>
        			</form>
				</div>
			</div>
		</div>
	</div>
</main>