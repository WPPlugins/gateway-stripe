<tr class="top">
	<th class="title-description">
		<?php echo $data['title']?>
		<?php echo $this->get_tooltip_html($data)?>
		<?php echo $this->generate_helper_modal($key, $data);?>
	</th>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<select name="<?php echo $field_key?>" id="<?php echo $field_key?>"
					class="<?php echo $data['class']?>" <?php sg_get_html_field_attributes($data['attributes'], true)?>>
                        			<?php foreach($data['options'] as $option => $value){?>
                        				<option value="<?php echo $option?>"
						<?php selected($option, $this->get_option($key))?>><?php echo $value?></option>
                        			<?php }?>
                        		</select>
			</div>
		</div>
	</td>
</tr>