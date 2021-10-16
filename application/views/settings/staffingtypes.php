<?php
display_messages();
?>
<div class='card card-custom'>
	<div class='table-responsive'>
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
					<th>
						Name
					</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($original_types as $key => $name) {
					if (array_key_exists($key, $type_labels) && !empty($type_labels[$key])) {
						$name = $type_labels[$key];
					}
					?>
					<tr>
						<td class="name">
							<?php echo anchor('settings/staffingtypes/edit/' . $key, $name); ?>
						</td>
						<td>
							<div class='text-right'>
								<a class='btn btn-warning btn-sm' href='<?php echo site_url('settings/staffingtypes/edit/' . $key); ?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</div>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>
