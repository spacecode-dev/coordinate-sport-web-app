<?php
display_messages();
$data = array(
	'folder' => $folder
);
$this->load->view('messages/tabs.php', $data);
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
		<?php
		$hidden_fields = array(
			'search' => 1
		);
		echo form_hidden($hidden_fields);
		?>
		<div class='row'>
            <div class='col-sm-2'>
                <p>
                    <strong><label for="field_name">Name</label></strong>
                </p>
                <?php
                $data = array(
                    'name' => 'search_name',
                    'id' => 'field_subject',
                    'class' => 'form-control',
                    'value' => $search_fields['name']
                );
                echo form_input($data);
                ?>
            </div>
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
if ($templates->num_rows() < 1) {
	?>
	<div class="alert alert-info">
		No templates found. Do you want to <?php echo anchor($add_url, 'create one'); ?>?
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
                            Name
                        </th>
						<th>
							Subject
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
                <?php
                foreach ($templates->result() as $row) {
                    ?>
                    <tr>
                        <td class="center"><input type="checkbox" name="bulk_templates[]" value="<?php echo $row->id; ?>"<?php if (in_array($row->id, $bulk_templates)) { echo ' checked="checked"'; } ?>></td>
                        <td class="name">
                            <?php
                            echo anchor('messages/template/view/' . $row->id, $row->name);
                            ?>
                        </td>
                        <td>
                            <?php echo $row->subject; ?>
                        </td>
                        <td>
                            <div class='text-right'>
                                <a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('messages/templates/remove/' . $row->id); ?>' title="Remove">
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
	<br />
	<div class="row bulk-actions">
		<div class="col-sm-2">
			<?php
			$options = array(
				'delete' => 'Delete',
			);

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
