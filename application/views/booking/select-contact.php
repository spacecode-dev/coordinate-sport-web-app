<?php
display_messages();
?>
<div class='row'>
	<div class='col-sm-12'>
		<div class='box bordered-box'>
			<div class='box-content box-double-padding'>
				<?php
				echo form_open();
					echo form_fieldset();
						?>
						<div class='col-sm-6'>
							<div class='form-group'><?php
								echo form_label('Select Contact <em>*</em>', 'contactID');
								$options = [];
								if (count($contacts) > 0) {
									foreach ($contacts as $id => $label) {
										$options[$id] = $label;
									}
								}
								echo form_dropdown('contactID', $options, set_value('contactID', NULL, FALSE), 'id="contactID" class="select2-ajax form-control" data-ajax-url="' . site_url('ajax/contacts') . '"')
							?></div>
						</div>
						<div class='col-sm-6'>
							<div class="form-group">
								<label class="visible-sm-block visible-md-block visible-lg-block">&nbsp;</label>
								<button class='btn btn-primary btn-submit' type="submit">
									Continue
								</button>
							</div>
						</div>
					<?php echo form_fieldset_close();
				echo form_close();
				?>
			</div>
		</div>
	</div>
</div>
