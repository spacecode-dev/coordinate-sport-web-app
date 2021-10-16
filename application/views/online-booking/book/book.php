<?php
if (!$in_crm) {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
}
display_messages($fa_weight);
echo form_open();
echo form_hidden(array(
	'process' => 1
));
if($register_type === 'adults_children'){
	echo form_hidden(array(
		'adults_children' => 1
	));
}
?>
<input type="hidden" name="already_in_cart" id="already_in_cart" value="<?php echo $already_in_cart ?>" />
<input type="hidden" name="initialFlag" id="initialFlag" value="0" />
<input type="hidden" name="participantFlag" id="participantFlag" value="0" />
<input type="hidden" name="hiddenflag" id="hiddenflag" value="<?php echo $hiddenflag ?>" />
<fieldset class="card card-custom">
		<div class="card-header">
			<div class="card-title">
				<?php if($register_type === 'adults_children'){ ?>
					<h4 class="with-line light">Select <?php echo $this->settings_library->get_label('adults_children', $this->cart_library->accountID); ?></h4>
				<?php }else{ ?>
					<h4 class="with-line light">Select <?php echo $this->settings_library->get_label('participants', $this->cart_library->accountID); ?></h4>
				<?php } ?>
			</div>
		</div>
		<div class='card-body'>
	<div id="select-participants">
		<?php
		$flag = 1;$childFlag=1;$valId='';
		if (count($participants) > 0) {
			foreach ($participants as $participantID => $participant) {
				if(isset($participant->type) &&  $participant->type === 'parent' && $flag){
					echo '<h5 class="light">Select '.$this->settings_library->get_label('adults', $this->cart_library->accountID).'</h5>';
					$flag = 0;
					$valId = 'a';
				}
				if(isset($participant->type) &&  $participant->type === 'child' && $childFlag){
					echo '<h5 class="light">Select '.$this->settings_library->get_label('participants', $this->cart_library->accountID).'</h5>';
					$childFlag = 0;
					$identifierChildFlag = 1;
					$valId = 'p';
				}
				$data = array(
					'name' => 'participants[]',
					'value' => $participantID,
					'data-dob' => $participant->dob
				);
				if(count($subs) > 0 && isset($participant->subID)){
					$data['data-subscription-id'] = $participant->subID;
					if(!$in_crm){
						$data['data-subscription-id'] = NULL;
					}
				} else {
					$data['data-subscription-id'] = NULL;
				}
				if (in_array($participantID, $selected_participants)) {
					$data['checked'] = TRUE;
				}
				?><div class="checkbox d-block p-0 m-0">
				<label class="fancy-checkbox<?php if (empty($participant->dob)) { echo ' disabled'; } ?>">
					<?php
					if (!empty($participant->dob)) {
						echo form_checkbox($data); ?>
						<i class="far fa-circle unchecked"></i>
						<i class="far fa-check-circle checked"></i><?php
					} else {
						?><i class="far fa-ban"></i><?php
					}
					?>
					<span class="name"><?php echo $participant->first_name .  ' ' . $participant->last_name; if (empty($participant->dob)) { echo ' (Missing Date of Birth)'; } ?></span>
				</label>
				</div><?php
			}
		}
		?>
	</div>
	<script>
		var selected_lessons = <?php echo json_encode($selected_lessons); ?>;
		var already_booked_sessions = <?php echo json_encode($already_booked_sessions); ?>;
	</script>
	<p class="mt-4 mb-0">
		<?php if($register_type === 'adults_children'){ ?>
			<a href="<?php echo site_url($new_adults_link); ?>" class="lightbox">Add <?php echo $this->settings_library->get_label('adult', $this->cart_library->accountID); ?></a>&nbsp;&nbsp;<a href="<?php echo site_url($new_participants_link); ?>" class="lightbox">Add <?php echo $this->settings_library->get_label('participant', $this->cart_library->accountID); ?></a>
		<?php }else{?>
			<a href="<?php echo site_url($new_participants_link); ?>" class="lightbox">Add <?php echo $this->settings_library->get_label('participant', $this->cart_library->accountID); ?></a>
		<?php }?>
	</p>
	<script>
		function lightbox_callback(new_participant) {
			$.magnificPopup.close();
			var participant_html = '<div class="checkbox">';
			participant_html += '<label class="fancy-checkbox">';
			participant_html += '<input type="checkbox" name="participants[]" value="' + new_participant.participantID + '"  data-dob="' + new_participant.dob + '" data-subscription-id="" checked="checked">';
			participant_html += '<i class="far fa-circle unchecked"></i>';
			participant_html += '<i class="far fa-check-circle checked"></i>';
			participant_html += ' <span class="name">' + new_participant.name + '</span>';
			participant_html += '</label>';
			participant_html += '</div>';
			$('#select-participants').append(participant_html);
			update_participants(false);

			$('#select-participants :checkbox[value=' + new_participant.participantID + ']').trigger("change");
			location.reload();
		}
	</script>
		</div>
	</fieldset>

	<div id="step2">
	<fieldset class="card card-custom">
		<div class="card-body">
			<?php
			if(count($subs) > 0):
				?>
				<div id="subscriptions_section" class="">
					<?php foreach($subs as $participantID => $subscription):
						?>
						<fieldset class="subscriptions <?php if($subscriptions_only) echo 'subs_only' ?>">
							<div id="subscription-<?php echo $participantID ?>" class="pb-4">
								<h4 class="with-line light">Select Subscriptions - <?php echo $participants[$participantID]->first_name . ' ' . $participants[$participantID]->last_name ?></h4>
								<?php
								$counter = 0;
								foreach ($subscription as $subID => $sub) {

									$data = array(
										'name' => 'subscriptions[' . $participantID . ']',
										'value' => $subID,
										'data-sub-price' => $sub['price'],
										'id' => 'sub-' . $participantID . '-' . $subID,
										'class' => 'subscriptions',
										'data-sub-now' => $sub['no_of_sessions_per_week'],
										'data-frequency' => $sub['frequency']

									);
									if(isset($subscription_status[$participantID][$subID]) && $subscription_status[$participantID][$subID]['status'] != 'active'){
										$data['data-sub-active'] = 0;
										$data['data-user'] = $participantID;
									}
									if(array_key_exists($participantID, $selected_subs) && $selected_subs[$participantID] == $subID) {
										if($already_in_cart) {
											$data['checked'] = TRUE;
										}
									}
									$dnone = '';
									if(isset($already_booked_subscriptions[$participantID]) && in_array($subID, $already_booked_subscriptions[$participantID])){
										$counter++;
										if($counter === count($subscription)){
											echo '<p>All subscriptions for this user has been already booked.</p>';
										}
										/*continue; */;
										$dnone = "style='display:none'";
										if(!$in_crm){
											$data['checked'] = FALSE;
										}
									}
									?><div class="checkbox" <?php echo $dnone ?>>
									<label class="fancy-checkbox">
										<?php
										echo form_checkbox($data); ?>
										<i class="far fa-circle unchecked"></i>
										<i class="far fa-check-circle checked"></i>
										<span class="name"><?php echo $sub['label'] ?></span>
										<?php if($sub['no_of_sessions_per_week'] != "" && $sub['no_of_sessions_per_week'] != "0"){ ?>
										<br />
										<span id="sp-<?php echo $participantID . '-' . $subID ?>" style="display:none"><b> This subscription will allow you to attend <?php echo $sub['no_of_sessions_per_week'] ?> sessions every week, please select the sessions you would like to attend. You will be able to change these later. </b></span>
										<?php } ?>
										<input type="hidden" name="now-<?php echo $participantID . '-' . $subID ?>" id="now-<?php echo $participantID . '-' . $subID ?>" value="<?php echo $sub['no_of_sessions_per_week']?>" />
										<input type="hidden" name="cut_off-<?php echo $participantID . '-' . $subID ?>" id="cut_off-<?php echo $participantID . '-' . $subID ?>" value="<?php echo $sub['session_cut_off']?>" />

									</label>
									</div><?php
								}?>
							</div>
						</fieldset>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div id="sessions">
			<h4 class="with-line light">Select Sessions</h4>
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true"><?php
				$i = 0;
				foreach ($blocks as $block) {
					$in_past = FALSE;
					$last_block_date = end($block->dates);
					if (strtotime($block->endDate . ' 23:59:59') < time()) {
						$in_past = TRUE;
					}
					$i++;
					?><div class="panel panel-default<?php if ($in_past === TRUE) { echo ' in-past'; } ?>" data-block="<?php echo $block->blockID; ?>">
						<div class="panel-heading" role="tab" id="heading<?php echo $i; ?>">
							<h4 class="panel-title">
								<a role="button"<?php if ($block->blockID != $blockID) { echo ' class="collapsed"'; } ?> data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $i; ?>" aria-expanded="<?php if ($block->blockID == $blockID) { echo 'true'; } else { echo 'false'; } ?>" aria-controls="collapse<?php echo $i; ?>">
									<?php echo $block->block; ?>
									<?php

										// show block total if all sessions required or fixed auto discount or fixed sibling discount
										if (intval($block->require_all_sessions) === 1 || ($block->autodiscount === 'fixed' && $block->autodiscount_amount > 0) || ($block->siblingdiscount === 'fixed' && $block->siblingdiscount_amount > 0)) {
											?> <span class="block_totals">
												<span class="price"><?php echo currency_symbol($this->cart_library->accountID); ?><span>0</span></span>
												<span class="discounted_price"></span>
											</span><?php
										}

								?>
							</a>
						</h4>
					</div>
					<div id="collapse<?php echo $i; ?>" class="panel-collapse collapse <?php if ($block->blockID == $blockID) { echo 'show in'; } ?>" role="tabpanel" aria-labelledby="heading<?php echo $i; ?>">
						<div class="panel-body">
							<table class="sessions table table-striped table-bordered1 <?php echo $in_crm ? "in-crm" : ""; ?>" data-require_all_sessions="<?php echo intval($block->require_all_sessions); ?>" data-autodiscount="<?php echo $block->autodiscount; ?>" data-siblingdiscount="<?php echo $block->siblingdiscount; ?>" data-siblingdiscount_amount="<?php echo $block->siblingdiscount_amount; ?>"<?php
							if ($block->block_price !== NULL) {
								echo ' data-block_price="' . $block->block_price . '"';
							}
							if ($block->autodiscount === 'fixed' && $block->autodiscount_amount > 0) {
								echo ' data-fixed_autodiscount="' . $block->autodiscount_amount . '"';
							}
							if ($block->siblingdiscount === 'fixed' && $block->siblingdiscount_amount > 0) {
								echo ' data-fixed_siblingdiscount="' . $block->siblingdiscount_amount . '"';
							}
							?>>
								<thead<?php echo $in_crm ? " style=\"color: #464E5F;\"" : "";?>>
								<tr>
									<th></th>
									<?php
									if (count($block->lesson_columns) > 0) {
										foreach ($block->lesson_columns as $label => $lessonIDs) {
											$label_parts = explode('!#!', $label);
											$label = array();
											if (array_key_exists(2, $label_parts) && !empty($label_parts[2])) {
												$label[] = $label_parts[2];
											} else if (array_key_exists(2, $label_parts) && !empty($label_parts[1])) {
												$label[] = $label_parts[1];
											}
											$label[] = $label_parts[0];
											?><th scope="col" class="text-center" data-check-all="0"><?php echo implode('<br>', $label); ?></th><?php
										}
									}
									?>
								</tr>
								</thead>
								<tbody>
								<?php
								if (count($block->dates) > 0) {
									$lessons_shown = array();
									foreach ($block->dates as $date => $lessons) {
										if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
											// if all sessions already shown, skip
											if (count(array_keys($lessons)) == count(array_intersect(array_keys($lessons), $lessons_shown))) {
												continue;
											}
										}
										$days_shown[] = strtolower(date('l', strtotime($date)));
										?><tr data-date="<?php echo $date; ?>">
										<th scope="row" data-check-all="0"><?php
											if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
												echo date('l', strtotime($date));
											} else {
												echo date('D jS M', strtotime($date));
											}
											?></th>
										<?php
										foreach ($block->lesson_columns as $label => $lessonIDs) {
											$date_lessons = array_keys($lessons);
											$result = array_intersect($lessonIDs, $date_lessons);
											if (is_array($result) && count($result) > 0) {
												foreach ($result as $lessonID) {
													$this_lesson = $lessons[$lessonID];
													$show_as_sold_out =  !$in_crm;
													// if auto discount and booking tags, check match that of contact
													if (count($block->booking_tags) > 0 && count(array_intersect($block->booking_tags, $this->cart_library->contact_tags)) == 0) {
														// if not, set autodiscount to 0
														$this_lesson['autodiscount'] = 0;
													}
													// if fixed block price, set auto and sibling discount to 0
													if ($block->require_all_sessions == 1 && $block->block_price !== NULL) {
														$this_lesson['autodiscount'] = 0;
														$this_lesson['siblingdiscount'] = 0;
													}
													// if all weeks boooking, multiply price/autodiscount
													if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
														$lessons_shown[] = $lessonID;
														$multiplier = 0;
														$tmp_dates = $block->dates;
														foreach ($tmp_dates as $tmp_lessons) {
															if (array_key_exists($lessonID, $tmp_lessons)) {
																$multiplier++;
															}
														}
														$this_lesson['price'] = $this_lesson['price']*$multiplier;
														$this_lesson['autodiscount'] = $this_lesson['autodiscount']*$multiplier;
														$this_lesson['siblingdiscount'] = $this_lesson['siblingdiscount']*$multiplier;
													}
													$availability_excluding = ($this_lesson['target_participants']-$this_lesson['actual_participants'])+(!isset($selected_lessons[$lessonID][$date]) ? 0 : count($selected_lessons[$lessonID][$date]));
													?><td class="text-center lesson<?php
													if ($this_lesson['sold_out'] === TRUE && $show_as_sold_out) {
														echo ' sold-out';
													}
													if ($this_lesson['cutoff'] < time()) {
														echo ' in-past';
													}
													?>" data-lessonID="<?php echo $lessonID; ?>" data-date="<?php echo $date; ?>" data-blockID="<?php echo $block->blockID; ?>" data-min_age="<?php echo $this_lesson['min_age']; ?>" data-max_age="<?php echo $this_lesson['max_age']; ?>" data-price="<?php echo $this_lesson['price']; ?>" <?php echo ($in_crm ? 'data-available_excluding="'.$availability_excluding.'"' : ""); ?> data-available="<?php echo $this_lesson['available']; ?>"<?php if ($this_lesson['autodiscount'] !== FALSE) { echo ' data-autodiscount="' . $this_lesson['autodiscount'] . '"'; } if ($this_lesson['siblingdiscount'] !== FALSE) { echo ' data-siblingdiscount="' . $this_lesson['siblingdiscount'] . '"'; } ?> data-discount="<?php echo $this_lesson['discount']; ?>"<?php if ($this_lesson['cutoff'] < time()) {
														echo ' title="Past Booking"';
													} ?>>
													<?php
													if ($this_lesson['sold_out'] === TRUE && $show_as_sold_out) {
														?><span>Sold Out</span><?php
													} else if(!isset($selected_lessons[$lessonID][$date]) && isset($block->status) && $block->status != 'active'){
														?><span>-</span><?php
													}else {
														$attribute = "";
														if(isset($selected_lessons[$lessonID][$date]) && count($selected_lessons[$lessonID][$date]) > 0){
															foreach($selected_lessons[$lessonID][$date] as $participantID){
																if(isset($subs[$participantID])) {
																	foreach ($subs[$participantID] as $index => $value) {
																		if (count($subscription_status) > 0 && isset($subscription_status[$participantID]) && array_key_exists($index, $subscription_status[$participantID])) {
																			$last_action_date = $subscription_status[$participantID][$index]['valid'];
																			$frequency = $subs[$participantID][$index]['frequency'];
																			switch ($frequency) {
																				case "weekly":
																					$validity = strtotime("+7 day", strtotime($last_action_date));
																					$valid_till = date('d-m-Y', $validity);
																					if (strtotime($date) > strtotime($valid_till)) {
																						$attribute .= $participantID . ",";
																					}
																					break;
																				case "monthly":
																					$validity = strtotime("+1 month", strtotime($last_action_date));
																					$valid_till = date('d-m-Y', $validity);
																					if (strtotime($date) > strtotime($valid_till)) {
																						$attribute .= $participantID . ",";
																					}
																					break;
																				case "yearly":
																					$validity = strtotime("+1 year", strtotime($last_action_date));
																					$valid_till = date('d-m-Y', $validity);
																					if (strtotime($date) > strtotime($valid_till)) {
																						$attribute .= $participantID . ",";
																					}
																					break;
																			}
																		}
																	}
																}
															}
														}
														$attribute = rtrim($attribute,",");
														?><label class="fancy-checkbox<?php if ($this_lesson['sold_out'] === TRUE && $show_as_sold_out) { echo " d-none hidden"; } ?>" subs-inactive="<?php echo $attribute;?>">
														<?php
														$data = array(
															'class' => 'lesson-toggle'
														);
														echo form_checkbox($data);
														?>
														<i class="far fa-circle unchecked"></i>
														<i class="far fa-check-circle checked"></i>
														<i class="far fa-ban disabled"></i>
														<br>
													<?php if(!$subscriptions_only){ ?>
														<span class="price"><?php
															if ($this_lesson['price'] > 0) {
																echo currency_symbol($this->cart_library->accountID) . '<span>' . $this_lesson['price'] . '</span>';
															} else {
																echo 'Free';
															}
															?></span>
													<?php } ?>
														<span class="discounted_price"></span>
														<div class="participants">
														</div>
														<?php
												}
													if ($in_crm && !empty($block->availability_status)) {
															?>
															<div class="availability <?php echo $block->availability_status_class; ?>">
																<i class="fas fa-circle"></i> Availability - <?php echo $block->availability_status; ?>
															</div>
															<?php
														}
														?>
														</label>
													</td><?php
													break;
												}
											} else {
												echo '<td class="text-center no-lesson">-</div>';
											}
										}
										?></tr><?php
									}
								}
								?>
								</tbody>
							</table>
							<p>Click on the date <span class="or_time"> or time</span> to select all</p>
							<?php
							if ($block->require_all_sessions != 1) {
								if ($block->siblingdiscount !== 'off') {
									?><div class="alert alert-info upsell upsell-siblingdiscount">
										<p><i class="<?php echo $fa_weight; ?> fa-info-circle"></i>
											Select <span class="participants_needed"></span> participant<span class="pl">s</span> on <span class="on"></span> to take advantage of a <?php
											switch ($block->siblingdiscount) {
												case 'amount':
													echo '&pound;' . number_format($block->siblingdiscount_amount, 2);
													break;
												case 'percentage':
													echo $block->siblingdiscount_amount . '%';
													break;
												case 'fixed':
													echo '&pound;' . number_format($block->siblingdiscount_amount, 2);
													break;
											}
											?> discount<?php if ($block->siblingdiscount != 'fixed') { ?> on each of them<?php } ?>
										</p>
									</div><?php
								}
								if ($block->autodiscount !== 'off') {
									?><div class="alert alert-info upsell upsell-autodiscount">
										<p><i class="<?php echo $fa_weight; ?> fa-info-circle"></i>
											Book <span class="lessons_needed"></span> more main session<span class="pl">s</span> to take advantage of a <?php
											switch ($block->autodiscount) {
												case 'amount':
													echo '&pound;' . number_format($block->autodiscount_amount, 2);
													break;
												case 'percentage':
													echo $block->autodiscount_amount . '%';
													break;
												case 'fixed':
													echo '&pound;' . number_format($block->autodiscount_amount, 2);
													break;
											}
											?> discount<?php if ($block->autodiscount != 'fixed') { ?> on each of them<?php } ?>
										</p>
									</div><?php
								}
							}
							?>
						</div>
					</div>
					</div><?php
				}
				?></div>
			</div>
		</div>
	</fieldset>
