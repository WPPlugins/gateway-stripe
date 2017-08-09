<div class="row">
	<div class="input-field col s12 m8 l8">
		<select id="order_meta_options" class="<?php echo $data['class']?> settings-chip-options">
                			<?php foreach($data['options'] as $option => $value){?>
                				<option value="<?php echo $option?>"><?php echo $value?></option>
                			<?php }?>
		</select>
	</div>
	<div class="input-field col s12 m4 l4">
		<a href="#" id="add_order_meta" field-key="<?php echo $field_key?>" select-id="#order_meta_options" class="waves-effect waves-light btn light-blue lighten-2 add-chip-setting"><?php echo __('Add Meta', 'stripe_gateway')?></a>
	</div>
	<?php $meta_options = $this->get_option($key);?>
    <div class="row"> 
    	<div id="order_meta_container" class="chip-settings-container col s12 m12 l12">
    		<?php foreach($meta_options as $meta_key => $meta){?>
    		  <div class="chip"><?php echo $data['options'][$meta_key]?><input type="hidden" name="<?php echo $field_key . '[' . $meta_key . ']'?>" value=""><i class="remove-settings-chip close material-icons">close</i></div>
    		<?php
    		}?>
    	</div>
    </div>
</div>

