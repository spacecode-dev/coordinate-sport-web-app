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
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'field_name');
					$name = NULL;
					if (isset($voucher_info->name)) {
						$name = $voucher_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Code <em>*</em>', 'field_code');
					$code = NULL;
					if (isset($voucher_info->code)) {
						$code = $voucher_info->code;
					}
					$data = array(
						'name' => 'code',
						'id' => 'field_code',
						'class' => 'form-control',
						'value' => set_value('code', $this->crm_library->htmlspecialchars_decode($code), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Discount Type <em>*</em>', 'discount_type');
					$discount_type = NULL;
					if (isset($voucher_info->discount_type)) {
						$discount_type = $voucher_info->discount_type;
					}
					$options = array(
						'percentage' => 'Percentage',
						'amount' => 'Amount per session',
						'block_amount' => 'Amount per block'
					);
					echo form_dropdown('discount_type', $options, set_value('discount_type', $this->crm_library->htmlspecialchars_decode($discount_type), FALSE), 'id="discount_type" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Discount <em>*</em>', 'field_discount');
					$discount = NULL;
					if (isset($voucher_info->discount)) {
						$discount = $voucher_info->discount;
					}
					$data = array(
						'name' => 'discount',
						'id' => 'field_discount',
						'class' => 'form-control',
						'value' => set_value('discount', $this->crm_library->htmlspecialchars_decode($discount), FALSE)
					);
					?><div class="input-group">
						<div class="input-group-append amount"><span class="input-group-text"><?php echo currency_symbol(); ?></span></div>
						<?php echo form_number($data); ?>
						<div class="input-group-append percentage"><span class="input-group-text">%</span></div>
						<div class="input-group-append amount"><span class="input-group-text">per session</span></div>
						<div class="input-group-append block_amount"><span class="input-group-text">per block</span></div>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Comment', 'field_comment');
					$comment = NULL;
					if (isset($voucher_info->comment)) {
						$comment = $voucher_info->comment;
					}
					$data = array(
						'name' => 'comment',
						'id' => 'field_comment',
						'class' => 'form-control',
						'value' => set_value('comment', $this->crm_library->htmlspecialchars_decode($comment), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					$data = array(
						'name' => 'siblingdiscount',
						'id' => 'siblingdiscount',
						'value' => 1
					);
					$siblingdiscount = NULL;
					if (isset($voucher_info->siblingdiscount)) {
						$siblingdiscount = $voucher_info->siblingdiscount;
					}
					if (set_value('siblingdiscount', $this->crm_library->htmlspecialchars_decode($siblingdiscount), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Sibling Discount
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">This voucher will only apply when one or more participants from the same account are booked on to the same session, either when booked simultaneously or separately</small>
				</div>
				<?php
				if (count($lesson_types) > 0 ){
					?><div class="form-group">
						<?php
						echo form_label('Applies To <em>*</em>');
						foreach ($lesson_types as $typeID => $label) {
							$data = array(
								'name' => 'lesson_types[]',
								'value' => $typeID
							);

							if ($this->input->post()) {
								$lesson_types_array = $this->input->post('lesson_types');
							}
							if (!is_array($lesson_types_array)) {
								$lesson_types_array = array();
							}
							if (in_array($typeID, $lesson_types_array)) {
								$data['checked'] = TRUE;
							}
							?>
							<div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									<?php echo $label; ?>
									<span></span>
								</label>
							</div>
							<?php
						}
						?>
					</div><?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
