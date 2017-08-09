<tr>
	<th class="title-description">
		<?php echo $data['title']?>
		<?php echo $this->get_tooltip_html($data)?>
		<?php echo $this->generate_helper_modal($key, $data);?>
    </th>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<button class="<?php echo $data['class']?>"
					name="<?php echo $field_key?>" id="<?php echo $field_key?>"
					value="<?php echo $data['value']?>"
					<?php sg_get_html_field_attributes($data['attributes'], true)?>><?php echo $data['label']?>
				</button>
			</div>
		</div>
	</td>
</tr>