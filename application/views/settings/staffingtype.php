<?php
display_messages();
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'field_name');
					$name = $original_types[$type];
					if (isset($type_info->name) && !empty($type_info->name)) {
						$name = $type_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					?><small class="text-muted form-text">Original name: <?php echo $original_types[$type]; ?></small>
				</div>
				<div class='form-group'><?php
					echo form_label('Include in Staffing Requirements', 'field_required');
					$required_for_session = 0;
					if (isset($type_info->required_for_session) && !empty($type_info->required_for_session)) {
						$required_for_session = $type_info->required_for_session;
					}
					if (!$type_info) {
					    if (array_key_exists($type, $required_staff_for_sessions)) {
					        $required_for_session = 1;
		                }
		            }
					$data = array(
						'name' => 'staff_required',
						'id' => 'staff_required',
						'value' => 1
					);
					if ($required_for_session) {
						$data['checked'] = TRUE;
					}
					?>
					<div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
		        <div class='form-group'><?php
		            echo form_label('Include on Timesheets', 'field_required');
		            $display_payroll = 0;
		            if (isset($type_info->display_on_payroll) && !empty($type_info->display_on_payroll)) {
		                $display_payroll = $type_info->display_on_payroll;
		            }
		            if (!$type_info) {
		                if (array_key_exists($type, $required_staff_for_sessions)) {
		                    $display_payroll = 1;
		                }
		            }
		            $data = array(
		                'name' => 'staff_display_on_payroll',
		                'id' => 'staff_display_on_payroll',
		                'value' => 1
		            );
		            if ($display_payroll) {
		                $data['checked'] = TRUE;
		            }
		            ?>
		            <div class="checkbox-single">
		            	<label class="checkbox">
		            		<?php echo form_checkbox($data); ?>
		            		Yes
		            		<span></span>
		            	</label>
		            </div>
		        </div>
		    </div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
