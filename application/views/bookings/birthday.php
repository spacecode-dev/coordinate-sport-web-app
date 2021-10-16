<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
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
					echo form_label('Package <em>*</em>', 'bPackage');
					$bPackage = NULL;
					if (isset($booking_info->bPackage)) {
						$bPackage = $booking_info->bPackage;
					}
					$options = array(
						'' => 'Select',
						'bronze' => 'Bronze',
						'silver' => 'Silver',
						'gold' => 'Gold'
					);
					echo form_dropdown('bPackage', $options, set_value('bPackage', $this->crm_library->htmlspecialchars_decode($bPackage), FALSE), 'id="bPackage" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Theme <em>*</em>', 'bTheme');
					$bTheme = NULL;
					if (isset($booking_info->bTheme)) {
						$bTheme = $booking_info->bTheme;
					}
					$data = array(
						'name' => 'bTheme',
						'id' => 'bTheme',
						'class' => 'form-control',
						'value' => set_value('bTheme', $this->crm_library->htmlspecialchars_decode($bTheme), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Attendees <em>*</em>', 'bAttendees');
					$bAttendees = NULL;
					if (isset($booking_info->bAttendees)) {
						$bAttendees = $booking_info->bAttendees;
					}
					$data = array(
						'name' => 'bAttendees',
						'id' => 'bAttendees',
						'class' => 'form-control',
						'value' => set_value('bAttendees', $this->crm_library->htmlspecialchars_decode($bAttendees), FALSE),								);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Notes', 'bNotes');
					$bNotes = NULL;
					if (isset($booking_info->bNotes)) {
						$bNotes = $booking_info->bNotes;
					}
					$data = array(
						'name' => 'bNotes',
						'id' => 'bNotes',
						'class' => 'form-control',
						'value' => set_value('bNotes', $this->crm_library->htmlspecialchars_decode($bNotes), FALSE),								);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Paid', 'bPaid');
					$data = array(
						'name' => 'bPaid',
						'id' => 'bPaid',
						'value' => 1
					);
					$bPaid = NULL;
					if (isset($booking_info->bPaid)) {
						$bPaid = $booking_info->bPaid;
					}
					if (set_value('bPaid', $this->crm_library->htmlspecialchars_decode($bPaid), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
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
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Checklist</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<h3>Bronze/Silver/Gold</h3>
				<div class='form-group'><?php
					echo form_label('1hour 15mins coaching', 'bcCoaching');
					$data = array(
						'name' => 'bcCoaching',
						'id' => 'bcCoaching',
						'value' => 1
					);
					$bcCoaching = NULL;
					if (isset($booking_info->bcCoaching)) {
						$bcCoaching = $booking_info->bcCoaching;
					}
					if (set_value('bcCoaching', $this->crm_library->htmlspecialchars_decode($bcCoaching), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Birthday Card', 'bcCard');
					$data = array(
						'name' => 'bcCard',
						'id' => 'bcCard',
						'value' => 1
					);
					$bcCard = NULL;
					if (isset($booking_info->bcCard)) {
						$bcCard = $booking_info->bcCard;
					}
					if (set_value('bcCard', $this->crm_library->htmlspecialchars_decode($bcCard), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Certificates', 'bcCerts');
					$data = array(
						'name' => 'bcCerts',
						'id' => 'bcCerts',
						'value' => 1
					);
					$bcCerts = NULL;
					if (isset($booking_info->bcCerts)) {
						$bcCerts = $booking_info->bcCerts;
					}
					if (set_value('bcCerts', $this->crm_library->htmlspecialchars_decode($bcCerts), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Invitations', 'bcInvites');
					$data = array(
						'name' => 'bcInvites',
						'id' => 'bcInvites',
						'value' => 1
					);
					$bcInvites = NULL;
					if (isset($booking_info->bcInvites)) {
						$bcInvites = $booking_info->bcInvites;
					}
					if (set_value('bcInvites', $this->crm_library->htmlspecialchars_decode($bcInvites), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'>
	                <h3>Silver/Gold</h3><?php
					echo form_label('Medals', 'bcMedals');
					$data = array(
						'name' => 'bcMedals',
						'id' => 'bcMedals',
						'value' => 1
					);
					$bcMedals = NULL;
					if (isset($booking_info->bcMedals)) {
						$bcMedals = $booking_info->bcMedals;
					}
					if (set_value('bcMedals', $this->crm_library->htmlspecialchars_decode($bcMedals), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Birthday Cake', 'bcCake');
					$data = array(
						'name' => 'bcCake',
						'id' => 'bcCake',
						'value' => 1
					);
					$bcCake = NULL;
					if (isset($booking_info->bcCake)) {
						$bcCake = $booking_info->bcCake;
					}
					if (set_value('bcCake', $this->crm_library->htmlspecialchars_decode($bcCake), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<h3>Gold</h3>
				<div class='form-group'><?php
					echo form_label('Party Bags', 'bcBags');
					$data = array(
						'name' => 'bcBags',
						'id' => 'bcBags',
						'value' => 1
					);
					$bcBags = NULL;
					if (isset($booking_info->bcBags)) {
						$bcBags = $booking_info->bcBags;
					}
					if (set_value('bcBags', $this->crm_library->htmlspecialchars_decode($bcBags), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Trophy', 'bcTrophy');
					$data = array(
						'name' => 'bcTrophy',
						'id' => 'bcTrophy',
						'value' => 1
					);
					$bcTrophy = NULL;
					if (isset($booking_info->bcTrophy)) {
						$bcTrophy = $booking_info->bcTrophy;
					}
					if (set_value('bcTrophy', $this->crm_library->htmlspecialchars_decode($bcTrophy), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Photo shoot', 'bcPhoto');
					$data = array(
						'name' => 'bcPhoto',
						'id' => 'bcPhoto',
						'value' => 1
					);
					$bcPhoto = NULL;
					if (isset($booking_info->bcPhoto)) {
						$bcPhoto = $booking_info->bcPhoto;
					}
					if (set_value('bcPhoto', $this->crm_library->htmlspecialchars_decode($bcPhoto), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
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
	</div>
<?php echo form_close();
