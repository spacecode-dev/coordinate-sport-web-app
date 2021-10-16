<?php
if (!$in_crm) {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
}
display_messages($fa_weight);
?>
<div class="row">
	<div class="col-sm-12 col-md-8">
		<div class="boxed">
			<?php
			$blocks_current = array();
			$blocks_past = array();
			$session_flag = 0;
			foreach ($blocks as $blockID => $block) {
				// if no sessions continue
				if (!array_key_exists($blockID, $cart_summary['sessions'])) {
					continue;
				}
				$block_output = '<div class="cart-item"><div class="row intro">';
				// hide images if viewing in crm
				if ($in_crm === TRUE) {
					$block->images = array();
				}
				if (count($block->images) > 0) {
					$data = array(
						'src' => $block->images[0]['thumb'],
						'alt' => $block->booking
					);
					$img = img($data);
					$block_output .= '<div class="hidden-xs col-sm-2">' . $img . '</div><div class="col-xs-12 col-sm-10">';
				} else {
					$block_output .= '<div class="col-sm-12">';
				}
				$block_output .= '<a href="' . site_url($cart_base . 'cart/remove/' . $blockID) . '" class="remove">Remove Item</a>';
				foreach($cart_summary['subscriptions'] as $remove_sub_data){
					if($block->bookingID === $remove_sub_data->bookingID){
						$block_output .= '<br/><a href="' . site_url($cart_base . 'cart/remove_subscription/' . (empty($remove_sub_data->childID)?$remove_sub_data->contactID: $remove_sub_data->childID)) . '/'.$remove_sub_data->bookingID.'" class="remove">
					Remove Subscription for '.(!empty($remove_sub_data->child_name)?$remove_sub_data->child_name:$remove_sub_data->contact_name).
							'</a>';
					}
				}
				$block_output .= '<h2 class="h3 light">' . $block->booking . ' - ';
				if (isset($cart_summary['block_totals'][$blockID]) && $cart_summary['block_totals'][$blockID] > 0){
					$block_output .= currency_symbol($this->cart_library->accountID) . number_format($cart_summary['block_totals'][$blockID], 2);
				} elseif (!empty($cart_summary['subscriptions'])) {
					$block_output .= 'Included with subscription';
				} else {
					$block_output .= 'Free';
				}
				$block_output .= '</h2><p class="event-specs">';
				if (!empty($block->location)) {
					$block_output .= '<span><i class="' .  $fa_weight . ' fa-map-marker-alt"></i> ' .  $block->location . '</span> ';
				}
				if(isset($cart_summary['sessions'][$blockID]) && $block->subscriptions_only !== '1'){
					$block_output .= '<span><i class="' .  $fa_weight . ' fa-arrow-circle-right"></i> ' .  $block->block . '</span>';
				}

				$block_output .= '</p>
				</div></div>
				<div class="panel-group mb-10" id="block' . $blockID . '" role="tablist" aria-multiselectable="true">
					<div class="panel panel-default">
						<div class="panel-heading" role="tab" id="heading' . $blockID . '">
							<h4 class="panel-title">
								<a role="button" class="collapsed" data-toggle="collapse" data-parent="#block' . $blockID . '" href="#collapse' . $blockID . '" aria-expanded="false" aria-controls="collapse' . $blockID . '">
									Booking Breakdown
								</a>
							</h4>
						</div>
						<div id="collapse' . $blockID . '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading' . $blockID . '">
							<div class="panel-body">';
				if(isset($cart_summary['sessions'][$blockID])) {
					//Sort booking breakdown by date
					uksort($cart_summary['sessions'][$blockID], function($a,$b) { $a = strtotime($a); $b=strtotime($b); return ($a == $b ? 0 : ($a < $b) ? -1 : 1); });
					foreach ($cart_summary['sessions'][$blockID] as $date => $participants) {
						$subscription_flag = 0;
						$block_output .= '<div class="row date flex">
										<div class="col-xs-12 col-sm-9">';
						$block_output .= '<i class="' . $fa_weight . ' fa-calendar-alt fa-fw"></i> <span>' . date('D jS M', strtotime($date)) . '</span><br/>';
						foreach ($participants as $participant => $sessions) {
							$block_output .= '<i class="far fa-check-circle fa-fw"></i> ' . $participant . ' - ' . implode(", ", $sessions) . '<br/>';
							if(isset($cart_summary['sessions_subscriptions'][$blockID][$date][$participant])) {
								$block_output .= 'Subscription: ' . $cart_summary['sessions_subscriptions'][$blockID][$date][$participant].'<br>';
								$subscription_flag ++;
							}
						}
						/*if($session_flag === 0 && isset($cart_summary['subscriptions'][$blockID]) && count($cart_summary['subscriptions'][$blockID]) > 0) {
							foreach ($cart_summary['subscriptions'][$blockID] as $subscription) {
								$block_output .= '<i class="far fa-check-circle fa-fw"></i> ' . (!empty($subscription->child_name) ? $subscription->child_name : $subscription->contact_name) . '<br>';
								$block_output .= 'Subscription: ' . $subscription->subName . '(' . currency_symbol($this->cart_library->accountID) . number_format($subscription->price, 2) . ' - ' . $subscription->frequency . ') <br>';
							}
							$session_flag = 1;
						}*/
						$block_output .= '</div>
										<div class="col-xs-12 col-sm-3 right flex-middle">';
						// dont show pricing if block priced
						if (!array_key_exists($blockID, $cart_summary['block_priced'])) {
							$block_output .= '<span class="price';
							if ($cart_summary['sessions_totals'][$blockID][$date] < $cart_summary['sessions_subtotals'][$blockID][$date]) {
								$block_output .= ' discounted';
							}
							$block_output .= '">';
							if ($cart_summary['sessions_subtotals'][$blockID][$date] > 0) {
								$block_output .= currency_symbol($this->cart_library->accountID) . '<span>' . number_format($cart_summary['sessions_subtotals'][$blockID][$date], 2) . '</span>';
							} else {
								if(count($participants) == $subscription_flag)
									$block_output .= 'Subscription';
								else
									$block_output .= 'Free';
							}
							$block_output .= '</span>';
							if ($cart_summary['sessions_totals'][$blockID][$date] < $cart_summary['sessions_subtotals'][$blockID][$date]) {
								$block_output .= '&nbsp;<span class="discounted_price">';
								if ($cart_summary['sessions_totals'][$blockID][$date] > 0) {
									$block_output .= currency_symbol($this->cart_library->accountID) . '<span>' . number_format($cart_summary['sessions_totals'][$blockID][$date], 2) . '</span>';
								} else {
									$block_output .= 'Free';
								}
								$block_output .= '</span>';
							}
						}
						$block_output .= '</div>
									</div>';
					}
				}else{
					$block_output .= '<div class="row date flex">
										<div class="col-xs-12 col-sm-9">';
					foreach($cart_summary['subscriptions'][$blockID] as $subscription){
						$block_output .= '<i class="far fa-check-circle fa-fw"></i> ' . (!empty($subscription->child_name)?$subscription->child_name:$subscription->contact_name) . '<br>';
						$block_output .= 'Subscription: ' . $subscription->subName . '(' . currency_symbol($this->cart_library->accountID) . number_format($subscription->price, 2).' - '. $subscription->frequency .')<br>';
					}
					$block_output .= '</div><div class="col-xs-12 col-sm-3 right flex-middle">';
					$block_output .= '<span class="price">';
					$block_output .= 'Subscription';
					$block_output .= '</span>';
					$block_output .= '</div>
									</div>';

				}
				$block_output .= '</div>
						</div>
					</div>
				</div>
				<a href="' . site_url($cart_base . 'book/' . $blockID) . '" class="btn';
				if ($in_crm) {
					$block_output .= ' btn-primary';
				} else {
					$block_output .= ' btn-block btn-hollow';
				}
				$block_output .= '">Edit Booking</a></div>';
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
			?>
		</div>
	</div>
	<div class="col-sm-12 col-md-4 summary">
		<?php
		$data = array(
			'checkout' => $checkout,
			'cart_summary' => $cart_summary,
			'fa_weight' => $fa_weight,
			'in_crm' => $in_crm
		);
		$this->load->view('online-booking/cart/partials/summary');
		?>
	</div>
</div>
