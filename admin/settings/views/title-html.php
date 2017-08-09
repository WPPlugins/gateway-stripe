<section class="">
	<div class="container">
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<h1 class="<?php echo $data['class']?>"><?php echo $data['title']?></h1>
				<?php if(!empty($data['description'])){?>
					<p><?php echo $data['description']?></p>
				<?php }?>
			</div>
		</div>
	</div>
</section>