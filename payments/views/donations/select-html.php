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
	<select name="<?php echo $key?>" id="<?php echo $key?>" <?php echo implode(' ', $attributes)?>>
	<?php foreach($data['options'] as $option => $value){?>
		<option value="<?php echo $option?>"><?php echo $value?></option>
	<?php }?>
	</select>
</div>