<?php
if (count($monitoring_fields) > 0) {
	$monitoring_msg = '';
	$monitoring_flag = 0;
	foreach ($monitoring_fields as $key => $label) {
		if ($monitoring_fields_configs[$key]["entry_type"]!="2") { continue; }
		$monitoring_flag = 1;
		$monitoring_msg.='<tr data-key="'.$key.'">
		<th scope="row"><label>'.$label.($monitoring_fields_configs[$key]["mandatory"]==1 ? "<em> *</em>" : "").'</label></th>
		</tr>';
	}
	?>
	<fieldset class="card card-custom" <?php echo ($monitoring_flag == 0)?'style="display:none"':'' ?>" >
	<div class="card-header">
		<div class="card-title">
			<h4 class="card-label with-line light">Supplementary Information</h4>
		</div>
	</div>
	<div id="monitoring" class="card-body">
		<script>
			var monitoring_existing = <?php echo json_encode($monitoring_existing); ?>;
		</script>
		<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<thead>
				<tr>
					<th scope="col"></th>
				</tr>
				</thead>
				<tbody>
				<?php
					echo $monitoring_msg;
				?>
				</tbody>
			</table>
		</div>
	</div>
	</fieldset><?php
}
?>
	<div class="card card-custom">
	<div class="card-header">
		<div class="card-title">
			<h4 class="card-label with-line light">Summary</h4>
		</div>
	</div>
	<div class="card-body">
	<div class="row summary">
