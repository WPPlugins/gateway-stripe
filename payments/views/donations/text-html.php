<div class="field-container">
	<div>
		<label><?php echo $data['label']?></label>
	</div>
	<?php
    $attributes = array();
    foreach ($data['attributes'] as $k => $v) {
        $attributes[] = $k . '="' . $v . '"';
    }
    ?>
	<input type="text" name="<?php echo $key?>" id="<?php echo $key?>"
		placeholder="<?php echo $data['placeholder']?>"
		<?php echo implode(' ', $attributes)?>>
</div>
