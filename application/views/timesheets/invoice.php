<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
	   ?><div class='card-header'>
		   	<div class="card-title">
		   		<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
		   		<h3 class="card-label">Details<?php
		   		if (isset($invoice_info->sent) && $invoice_info->sent == 1) {
				   ?> <small>Invoice sent on <?php echo mysql_to_uk_datetime($invoice_info->sent_date); ?></small><?php
				}
				?></h3>
		   	</div>
		   	<?php
			if (isset($invoice_info->sent) && $invoice_info->sent == 1) {
				if (in_array($this->auth->user->department, $allowed_departments)) {
					?><div class="card-toolbar"><input class='btn btn-sm btn-primary' type="submit" name="revert_draft" value="Revert to Draft" /></div><?php
				}
			}
			?>
	   </div>
	   <div class="card-body">
		   <div class="multi-columns">
				<div class='form-group'><?php
					echo form_label('Invoice Number <em>*</em>', 'number');
					$number = NULL;
					if (isset($invoice_info->number)) {
					   $number = $invoice_info->number;
					}
					$data = array(
					   'name' => 'number',
					   'id' => 'number',
					   'class' => 'form-control',
					   'value' => set_value('number', $this->crm_library->htmlspecialchars_decode($number), FALSE),
					   'maxlength' => 10,
					   'step' => 1,
					   'min' => 0
					);
					if ($mode != 'edit') {
					   $data['readonly'] = 'readonly';
					}
					?><div class="input-group"><?php
						if (!empty($invoice_prefix)) {
							?><div class="input-group-append"><span class="input-group-text"><?php echo $invoice_prefix; ?></span></div><?php
						}
						echo form_number($data);
					?></div>
				</div>
				<div class='form-group'><?php
				   echo form_label('Date <em>*</em>', 'date');
				   $date = NULL;
				   if (isset($invoice_info->date)) {
					   $date = mysql_to_uk_date($invoice_info->date);
				   }
				   if (empty($date)) {
					   $date = date('d/m/Y');
				   }
				   $data = array(
					   'name' => 'date',
					   'id' => 'date',
					   'class' => 'form-control datepicker',
					   'value' => set_value('date', $this->crm_library->htmlspecialchars_decode($date), FALSE),
					   'maxlength' => 10
				   );
				   if ($mode != 'edit') {
					   $data['readonly'] = 'readonly';
				   }
				   echo form_input($data);
				?></div>
				<div class='form-group'><?php
				  echo form_label('Subject <em>*</em>', 'subject');
				  $subject = NULL;
				  if (isset($invoice_info->subject)) {
					  $subject = $invoice_info->subject;
				  }
				  $data = array(
					  'name' => 'subject',
					  'id' => 'subject',
					  'class' => 'form-control',
					  'value' => set_value('subject', $this->crm_library->htmlspecialchars_decode($subject), FALSE),
					  'maxlength' => 200
				  );
				  if ($mode != 'edit') {
					  $data['readonly'] = 'readonly';
				  }
				  echo form_input($data);
				?></div>
				<div class='form-group'><?php
				 echo form_label('Buyer ID', 'buyer_id');
				 $buyer_id = NULL;
				 if (isset($invoice_info->buyer_id)) {
					 $buyer_id = $invoice_info->buyer_id;
				 }
				 $data = array(
					 'name' => 'buyer_id',
					 'id' => 'buyer_id',
					 'class' => 'form-control',
					 'value' => set_value('buyer_id', $this->crm_library->htmlspecialchars_decode($buyer_id), FALSE),
					 'maxlength' => 50
				 );
				 if ($mode != 'edit') {
					 $data['readonly'] = 'readonly';
				 }
				 echo form_input($data);
				?></div>
				<div class='form-group'><?php
				echo form_label('UTR', 'utr');
				$utr = NULL;
				if (isset($invoice_info->utr)) {
					$utr = $invoice_info->utr;
				}
				$data = array(
					'name' => 'utr',
					'id' => 'utr',
					'class' => 'form-control',
					'value' => set_value('utr', $this->crm_library->htmlspecialchars_decode($utr), FALSE),
					'maxlength' => 50
				);
				if ($mode != 'edit') {
					$data['readonly'] = 'readonly';
				}
				echo form_input($data);
				?></div>
				<div class='form-group'><?php
				echo form_label('Bank Name <em>*</em>', 'bank_name');
				$bank_name = NULL;
				if (isset($invoice_info->bank_name)) {
				   $bank_name = $invoice_info->bank_name;
				}
				$data = array(
				   'name' => 'bank_name',
				   'id' => 'bank_name',
				   'class' => 'form-control',
				   'value' => set_value('bank_name', $this->crm_library->htmlspecialchars_decode($bank_name), FALSE),
				   'maxlength' => 100
				);
				if ($mode != 'edit') {
				   $data['readonly'] = 'readonly';
				}
				echo form_input($data);
				?></div>
				<div class='form-group'><?php
				echo form_label('Sort Code <em>*</em>', 'bank_sort_code');
				$bank_sort_code = NULL;
				if (isset($invoice_info->bank_sort_code)) {
				  $bank_sort_code = $invoice_info->bank_sort_code;
				}
				$data = array(
				  'name' => 'bank_sort_code',
				  'id' => 'bank_sort_code',
				  'class' => 'form-control',
				  'value' => set_value('bank_sort_code', $this->crm_library->htmlspecialchars_decode($bank_sort_code), FALSE),
				  'maxlength' => 20
				);
				if ($mode != 'edit') {
				  $data['readonly'] = 'readonly';
				}
				echo form_input($data);
				?></div>
				<div class='form-group'><?php
				echo form_label('Account No. <em>*</em>', 'bank_account');
				$bank_account = NULL;
				if (isset($invoice_info->bank_account)) {
				 $bank_account = $invoice_info->bank_account;
				}
				$data = array(
				 'name' => 'bank_account',
				 'id' => 'bank_account',
				 'class' => 'form-control',
				 'value' => set_value('bank_account', $this->crm_library->htmlspecialchars_decode($bank_account), FALSE),
				 'maxlength' => 20
				);
				if ($mode != 'edit') {
				 $data['readonly'] = 'readonly';
				}
				echo form_input($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Items</h3>
			</div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="timesheet">
				<thead>
					<tr>
						<th>
							Description
						</th>
						<th>
							Amount
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (count($invoice_rows) > 0) {
						foreach ($invoice_rows as $row) {
							?><tr>
								<td class="name">
									<?php echo $row['desc']; ?>
								</td>
								<td>
									<?php echo currency_symbol() . number_format($row['amount'], 2); ?>
								</td>
							</tr><?php
						}
					}
					?>
					<tr>
						<td class="text-right"><strong>Total</strong></td>
						<td><?php echo currency_symbol() . number_format($total, 2); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php echo form_fieldset_close();
	if ($mode == 'edit') {
		?><div class='form-actions d-flex justify-content-between'>
			<div class="btn-group">
				<?php
				if ($this->settings_library->get('send_staff_invoices') == 1 && !empty($this->settings_library->get('staff_invoice_to'))) {
					?><button class='btn btn-primary btn-submit' type="submit">
						Send
					</button> <?php
				}
				if (!isset($invoice_info->sent) || $invoice_info->sent != 1) {
					?><input class='btn btn-light' type="submit" name="save" value="Save Draft" /><?php
				}
				?>
			</div>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div><?php
	}
echo form_close();
