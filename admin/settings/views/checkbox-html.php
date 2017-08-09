<tr>
	<th class="title-description">
    	<?php echo $data['title']?>
    	<?php echo $this->get_tooltip_html($data)?>
    	<?php echo $this->generate_helper_modal($key, $data)?>
    </th>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<p>
					<input type="checkbox" name="<?php echo $field_key?>"
						id="<?php echo $field_key?>" class="<?php echo $data['class']?>"
						value="<?php echo $data['value']?>"
						<?php checked($data['value'], $this->get_option($key))?> <?php sg_get_html_field_attributes($data['attributes'], true)?>> <label
						for="<?php echo $field_key?>"><?php echo $data['title']?></label>
				</p>
			</div>
		</div>
	</td>
</tr>