<?php
if (!$in_crm) {
	?><div class="col-sm-12 col-md-8"><?php
if ($this->settings_library->get('require_full_payment', $this->cart_library->accountID) == 1) {
	?><p>When checking out you must pay the whole amount. If you choose not to check out now, your place will not be secured.</p><?php
} else {
	?><p>When checking out you can choose to pay the whole amount now or just a deposit. If you choose not to make a payment now, your place will not be secured.</p><?php
}
?></div><div class="col-sm-12 col-md-4 text-right totals"><?php
	} else {
	?><div class="col-sm-12 totals"><?php
}
?>
	<p>Sub Total: <?php echo currency_symbol($this->cart_library->accountID); ?><span id="sub_total"></span></p>
	<p>Discount: -<?php echo currency_symbol($this->cart_library->accountID); ?><span id="discount"></span></p>
<?php if(count($subs) > 0): ?>
	<div class="sub-total">
		<p>Subscription Total: <?php echo currency_symbol($this->cart_library->accountID); ?><span></span></p>
	</div>
<?php endif; ?>
	<p><strong>Total: <?php echo currency_symbol($this->cart_library->accountID); ?><span id="total"></span></strong></p>
	</div>
	</div>
	<button id='add-to-cart' class='btn <?php if ($in_crm) { echo 'btn-primary'.($already_in_cart ? " update" : " add")."-booking"; } else { echo 'btn-block'; } ?>'><?php
		if ($already_in_cart === TRUE) {
			echo 'Update';
		} else {
			echo 'Add to';
		}
		if ($this->cart_library->cart_type == 'booking') {
			echo ' Booking';
		} else {
			echo ' Booking Cart';
		}
		?></button>
	</div>
	</div>
	</div><?php
echo form_close(); ?>
		<div class="modal fade" id="myModal_message" role="dialog">
			<div class="modal-dialog modal-md">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-body" style="padding:0" id="verification">
						<div id="msg" class="alert alert-danger" style="margin: 0;">
						</div>
					</div>
				</div>
			</div>
		</div>
