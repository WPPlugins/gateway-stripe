<tr class="top">
	<th class="title-description">
		<?php echo $data['title']?>
		<?php echo $this->get_tooltip_html($data)?>
		<?php echo $this->generate_helper_modal($key, $data)?>
	</th>
	<td>
		<?php  call_user_func_array($data['function'], array($key, $data))?>
	</td>
</tr>