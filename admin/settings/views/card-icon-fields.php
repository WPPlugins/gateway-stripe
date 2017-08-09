<div class="row">
	<div class="input-field col s12 m8 l8">
		<select id="card_icon_options" class="<?php echo $data['class']?> settings-chip-options">
                			<?php foreach($data['options'] as $option => $value){?>
                				<option value="<?php echo $option?>"><?php echo $value?></option>
                			<?php }?>
		</select>
	</div>
	<div class="input-field col s12 m4 l4">
		<a href="#" id="add_card_type" field-key="<?php echo $field_key?>" select-id="#card_icon_options" class="waves-effect waves-light btn light-blue lighten-2 add-chip-setting"><?php echo __('Add Card', 'stripe_gateway')?></a>
	</div>
	<?php $card_options = $this->get_option($key);?>
    <div class="row"> 
    	<div id="card_type_container" class="chip-settings-container col s12 m12 l12">
    		<?php 
    		if(!empty($card_options)){
        		foreach($card_options as $card => $type){?>
        		  <div class="chip"><?php echo $data['options'][$card]?><input type="hidden" name="<?php echo $field_key . '[' . $card . ']'?>" value=""><i class="remove-settings-chip close material-icons">close</i></div>
        		<?php
    		    }
    		}?>
    	</div>
    </div>
</div>