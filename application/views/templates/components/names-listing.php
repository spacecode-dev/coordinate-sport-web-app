<input type="hidden" id="update-url" value="<?= $updateUrl ?>">
<input type="hidden" id="remove-url" value="<?= $removeUrl ?>">
<input type="hidden" id="token" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
<div class="custom-listing-wrapper">
		<div class="row top-block">
			<div class="col-sm-12">
				<?php echo form_open($createUrl, ['id' => 'add-form', 'class' => 'card']); ?>
					<div class="input-group card-body">
						<input type="text" name="name" class="form-control form-control-custom-input" id="input-field-add-option" placeholder="<?= $addFieldPlaceholder ?>">
						<span class="input-group-btn">
							<button type="submit" id="submit-new-option" class="btn btn-primary">Save</button>
						</span>
					</div>
				<?= form_close(); ?>
			</div>
		</div>
	<?php
	if (count($items) == 0) {
		?>
			<div class="row">
				<div class="col-sm-12">
					<div class="result card card-custom">
						<div class="card-body">
							<div class="alert alert-info">
								<i class="far fa-info-circle"></i>
								No records found.
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
	} else {
	?>
	<div class="row">
		<div class="col-sm-12">
			<div class="result card card-custom">
				<div class="card-body">
					<table class="table records-table">
					<tbody>
						<?php foreach ($items as $item)  { ?>
							<tr record-id="<?= $item->{$idField} ?>" original-value="<?= $item->name ?>">
								<td class="editable-td pl-0">
									<span class="name"><?= $item->name ?></span>
									<div class="block-extend-cell-width"><i class='edit-icon far fa-pencil'></i></div>
								</td>
								<td class="actions">
									<a class='delete' href='<?php echo site_url($removeUrl . $item->{$idField}); ?>' title="Remove">
										<i class='far fa-times'></i>
									</a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
