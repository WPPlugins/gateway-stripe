<?php
include STRIPE_GATEWAY_ADMIN . 'views/admin-header.php';
stripe_manager()->log->initialize_log_entries(); //Logs need to be loaded.
?>
<main>
	<section>
		<div class="container">
    		<div class="row">
    			<div class="col s12 m12 l12">
    				<h1 class="thin"><?php echo __('Log Entries', 'stripe_gateway')?></h1>
    			</div>
    		</div>
		</div>
	</section>
	<div class="container">
		<div class="row">
			<div class="col s12 m12 l12">
				<form method="post">
					<input type="hidden" name="stripe_gateway_delete_logs">
					<button class="btn right"><?php echo __('Delete Logs', 'stripe_gateway')?></button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col s12 m12 l12">
				<div class="card">
					<div class="card-content">
						<div class="card-title">
							<span><?php echo __('Logs', 'stripe_gateway')?></span>
						</div>
						<table class="bordered log-entries" id="log-entries">
    						<thead class="grey lighten-3">
    							<tr>
    								<th><?php echo __('Type', 'stripe_gateway')?></th>
    								<th><?php echo __('Time', 'stripe_gateway')?></th>
    								<th><?php echo __('Message', 'stripe_gateway')?></th>
    							</tr>
    						</thead>
    						<tbody>
    						<?php foreach(stripe_manager()->log->logs as $log){
    						          foreach($log as $entry){
    						              if(!empty($entry)){
    						              $type = $entry['type']
    						              ?>
                							<tr>
                								<td class="<?php if($type === 'success'){ echo 'green lighten-4';}elseif($type === 'error'){echo 'red lighten-4';}elseif($type === 'info'){echo 'blue lighten-4';}?>"><?php echo $entry['type']?></td>
                								<td class="time-td"><?php echo $entry['time']?></td>
                								<td><div class="log-message"><?php echo $entry['message']?></div></td>
                							</tr>
        							<?php }
    						          }
        						} ?>
    						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
