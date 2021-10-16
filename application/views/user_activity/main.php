<?php
display_messages();
echo form_open($page_base, ['method' => 'get']);
	?><div class='card card-custom card-search<?php if ($search_fields['search'] == '') { echo " card-collapsed"; } ?>'>
		<div class="card-header">
			<div class="card-title">
				<h3 class="card-label">Search</h3>
			</div>
			<div class="card-toolbar">
				<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_date_from">Date From</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_date_from',
						'id' => 'field_date_from',
						'class' => 'form-control datepicker',
						'value' => $search_fields['date_from']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_date_to">Date To</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_date_to',
						'id' => 'field_date_to',
						'class' => 'form-control datepicker',
						'value' => $search_fields['date_to']
					);
					echo form_input($data);
					?>
				</div>
				<?php if (!in_array($this->auth->user->department, ['fulltimecoach', 'coaching'])){?>
					<div class='col-sm-2'>
						<p>
							<strong><label for="field_staff_id">Staff</label></strong>
						</p>
						<?php
						$options = [];
						if (count($staff_list) > 0) {
							foreach ($staff_list as $id => $staff) {
								$options[$id] = $staff;
							}
						}
						echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
						?>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class='card-footer'>
			<div class="d-flex justify-content-between">
				<button class='btn btn-primary btn-submit' type="submit">
					<i class='far fa-search'></i> Search
				</button>
				<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
					Reset
				</a>
			</div>
		</div>
	</div>
	<div id="results"></div>
	<?php echo $this->pagination_library->display($page_base); ?>
	<?php
	if ($records['Count'] < 1) {
		?>
		<div class="alert alert-info">
			No data found.
		</div>
		<?php
	} else {
		?><div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered' id="user-activity">
					<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Activity
						</th>
						<th>
							Page
						</th>
					</tr>
					</thead>
					<tbody>
						<?php foreach ($records['Items'] as $log) {
							$log = $this->activity_library->unmarshal($log);
							if (in_array($this->auth->user->department, ['fulltimecoach', 'coaching'])){
								if ($log['user_id'] != $this->auth->user->staffID) {
									continue;
								}
							}
							?>
							<tr>
								<td>
									<?php echo(date('d/m/Y H:i:s', $log['created_at'])); ?>
								</td>
								<td>
									<?php echo($log['info']['action']); ?>
								</td>
								<td>
									<?php echo($log['info']['page_name']); ?> -
									<a href="<?php echo(site_url($log['info']['url'])) ?>" target="_blank"><?php echo(site_url($log['info']['url'])) ?></a>
								</td>
							</tr>
						<?php
							}
						?>
					</tbody>
				</table>
			</div>
		</div><?php
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<?php
		if ($this->input->get('last_key') && !$this->input->get('search')) {
			?><a href="#" class="history-back btn btn-primary">Previous</a> <?php
		}
		if (!empty($last_key)) {
			echo form_hidden('last_key', $last_key);
			$data = [
				'name' => 'next',
				'value' => 'Next',
				'class' => 'btn btn-primary'
			];
			echo form_submit($data);
		}
		?>
	</div><?php
echo form_close();
