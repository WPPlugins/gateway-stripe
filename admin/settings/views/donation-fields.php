<div class="row">
	<div class="input-field col s12 m8 l8">
		<select id="donation-fields" class="<?php echo $data['class']?> settings-chip-options">
			<?php foreach($data['options'] as $option => $value){?>
				<option value="<?php echo $option?>"><?php echo $value?></option>
			<?php }?>
		</select>
	</div>
	<div class="input-field col s12 m4 l4">
		<a href="#" id="add_donation_field" field-key="<?php echo $field_key?>" select-id="#donation-fields" class="waves-effect waves-light btn light-blue lighten-2 add-chip-setting"><?php echo __('Add Field', 'stripe_gateway')?></a>
	</div>
	<?php $donation_fields = $this->get_option($key);?>
    <div class="row"> 
    	<div class="chip-settings-container col s12 m12 l12">
    		<?php 
    		if(!empty($donation_fields)){
        		foreach($donation_fields as $k => $v){?>
        		  <div class="chip"><?php echo $data['options'][$k]?><input type="hidden" name="<?php echo $field_key . '[' . $k . ']'?>" value=""><i class="remove-settings-chip close material-icons">close</i></div>
        		<?php
    		    }
    		}?>
    	</div>
    </div>
</div>