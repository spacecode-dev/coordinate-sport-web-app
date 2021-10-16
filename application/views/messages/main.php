<?php
display_messages();
$data = array(
	'folder' => $folder
);
$this->load->view('messages/tabs.php', $data);
$this->load->view('messages/subtabs.php', $data);
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
		<div class="row">
			<?php
			$hidden_fields = array(
				'search' => 1
			);
			echo form_hidden($hidden_fields);
			?>
				<?php
				switch ($folder) {
					case 'inbox':
						?><div class='col-sm-2'>
							<p>
								<strong><label for="field_from_id">From</label></strong>
							</p>
							<?php
							$options = array(
								'' => 'Select'
							);
							if ($search_to->num_rows() > 0) {
								foreach ($search_to->result() as $row) {
									$options[$row->ID] = $row->name;
								}
							}
							echo form_dropdown('search_from_id', $options, $search_fields['from_id'], 'id="field_from_id" class="select2 form-control"');
							?>
						</div><?php
						break;
					case 'sent':
						?><div class='col-sm-2'>
							<p>
								<strong><label for="field_to_id">To</label></strong>
							</p>
							<?php
							$options = array(
								'' => 'Select'
							);
							if (!is_array($search_to) && $search_to->num_rows() > 0) {
								foreach ($search_to->result() as $row) {
									$options[$row->ID] = $row->name;
								}
							}
							echo form_dropdown('search_to_id', $options, $search_fields['to_id'], 'id="field_to_id" class="select2 form-control"');
							?>
						</div><?php
						break;
				}
				?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_subject">Subject</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_subject',
						'id' => 'field_subject',
						'class' => 'form-control',
						'value' => $search_fields['subject']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_message">Message</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_message',
						'id' => 'field_message',
						'class' => 'form-control',
						'value' => $search_fields['message']
					);
					echo form_input($data);
					?>
				</div>
			<?php if($group == 'archive'){?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_message">Date</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_date',
						'id' => 'field_date',
						'class' => 'form-control datepicker',
						'value' => $search_fields['date']
					);
					echo form_input($data);
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
					Cancel
				</a>
			</div>
		</div>
	<?php echo form_hidden('search', 'true'); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
if ($messages->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No messages found.
		<?php if($folder != 'archive'){?>
			Do you want to <?php echo anchor($add_url, 'create one'); ?>?
		<?php }?>
	</div>
	<?php
} else {
	echo $this->pagination_library->display($page_base);
	echo form_open($submit_to);
	$hidden_fields = array(
		'bulk' => 1
	);
	echo form_hidden($hidden_fields);
	?><div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered bulk-checkboxes'>
				<thead>
					<tr>
						<th class="bulk-checkbox">
							<input type="checkbox" />
						</th>
						<th>
							Subject
						</th>
						<th>
							<?php
							if ($folder == 'inbox') {
								echo 'From';
							} else {
								echo 'To';
							}
							?>
						</th>
						<th>
							Date
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($messages->result() as $row) {
						?>
						<tr>
							<td class="center"><input type="checkbox" name="bulk_messages[]" value="<?php echo $row->messageID; ?>"<?php if (in_array($row->messageID, $bulk_messages)) { echo ' checked="checked"'; } ?>></td>
							<td class="name">
								<?php
								if ($row->status == 0) {
									echo "<i class='far fa-envelope'></i> ";
								}
								if ($row->attachments > 0) {
									echo "<i class='far fa-paperclip '></i> ";
								}
								echo anchor('messages/view/'.$group .'/'. $row->messageID, $row->subject);
								?>
							</td>
							<td>
								<?php
								switch ($group) {
									case "staff":
										echo $row->first . ' ' . $row->surname;
										break;
									case "participants":
										echo $row->first_name . ' ' . $row->last_name;
										break;
									case 'schools':
									case 'organisations':
										echo empty($row->contact_name) ? $row->name : $row->contact_name;
										break;
								}
								 ?>
							</td>
							<td>
								<?php echo mysql_to_uk_datetime($row->added); ?>
							</td>
							<td>
								<div class='text-right'>
									<?php if ($this->auth->user->department == 'directors' && $folder == 'inbox' && $this->auth->account->admin == 1) { ?>
										<a class='btn btn-info btn-sm confirm-forward' href='<?php echo site_url('messages/forward/' . $row->messageID); ?>' title="Forward to Support">
											<i class="far fa-envelope"></i>
										</a>
									<?php } ?>
									<?php
										if ($this->auth->account->admin == 1 || $this->auth->user->accountID == $row->accountID) {
									?>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('messages/'.$group.'/remove/' . $row->messageID); ?>' title="Remove">
										<i class='far fa-trash'></i>
									</a>
									<?php } ?>
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
	<br />
	<div class="row bulk-actions">
		<div class="col-sm-2">
			<?php
			$options = array(
				'delete' => 'Delete',
				'read' => 'Mark Read',
				'unread' => 'Mark Unread',
				'archive' => 'Archive'
			);
			if ($folder == 'archive') {
				unset($options['archive']);
			}
			if ($folder == 'sent') {
				unset($options['read']);
				unset($options['unread']);
			}

			// sort
			asort($options);

			$options = array(
				'' => 'Bulk Action'
			) + $options;

			echo form_dropdown('action', $options, set_value('action'), 'id="action" class="select2 form-control"');
			?>
		</div>
		<div class="col-sm-2">
			<button class='btn btn-primary btn-submit' type="submit">
				Go
			</button>
		</div>
	</div><?php
	echo form_close();
	echo $this->pagination_library->display($page_base);
}
