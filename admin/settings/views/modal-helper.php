<i class="material-icons modal-trigger right" href="#<?php echo $field_key . '_modal'?>">featured_video</i>
<div class="modal" id="<?php echo $field_key . '_modal'?>">
	<div class="modal-content">
		<h4><?php echo $data['title']?></h4>
		<p class="small">
			<?php echo $data['helper']['description']?>
		</p>
		<p>
			<img class="modal-helper-img" src="<?php echo $data['helper']['url']?>" />
		</p>
	</div>
	<div class="modal-footer">
		<a href="#!"
			class=" modal-action modal-close waves-effect waves-green btn blue lighten-2"><?php echo __('Close', 'stripe_gateway')?></a>
	</div>
</div>