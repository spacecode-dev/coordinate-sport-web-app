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
				foreach ($original_levels as $key => $name) {
					if (array_key_exists($key, $level_labels) && !empty($level_labels[$key])) {
						$name = $level_labels[$key];
					}
					?>
					<tr>
						<td class="name">
							<?php
							if ($key != 'directors') {
								echo anchor('settings/permissionlevels/edit/' . $key, $name);
							} else {
								echo $name;
							}
							?>
						</td>
						<td>
							<div class='text-right'>
								<?php
								if ($key != 'directors') {
									?><a class='btn btn-warning btn-sm' href='<?php echo site_url('settings/permissionlevels/edit/' . $key); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a><?php
								}
								?>
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
