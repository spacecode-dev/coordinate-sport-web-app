<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (!empty($staff_info->profile_pic)) {
					?><div class='form-group'><?php
						echo form_label('Current Photo');
						$data = array(
							'src' => 'attachment/staff_profile_pic/profile_pic/'.$staff_info->staffID,
							'class' => 'responsive-img',
							'style' => 'max-height:200px;'
						);
						echo "<br />" . img($data);
					?><span class="form-text text-muted">Size of printed ID Card will be 6.2cm (w) x 9.3cm (h)</span>
					</div><div class='form-group'>&nbsp;</div><?php
				}
				elseif (!empty($staff_info->id_photo_path)) {
					?><div class='form-group'><?php
					echo form_label('Current Photo');
					$data = array(
						'src' => 'attachment/staff-id/' . $staff_info->id_photo_path,
						'class' => 'responsive-img',
						'style' => 'max-height:200px;'
					);
					echo "<br />" . img($data);
					?><span class="form-text text-muted">Size of printed ID Card will be 6.2cm (w) x 9.3cm (h)</span>
					</div><div class='form-group'>&nbsp;</div><?php
				}
				?>
				<div class='form-group'><?php
					echo form_label('Personal Statement <em>*</em>', 'id_personalStatement');
					$id_personalStatement = NULL;
					if (isset($staff_info->id_personalStatement)) {
						$id_personalStatement = $staff_info->id_personalStatement;
					}
					// convert pre-wysiwyg fields to html
					if ($id_personalStatement == strip_tags($id_personalStatement)) {
						$id_personalStatement = '<p>' . nl2br($id_personalStatement) . '</p>';
					}
					$data = array(
						'name' => 'id_personalStatement',
						'id' => 'id_personalStatement',
						'class' => 'form-control wysiwyg',
						'value' => set_value('id_personalStatement', $this->crm_library->htmlspecialchars_decode($id_personalStatement), FALSE),
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Specialism <em>*</em>', 'id_specialism');
					$id_specialism = NULL;
					if (isset($staff_info->id_specialism)) {
						$id_specialism = $staff_info->id_specialism;
					}
					$data = array(
						'name' => 'id_specialism',
						'id' => 'id_specialism',
						'class' => 'form-control',
						'value' => set_value('id_specialism', $this->crm_library->htmlspecialchars_decode($id_specialism), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Favourite Quote <em>*</em>', 'id_favQuote');
					$id_favQuote = NULL;
					if (isset($staff_info->id_favQuote)) {
						$id_favQuote = $staff_info->id_favQuote;
					}
					$data = array(
						'name' => 'id_favQuote',
						'id' => 'id_favQuote',
						'class' => 'form-control',
						'value' => set_value('id_favQuote', $this->crm_library->htmlspecialchars_decode($id_favQuote), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Sporting Hero <em>*</em>', 'id_sportingHero');
					$id_sportingHero = NULL;
					if (isset($staff_info->id_sportingHero)) {
						$id_sportingHero = $staff_info->id_sportingHero;
					}
					$data = array(
						'name' => 'id_sportingHero',
						'id' => 'id_sportingHero',
						'class' => 'form-control',
						'value' => set_value('id_sportingHero', $this->crm_library->htmlspecialchars_decode($id_sportingHero), FALSE),
						'maxlength' => 55
					);
					echo form_input($data);
				?></div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
<div class='form-actions d-flex justify-content-between'>
	<button class='btn btn-primary btn-submit' type="submit">
		<i class='far fa-save'></i> Save
	</button>
</div>
<?php echo form_close();
