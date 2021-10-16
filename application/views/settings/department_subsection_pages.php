<?php
display_messages();
if (!empty($department_data) && $department_data->num_rows() > 0) {
	echo form_open_multipart('settings/subsection/departments_emailsms/active', array('class' => 'settings')); ?>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered checkbox-enable-td'>
					<thead>
						<th>Name</th>
						<th>Departments</th>
						<th>Active</th>
						<th></th>
					</thead>
					<tbody>
						<?php foreach($department_data->result() as $item){ ?>
							<tr>
								<td><?php
									echo anchor('settings/subsection/departments_emailsms/edit/' . $item->ID, $item->name);
									?></td>
								<td><?php
									$brands = explode(",", $item->brand_name);
									$brand_colors = explode(",", $item->brand_colors);
									if(count($brands) > 0){
										foreach($brands as $index => $brand){ ?>
											<label class="label label-inline"
												   style="<?php echo label_style($brand_colors[$index]); ?>"
											><?php echo $brand;?></label>
										<?php }
									}
									?></td>
								<td class="text-center"><?php
									$data = array(
										'name' => 'status[]',
										'class' => 'auto',
										'value' => $item->ID,
									);

									if($item->active == "1"){
										$data['checked'] = TRUE;
									}
									echo form_checkbox($data);
									?></td>
								<td class="text-center">
									<a class='btn btn-warning btn-xs' href='<?php
									echo site_url('settings/subsection/departments_emailsms/edit/'. $item->ID);?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
								</td>
							</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
		</div>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
	<?php
	echo form_close();
}else{ ?>
	<div class="alert alert-info">
		No email footers found. Do you want to <a href="/settings/subsection/departments_emailsms/create">create one?</a>
	</div>
<?php } ?>
