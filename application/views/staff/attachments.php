<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
if ($this->auth->user->department == 'office') {
	?><div class="alert alert-info">
		<p>You can add attachments in this area by clicking on <?php echo anchor('staff/attachments/' . $staffID . '/new', 'Create New'); ?>, however you will not be able to see previously added attachments or edit them in this area unless you have a higher access level.</p>
	</div><?php
} else {
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
			<div class='row'>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_name">Name</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => $search_fields['name']
					);
					echo form_input($data);
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_comment">Comment</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_comment',
						'id' => 'field_comment',
						'class' => 'form-control',
						'value' => $search_fields['comment']
					);
					echo form_input($data);
					?>
				</div>
                <div class='col-sm-2'>
                    <p>
                        <strong><label for="field_comment">Comment</label></strong>
                    </p>
                    <?php
                    $data = array(
                        'name' => 'search_comment',
                        'id' => 'field_comment',
                        'class' => 'form-control',
                        'value' => $search_fields['comment']
                    );
                    echo form_input($data);
                    ?>
                </div>
                <div class='col-sm-2'>
                    <p>
                        <strong><label for="field_gender">Qualifications</label></strong>
                    </p>
                    <?php
                    $options = array(
                        '' => 'Select',
                        'additional_quals' => 'Additional Qualifications',
                        'mandatory_quals' => 'Mandatory Qualifications'
                    );
                    echo form_dropdown('search_area', $options, $search_fields['area'], 'id="field_qualifications" class="select2 form-control"');
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
	if ($files->num_rows() == 0) {
		?>
		<div class="alert alert-info">
			No files found. Do you want to <?php echo anchor($add_url, 'create one'); ?>?
		</div>
		<?php
	} else {
		?>
		<?php echo $this->pagination_library->display($page_base); ?>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th>
								Name
							</th>
							<th>
								Comment
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
                        $quals = array(
                            'first' => 'First Aid',
                            'child' => 'Child Protection',
                            'fsscrb' => 'Company DBS',
                            'othercrb' => 'Other DBS'
                        );
						foreach ($files->result() as $row) {
						    $additionalText = null;
                            $qualToDisplay = null;
                            if ($row->area) {
                                switch ($row->area) {
                                    case 'mandatory_quals':
                                        if (isset($quals[$row->belongs_to])) {
                                            $qualToDisplay = $quals[$row->belongs_to];
                                        } else if (isset($mandatory_qualifications[$row->belongs_to])) {
                                            $qualToDisplay = $mandatory_qualifications[$row->belongs_to]->name;
                                        }

                                        $additionalText = '(Mandatory Qualifications)';

                                        if ($qualToDisplay) {
                                            $additionalText = '(Mandatory Qualifications > ' . $qualToDisplay . ')';
                                        }
                                        break;
                                    case 'additional_quals':
                                        $qualToDisplay = $additional_qualifications[$row->belongs_to]->name;

                                        $additionalText = '(Additional Qualifications)';

                                        if ($qualToDisplay) {
                                            $additionalText = '(Additional Qualifications > ' . $qualToDisplay . ')';
                                        }
                                        break;
                                }
                            }
							?>
							<tr>
								<td class="name">
									<?php echo anchor('attachment/staff/' . $row->path, $row->name, 'target="_blank"'); ?>
                                    <?php
                                        if ($additionalText) {
                                            echo "<br>" . $additionalText;
                                        }
                                    ?>
								</td>
								<td>
									<?php echo $row->comment; ?>
								</td>
								<td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('staff/attachments/edit/' . $row->attachmentID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/attachments/remove/' . $row->attachmentID); ?>' title="Remove">
											<i class='far fa-trash'></i>
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
		<?php
	}
	echo $this->pagination_library->display($page_base);
}
