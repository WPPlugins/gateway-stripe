<tr>
	<th class="title-description">
        	<?php echo $data['title']?>
            <?php echo $this->get_tooltip_html($data)?>
            </th>
	<td><input type="radio" class="<?php echo $data['class']?>"
		name="<?php echo $field_key?>" id="<?php echo $field_key?>"
		<?php checked($data['value'], $this->get_option($key))?>
		<?php sg_get_html_field_attributes($data['attributes'], true)?> /></td>
</tr>