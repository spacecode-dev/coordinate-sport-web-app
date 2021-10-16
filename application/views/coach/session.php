<?php
display_messages();

echo form_fieldset('', ['class' => 'card card-custom']);
	?><div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
			<h3 class="card-label">Session Details</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<?php
			if ($booking_info->type == 'event' && !empty($booking_info->name)) {
				?><div class='form-group'><?php
				echo form_label('Event');
				echo '<p>' . $booking_info->name . '</p>';
				?></div><?php
			}
			?>
			<div class='form-group'><?php
				echo form_label('Date');
				echo '<p>' . mysql_to_uk_date($date) . '</p>';
				?></div>
			<div class='form-group'><?php
				echo form_label('Time');
				echo '<p>' . substr($lesson_info->startTime, 0, 5) . ' to ' . substr($lesson_info->endTime, 0, 5) . '</p>';
				?></div>
			<div class='form-group'><?php
				if ($booking_info->type == 'event') { $label = 'Venue'; } else { $label = $this->settings_library->get_label('customer'); }
				echo form_label($label);
				echo '<p>' . $org_info->name . '</p>';
				?></div>
			<?php
			$address_parts = array();
			if (!empty($address_info->address1)) {
				$address_parts[] = $address_info->address1;
			}
			if (!empty($address_info->address2)) {
				$address_parts[] = $address_info->address2;
			}
			if (!empty($address_info->address3)) {
				$address_parts[] = $address_info->address3;
			}
			if (!empty($address_info->town)) {
				$address_parts[] = $address_info->town;
			}
			if (!empty($address_info->county)) {
				$address_parts[] = $address_info->county;
			}
			if (!empty($address_info->postcode)) {
				$address_parts[] = $address_info->postcode;
			}
			if (count($address_parts)) {
				?><div class='form-group'><?php
				echo form_label('Address');
				echo '<p>' . implode(', ', $address_parts) . '</p>';
				?></div><?php
			}
			if (!empty($lesson_info->location)) {
				?><div class='form-group'><?php
				echo form_label('Location');
				echo '<p>' . $lesson_info->location . '</p>';
				?></div><?php
			}
			if (!empty($lesson_info->type)) {
				?><div class='form-group'><?php
				echo form_label('Type');
				echo '<p>';
				if (!empty($lesson_info->type)) {
					echo $lesson_info->type;
				} else if (!empty($lesson_info->type_other)) {
					echo $lesson_info->type_other;
				}
				echo '</p>';
				?></div><?php
			}
			$activity = NULL;
			if (!empty($lesson_info->activity)) {
				$activity = $lesson_info->activity;
			} else if (!empty($lesson_info->activity_other)) {
				$activity = $lesson_info->activity_other;
			}
			if (!empty($lesson_info->activity_desc)) {
				if (!empty($activity)) {
					$activity .= ' - ';
				}
				$activity .= $lesson_info->activity_desc;
			}
			if (!empty($activity)) {
				?><div class='form-group'><?php
				echo form_label('Activity');
				echo '<p>' . $activity . '</p>';
				?></div><?php
			}
			if (!empty($lesson_info->group)) {
				?><div class='form-group'><?php
				echo form_label('Group/Class');
				echo '<p>';
				if ($lesson_info->group == 'other') {
					echo $lesson_info->group_other;
				} else {
					echo $this->crm_library->format_lesson_group($lesson_info->group);

				}
				echo '</p>';
				?></div><?php
			}
			if (!empty($lesson_info->class_size)) {
				?><div class='form-group'><?php
				echo form_label('Class Size');
				echo '<p>' . $lesson_info->class_size . '</p>';
				?></div><?php
			}
			if (count($staff_list['headcoaches'])) {
				$label = $this->settings_library->get_staffing_type_label('head');
				if (count($staff_list['headcoaches']) != 1) {
					$label = Inflect::pluralize($label);
				}
				?><div class='form-group'><?php
				echo form_label($label);
				echo '<p>' . implode('<br />', $staff_list['headcoaches']) . '</p>';
				?></div><?php
			}
			if (count($staff_list['leadcoaches'])) {
				$label = $this->settings_library->get_staffing_type_label('lead');
				if (count($staff_list['leadcoaches']) != 1) {
					$label = Inflect::pluralize($label);
				}
				?><div class='form-group'><?php
				echo form_label($label);
				echo '<p>' . implode('<br />', $staff_list['leadcoaches']) . '</p>';
				?></div><?php
			}
			if (count($staff_list['coaches'])) {
				$label = $this->settings_library->get_staffing_type_label('assistant');
				if (count($staff_list['coaches']) != 1) {
					$label = Inflect::pluralize($label);
				}
				?><div class='form-group'><?php
				echo form_label($label);
				echo '<p>' . implode('<br />', $staff_list['coaches']) . '</p>';
				?></div><?php
			}
			if (count($staff_list['participants'])) {
				$label = $this->settings_library->get_staffing_type_label('participant');
				if (count($staff_list['participants']) != 1) {
					$label = Inflect::pluralize($label);
				}
				?><div class='form-group'><?php
				echo form_label($label);
				echo '<p>' . implode('<br />', $staff_list['participants']) . '</p>';
				?></div><?php
			}
			if (count($staff_list['observers'])) {
				$label = $this->settings_library->get_staffing_type_label('observer');
				if (count($staff_list['observers']) != 1) {
					$label = Inflect::pluralize($label);
				}
				?><div class='form-group'><?php
				echo form_label($label);
				echo '<p>' . implode('<br />', $staff_list['observers']) . '</p>';
				?></div><?php
			}
			if($org_contact_info !== FALSE && $this->settings_library->get('show_customer_in_session_staff', $this->auth->user->accountID) == 1){
				$label = $this->settings_library->get_staffing_type_label('show_customer_in_session_staff');
				?>
				<div class='form-group'><?php
				echo "<label>Customer Contact</label>";
				echo '<p>'.$org_contact_info->name.' (<a href="tel:'.$org_contact_info->tel.'">'.$org_contact_info->tel.'</a>)</p>';
				?></div>
				<?php
			}
			?>
		</div>
	</div><?php
	echo form_fieldset_close();
	// show session evaluation if session in past and head coach
	if ($evaluation_show_form === TRUE) {
		echo form_fieldset('', ['class' => 'card card-custom', 'id' => 'attachments']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Session Evaluation</h3>
				</div>
			</div>
			<div class="card-body">
				<?php
				foreach ($evaluations as $evaluation_info) {
					//dont show evaluation form if it is unsubmitted and not belongs to current coach
					if (isset($evaluation_info->status) && isset($evaluation_info->byID)) {
						if ($evaluation_info->status == 'unsubmitted' && $this->auth->user->staffID != $evaluation_info->byID) {
							continue;
						}
					}
					echo form_open_multipart('coach/session/' . $lesson_info->lessonID . '/' . $date, 'id="evaluate"');
					echo form_hidden('action', 'evaluation');
					echo form_hidden('evaluations_id', $evaluation_info->noteID);
					?>
					<div class='form-group'><?php
						echo form_label('Status');
						$evaluation_status = 'Unsubmitted';
						$label_colour = 'info';
						if (isset($evaluation_info->status)) {
							$evaluation_status = ucwords($evaluation_info->status);
							switch ($evaluation_info->status) {
								default:
									$label_colour = 'info';
									break;
								case 'submitted':
									$label_colour = 'warning';
									break;
								case 'approved':
									$label_colour = 'success';
									break;
								case 'rejected':
									$label_colour = 'danger';
									if (!empty($evaluation_info->rejection_reason)) {
										$evaluation_status .= ' (' . $evaluation_info->rejection_reason . ')';
									}
									break;
							}
						}
						?><br><span
							class="label label-inline label-<?php echo $label_colour; ?>"><?php echo $evaluation_status; ?></span><?php
						?></div>
					<?php if ($evaluation_info->status != 'unsubmitted' && isset($staff_list['headcoaches'][$this->auth->user->staffID])) { ?>
						<div class="form-group">
							<?php
							echo form_label('Submitted By');
							?>
							<br><span><?php
								echo $staff_names[$evaluation_info->byID]; ?></span>
						</div>
					<?php } ?>
					<div class='form-group'><?php
					echo form_label('Evaluation <em>*</em>', 'evaluation');
					$evaluation = NULL;
					if (isset($evaluation_info->content)) {
						$evaluation = $evaluation_info->content;
					}
					// convert pre-wysiwyg fields to html
					if ($evaluation == strip_tags($evaluation)) {
						$evaluation = '<p>' . nl2br($evaluation) . '</p>';
					}
					$data = array(
						'name' => 'evaluation',
						'id' => 'evaluation',
						'class' => 'form-control wysiwyg',
						'value' => set_value('evaluation', $this->crm_library->htmlspecialchars_decode($evaluation), FALSE)
					);
					if (array_key_exists($evaluation_info->noteID, $evaluation_read_only)) {
						echo $evaluation;
					} else {
						echo form_textarea($data);
					}
					?></div><?php
					if (!array_key_exists($evaluation_info->noteID, $evaluation_read_only)) {
						?>
						<button class='btn btn-primary btn-submit' type="submit">
							Submit
						</button><?php
					}
					echo form_close();
				}
				if (array_key_exists($this->auth->user->staffID, $staff_list['headcoaches'])) {
					if (!array_key_exists($this->auth->user->staffID, $evaluations)) {
						echo form_open_multipart('coach/session/' . $lesson_info->lessonID . '/' . $date, 'id="evaluate"');
						echo form_hidden('action', 'evaluation');
						echo form_hidden('evaluations_id', null);
						?>
						<div class='form-group'><?php
							echo form_label('Status');
							$evaluation_status = 'Unsubmitted';
							$label_colour = 'info';
							?><br><span
								class="label label-inline label-<?php echo $label_colour; ?>"><?php echo $evaluation_status; ?></span><?php
							?></div>
						<div class='form-group'><?php
							echo form_label('Evaluation <em>*</em>', 'evaluation');
							$evaluation = NULL;
							$data = array(
								'name' => 'evaluation',
								'id' => 'evaluation',
								'class' => 'form-control wysiwyg',
								'value' => set_value('evaluation', $this->crm_library->htmlspecialchars_decode($evaluation), FALSE)
							);
							echo form_textarea($data);
							?></div>
						<button class='btn btn-primary btn-submit' type="submit">
							Submit
						</button><?php
						echo form_close();
					}
				}
				?>
			</div><?php
		echo form_fieldset_close();
	}
	// birthday
	if ($lesson_info->birthday_tab == 1) {
		echo form_fieldset('', ['class' => 'card card-custom']); ?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Birthday Details</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (!empty($booking_info->bPackage)) {
						?><div class='form-group'><?php
						echo form_label('Package');
						echo '<p>' . ucwords($booking_info->bPackage) . '</p>';
						?></div><?php
					}
					if (!empty($booking_info->bTheme)) {
						?><div class='form-group'><?php
						echo form_label('Theme');
						echo '<p>' . $booking_info->bTheme . '</p>';
						?></div><?php
					}
					if (!empty($booking_info->bAttendees)) {
						?><div class='form-group'><?php
						echo form_label('Attendees');
						echo '<p>' . nl2br($booking_info->bAttendees) . '</p>';
						?></div><?php
					}
					if (!empty($booking_info->bNotes)) {
						?><div class='form-group'><?php
						echo form_label('Notes');
						echo '<p>' . nl2br($booking_info->bNotes) . '</p>';
						?></div><?php
					}
					?>
				</div>
			</div><?php
			echo form_fieldset_close();
		}
		// session plans
		if ($this->auth->has_features('resources') && $lesson_plans->num_rows() > 0) {
			echo form_fieldset('', ['class' => 'card card-custom']);
				?><div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label">Scheme of Work</h3>
					</div>
				</div>
				<div class='table-responsive'>
					<table class='table table-striped table-bordered'>
						<thead>
						<tr>
							<th>
								Name
							</th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach ($lesson_plans->result() as $row) {
							?>
							<tr>
								<td>
									<?php echo anchor('attachment/files/' . $row->path, $row->name, 'target="_blank"'); ?>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div><?php
			echo form_fieldset_close();
		}
		// session notes
		if ($lesson_notes->num_rows() > 0) {
			echo form_fieldset('', ['class' => 'card card-custom']);
				?><div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label">Session Notes</h3>
					</div>
				</div>
				<div class='table-responsive'>
					<table class='table table-striped table-bordered'>
						<thead>
						<tr>
							<th>
								Date
							</th>
							<th>
								Note
							</th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach ($lesson_notes->result() as $row) {
							?>
							<tr>
								<td>
									<?php echo mysql_to_uk_datetime($row->added); ?>
								</td>
								<td class="w-75 less">
									<div class="d-block">
										<?php
										if ($row->content == strip_tags($row->content)) {
											$row->content = '<p>' . nl2br($row->content) . '</p>';
										}
										echo $row->content;
										?>
									</div>
									<a class="text-size text-left" href="javascript:void(0);">See More</a>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div><?php
			echo form_fieldset_close();
		}
		// session attachments
		echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Session Attachments</h3>
				</div>
			</div>
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
					</tr>
					</thead>
					<tbody>
					<?php
					if ($lesson_attachments->num_rows() == 0) {
						?><tr>
							<td colspan="2">None</td>
						</tr><?php
					} else {
						foreach ($lesson_attachments->result() as $row) {
							?><tr>
							<td>
								<?php echo anchor('attachment/booking/' . $row->path, $row->name, 'target="_blank"'); ?>
							</td>
							<td>
								<?php echo nl2br($row->comment); ?>
							</td>
							</tr><?php
						}
					}
					?>
					</tbody>
				</table>
			</div>
			<div class="card-body">
				<?php
				if ($this->auth->has_features('staff_lesson_uploads')) {
					if ($this->input->post('action') != 'attachment') {
						?><button class="upload-session-attachment btn btn-primary btn-xs">Upload Session Attachment</button><?php
					}
					echo form_open_multipart('coach/session/' . $lesson_info->lessonID . '/' . $date);
					echo form_hidden('action', 'attachment');
					?><div id="upload-session-attachment"<?php if ($this->input->post('action') != 'attachment') { echo ' style="display:none"'; } ?>>
						<div class="multi-columns">
							<div class='form-group'><?php
								echo form_label('Upload File <em>*</em>', 'file');
								$data = array(
									'name' => 'file',
									'id' => 'file',
									'class' => 'custom-file-input'
								);
								?>
								<div class="custom-file">
									<?php echo form_upload($data); ?>
									<label class="custom-file-label" for="file">Choose file</label>
								</div>
							</div>
							<div class='form-group'><?php
								echo form_label('Comment <em>*</em>', 'comment');
								$comment = NULL;
								if (isset($attachment_info->comment)) {
									$comment = $attachment_info->comment;
								}
								$data = array(
									'name' => 'comment',
									'id' => 'comment',
									'class' => 'form-control',
									'value' => set_value('comment'),
									'maxlength' => 255
								);
								echo form_input($data);
							?></div>
						</div>
						<button class='btn btn-primary btn-submit' type="submit">
							<i class='far fa-save'></i> Upload
						</button>
					</div><?php
					echo form_close();
				} ?>
			</div><?php
		echo form_fieldset_close();

		//Project attachments
		echo form_fieldset('', ['class' => 'card card-custom', 'id' => 'project-attachments']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Project Attachments</h3>
				</div>
			</div>
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
					</tr>
					</thead>
					<tbody>
					<?php
					if ($project_attachments->num_rows() == 0) {
						?><tr>
							<td colspan="2">None</td>
						</tr><?php
					} else {
						foreach ($project_attachments->result() as $row) {
							?><tr>
							<td>
								<?php echo anchor('attachment/event/' . $row->path, $row->name, 'target="_blank"'); ?>
							</td>
							<td>
								<?php echo nl2br($row->comment); ?>
							</td>
							</tr><?php
						}
					}
					?>
					</tbody>
				</table>
			</div><?php
		echo form_fieldset_close();
		// customer attachments
		if ($org_attachments->num_rows() > 0) {
			echo form_fieldset('', ['class' => 'card card-custom']);
				?><div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label"><?php if ($booking_info->type == 'event') { echo 'Venue'; } else { echo $this->settings_library->get_label('customer'); } ?> Attachments</h3>
					</div>
				</div>
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
						</tr>
						</thead>
						<tbody>
						<?php
						foreach ($org_attachments->result() as $row) {
							?>
							<tr>
								<td>
									<?php echo anchor('attachment/customer/' . $row->path, $row->name, 'target="_blank"'); ?>
								</td>
								<td>
									<?php echo nl2br($row->comment); ?>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div><?php
			echo form_fieldset_close();
		}
		// safety docs
		if ($this->auth->has_features('safety') && $safety_docs->num_rows() > 0) {
			echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Health &amp; Safety</h3>
				</div>
			</div>
			<div class="card-body">
				<p>Please click through to view any documents and confirm you have read them with the button at the bottom of the page when viewing.</p>
			</div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered'>
					<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Name
						</th>
						<th>
							Expiry
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ($safety_docs->result() as $row) {
						if ($row->type == 'camp induction') {
							$row->type = 'Event/Project Induction';
						}
						?>
						<tr>
							<td>
								<?php echo mysql_to_uk_date($row->date); ?>
							</td>
							<td>
								<?php
								echo anchor('customers/safety/view/' . $row->docID, ucwords($row->type), 'target="_blank"');
								if (!empty($row->lesson_type)) {
									echo ' (' . $row->lesson_type . ')';
								}
								?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->expiry); ?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div><?php
			echo form_fieldset_close();
		}
		// participants
		if ($this->auth->has_features('participants') && ($booking_info->type == 'event' || $booking_info->project == 1) && !array_key_exists($this->auth->user->staffID, $staff_list['participants'])) {
			echo form_fieldset('', ['class' => 'card card-custom']);
				?><div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label"><?php echo $this->settings_library->get_label('participants'); ?></h3>
					</div>
				</div>
				<div class="card-body">
					<p><?php
					if (in_array($booking_info->register_type, array('numbers', 'names'))) {
						echo anchor('bookings/participants/' . $lesson_info->blockID, 'View and enter participants');
					} else {
						echo anchor('bookings/participants/print/' . $lesson_info->blockID . '/' . $lessonID, 'View and print participant register', 'target="_blank"');
					}
					?></p>
				</div><?php
			echo form_fieldset_close();
		}

		// other sessions staff
		if (count($other_sessions_staff) > 0) {
			echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
					<h3 class="card-label">Staff on Other Sessions</h3>
				</div>
			</div>
			<div class="card-body">
				<?php
				$tabs = array();
				$tab_labels = array();
				foreach ($other_sessions_staff as $row) {
					$label = ucwords($row->day) . ' (' . substr($row->startTime, 0, 5);
					if ($row->startTime != $row->endTime) {
						$label .= '-' . substr($row->endTime, 0, 5);
					}
					$label .= ')';
					$tab_labels[$row->lessonID] = $label;
					$tab = '<tr>
									<td>
										' . $staff_names[$row->staffID] . '
									</td>
									<td>
										' . substr($row->startTime, 0, 5);
					if ($row->startTime != $row->endTime) {
						$tab .= '-' . substr($row->endTime, 0, 5);
					}
					$tab .= '</td>
									<td>
										' . mysql_to_uk_date($row->startDate);
					if ($row->startDate != $row->endDate) {
						$tab .= '-' . mysql_to_uk_date($row->endDate);
					}
					$tab .= '</td>
									<td>
										';
					if (array_key_exists('mobile', $staff_contact_details[$row->staffID]) && !empty($staff_contact_details[$row->staffID]['mobile'])) {
						$tab .= '<a href="tel:' . $staff_contact_details[$row->staffID]['mobile'] . '">' . $staff_contact_details[$row->staffID]['mobile'] . '</a>';
					}
					$tab .= '</td>
									<td class="center">
										';
					if (array_key_exists('email', $staff_contact_details[$row->staffID]) && !empty($staff_contact_details[$row->staffID]['email'])) {
						$tab .= '<a href="mailto:' . $staff_contact_details[$row->staffID]['email'] . '" class="btn btn-default btn-xs"><i class="far fa-envelope"></i></a>';
					}
					$tab .= '</td>
								</tr>';
					if (!array_key_exists($row->lessonID, $tabs)) {
						$tabs[$row->lessonID] = NULL;
					}
					$tabs[$row->lessonID] .= $tab;
				}
				?><ul class="nav nav-tabs nav-bold nav-tabs-line"><?php
					$i = 0;
					foreach ($tab_labels as $lessonID => $label) {
						?><li class='nav-item'>
							<a data-toggle='tab' href='#tab<?php echo $lessonID; ?>' class="nav-link<?php if ($i == 0) { echo ' active'; } ?>">
								<?php echo $label; ?>
							</a>
						</li><?php
						$i++;
					}
				?></ul><?php
				$i = 0;
				?><div class='tab-content'><?php
					foreach ($tabs as $lessonID => $tab) {
						?><div id="tab<?php echo $lessonID; ?>" class="tab-pane <?php if ($i == 0) { echo ' active'; } ?>">
								<div class='table-responsive'>
									<table class='table table-striped table-bordered'>
										<thead>
										<tr>
											<th>
												Staff
											</th>
											<th>
												Time
											</th>
											<th>
												Dates
											</th>
											<th>
												Mobile
											</th>
											<th>
												Email
											</th>
										</tr>
										</thead>
										<tbody>
										<?php echo $tab; ?>
										</tbody>
									</table>
								</div>
						</div>
						<?php
						$i++;
					}
					?>
				</div>
			</div><?php
			echo form_fieldset_close();
		}
