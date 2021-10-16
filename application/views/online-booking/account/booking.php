<?php
display_messages($fa_weight);
if ($in_crm) {
	$data = array(
		'familyID' => $this->cart_library->familyID,
		'tab' => $tab
	);
	if(empty($ajax)){
		$this->load->view('participants/tabs.php', $data);
	}
	?><div class='row'>
		<div class='col-sm-12'>
			<div class='box bordered-box'>
				<div class='box-content box-double-padding'><?php
}
$blocks_current = array();
$blocks_past = array();
foreach ($blocks as $blockID => $block) {

	// session details
	$block_output_temp = '<div class="panel-group card card-custom" id="block' . $blockID . '" role="tablist" aria-multiselectable="true">
		<div class="panel panel-default card-body">
			<div class="panel-heading" role="tab" id="heading' . $blockID . '">
				<h4 class="panel-title">
					<a role="button" class="collapsed" data-toggle="collapse" data-parent="#block' . $blockID . '" href="#collapse' . $blockID . '" aria-expanded="false" aria-controls="collapse' . $blockID . '" role="button">
						Booking Breakdown
					</a>
				</h4>
			</div>
			<div id="collapse' . $blockID . '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading' . $blockID . '">
				<div class="panel-body">';
					$counterFlag = 0;
					if(isset($booked_sessions[$blockID]) && count($booked_sessions[$blockID]) > 0) {
						foreach ($booked_sessions[$blockID] as $date => $lessons) {
							$block_output_temp .= '<div class="row date flex">
							<div class="col-xs-12 col-sm-9">
								<i class="' . $fa_weight . ' fa-calendar-alt fa-fw"></i> <span>' . date('D jS M', strtotime($date)) . '</span>';
								foreach ($lessons as $lessonID => $participants) {
									if (!array_key_exists($date, $blocks[$blockID]->dates) || !array_key_exists($lessonID, $blocks[$blockID]->dates[$date])) {
										continue;
									}
									$lesson = $blocks[$blockID]->dates[$date][$lessonID];
									sort($participants);
									$block_output_temp .= '<br><i class="' . $fa_weight . ' fa-check-circle fa-fw"></i> ' . $lesson['time'] . ' - ' . $lesson['type'] . ' - <strong>' . implode(", ", $participants) . '</strong>';
								}
							$block_output_temp .= '</div>';
							$block_output1 = '';
							$counter = 0;
							if($subscriptions->num_rows() > 0){
								foreach ($subscriptions->result() as $sub_data){
									$carts = isset($cartArray[$blockID])?$cartArray[$blockID]:'';
									$childs = isset($childArray[$blockID])?$childArray[$blockID]:'';
									$contacts = (isset($contactArray[$blockID]))?$contactArray[$blockID]:'';
									if($carts == $sub_data->cartID && ($contacts == $sub_data->contactID || $childs == $sub_data->childID)) {
										$block_output1 .= '<div class="col-xs-12 col-sm-9">';
										$block_output1 .= '<i class="' . $fa_weight . ' fa-check-circle fa-fw"></i> Subscription: ' . $sub_data->subName . ' (' . currency_symbol($this->cart_library->accountID) . '<span>' . number_format($sub_data->price, 2) . '</span>) - <strong>' . (empty($sub_data->child_first) ? $sub_data->contact_first . ' ' . $sub_data->contact_last : (!empty($sub_data->contact_first) ? $sub_data->child_first . ' ' . $sub_data->child_last . ', ' . $sub_data->contact_first . ' ' . $sub_data->contact_last : $sub_data->child_first . ' ' . $sub_data->child_last)) . '</strong>';
										$block_output1 .= '</div>';
										$counter++;
									}
								}
							}

							$block_output_temp .= '<div class="col-xs-12 col-sm-3 right flex-middle">';
							$price = 0;
							$total = 0;
							foreach ($lessons as $lessonID => $participants) {
								$price += $session_prices[$blockID][$date][$lessonID];
								$total += $session_totals[$blockID][$date][$lessonID];
							}

							// dont show pricing if block priced
							if (!array_key_exists($blockID, $block_priced)) {
								$block_output_temp .= '<span class="price';
								if ($total < $price) {
									$block_output_temp .= ' discounted';
								}
								$block_output_temp .= '">';
								if ($price > 0) {
									$block_output_temp .= currency_symbol($this->cart_library->accountID) . '<span>' . number_format($price, 2) . '</span>';
								} else {
									if($counter == count($participants)){
										$block_output_temp .= 'Subscription';
									}else{
										$block_output_temp .= 'Free';
										$counterFlag = 1;
									}
								}
								$block_output_temp .= '</span>';
								if ($total < $price) {
									$block_output_temp .= '&nbsp;<span class="discounted_price">';
									if ($total > 0) {
										$block_output_temp .= currency_symbol($this->cart_library->accountID) . '<span>' . number_format($total, 2) . '</span>';
									} else {
										$block_output_temp .= 'Free';
									}
									$block_output_temp .= '</span>';
								}
							}
							$block_output_temp .= '</div>';

							$block_output_temp .= $block_output1;

						$block_output_temp.= '</div>';
						}
					}
					$block_output_temp .= '
				</div>
			</div>
		</div>
	</div>';


	// header
	$block_output = '<div class="row intro">';
	// hide images if viewing in crm
	if ($this->in_crm === TRUE) {
		$block->images = array();
	}
	if (count($block->images) > 0) {
		$data = array(
			'src' => $block->images[0]['thumb'],
			'alt' => $block->booking
		);
		$img = img($data);
		$block_output .= '<div class="hidden-xs col-sm-2">' . $img . '</div><div class="col-sm-12 col-sm-8">';
	} else {
		$block_output .=  '<div class="col-sm-12">';
	}
	$block_output .= '<h2 class="h3 light">' . $block->booking;
	if (isset($block_totals[$blockID]) && $block_totals[$blockID] > 0){
		$block_output .= ' - '. currency_symbol($this->cart_library->accountID) . number_format($block_totals[$blockID], 2);
	} else {
		if($counterFlag == 0)
			$block_output .= ' - Subscription';
		else
			$block_output .= ' - Free';
	}
	$block_output .= '</h2>';
	if(isset($booked_sessions[$blockID]) && count($booked_sessions[$blockID]) > 0) {
		$block_output .= '<p class="event-specs">';
		if (!empty($block->location)) {
			$block_output .= '<span><i class="' . $fa_weight . ' fa-map-marker-alt"></i> ' . $block->location . '</span> ';
		}
		$block_output .= '<span><i class="' . $fa_weight . ' fa-arrow-circle-right"></i> ' . $block->block . '</span>
		</p>';
	}
	if (count($block->attachments) > 0) {
		$attachments = array();
		foreach ($block->attachments as $path => $name) {
			$attachments[] = '<i class="' . $fa_weight . ' fa-paperclip"></i> ' . anchor('attachment/event/' . $path . '/' . $this->cart_library->accountID, $name, array('target' => '_blank'));
		}
		$block_output .= '<p class="attachments">' . implode(', ', $attachments) . '</p>';
	}
	$block_output .= '</div></div>';

	$block_output .= $block_output_temp;

	if (!empty($block->booking_instructions)) {
		$block_output .= $block->booking_instructions;
	}
	// store in array
	end($block->dates);
	$last_session_date = key($block->dates);
	$key = $last_session_date . '-' . $block->blockID;
	if (strtotime($last_session_date) <= strtotime(date('Y-m-d'))) {
		$blocks_past[$key] = $block_output;
	} else {
		$blocks_current[$key] = $block_output;
	}
}
if (count($blocks_current) > 0) {
	ksort($blocks_current);
	echo implode("\n", $blocks_current);
}
if (count($blocks_past) > 0) {
	krsort($blocks_past);
	echo '<div class="past-sessions"><hr><h2 class="h4">Previous Sessions</h2>';
	echo implode("\n", $blocks_past);
	echo '</div>';
}
if (!$this->in_crm) {
	?><p><a href="<?php echo site_url('account'); ?>#details" class="btn">Return to Bookings</a></p><?php
} else {
				?></div>
			</div>
		</div>
	</div><?php
}
