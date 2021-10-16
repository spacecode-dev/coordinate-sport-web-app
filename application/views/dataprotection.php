<?php
$data = array(
	'tab' => $tab,
);
$this->load->view('tabs_export.php', $data);

$form_classes = 'card card-custom card-search';

echo form_open($page_base, ['class' => $form_classes]); ?>
	<div class="card-header">
		<div class="card-title">
			<h3 class="card-label">Details</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='row'>
			<div class='col-sm-12 form-group'>
				<strong><label for="data_protection_officer">Data Protection Officer(s)</label></strong>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($staff->result() as $result) {
					$options[$result->staffID] = $result->first." ".$result->surname;
				}
				
				echo form_dropdown('data_protection_officer[]', $options, $dpo_data['data_protection_officer'] , 'id="data_protection_officer" multiple class="select2 form-control"');
				?>
			</div>
			
			<div class='col-sm-12 form-group'>
				<strong><label for="data_protection_officer_send_notification">Send Notification to</label></strong>
				<?php
				$options = array(
					'' => 'Select'
				);
				
				foreach ($staff->result() as $result) {
					$options[$result->staffID] = $result->first." ".$result->surname;
				}
				
				echo form_dropdown('data_protection_officer_send_notification[]', $options, $dpo_data['data_protection_officer_send_notification'] , 'id="data_protection_officer_send_notification" class="select2 form-control" multiple');
				?>
			</div>
		</div>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' name="save" type="submit">
				<i class='far fa-save'></i> Save
			</button>
		</div>
	</div>
<?php echo form_close(); ?>