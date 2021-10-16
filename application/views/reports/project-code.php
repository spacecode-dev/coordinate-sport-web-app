<?php
/**
 * @var $payroll_data CI_DB_result
 * @var $staff CI_DB_result
 * @var $quals array
 * @var $staff_list CI_DB_result
 * @var $payroll_data array
 * @var $search_fields array
 * @var $page_base string
 */

display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'project-code-report-search']); ?>
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
			<div class='col-sm-2'>
				<p>
					<strong><label for="project_codes">Project Code</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($project_codes->num_rows() > 0) {
					foreach ($project_codes->result() as $row) {
						$options[$row->codeID] = $row->code;
					}
				}
				echo form_dropdown('search_project_code', $options, $search_fields['project_code'], 'id="project_codes" class="select2 form-control"');
				?>
			</div>
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
if (count($total) == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
				<tr>
					<th>Project Code</th>
					<th>Total Spend (<?php echo currency_symbol(); ?>)</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$totalValue = 0;
				foreach ($total as $key => $value) {
					?>
					<tr>
						<td>
							<?php echo $key; ?>
						</td>
						<td>
							<?php
								echo number_format($value['total_pay'], 2);
								$totalValue += $value['total_pay'];
							?>
						</td>
					</tr>
				 <?php } ?>
				<tr>
					<td align="left"><strong>Totals</strong></td>
					<td align="left">
						<?php echo number_format($totalValue, 2); ?>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
