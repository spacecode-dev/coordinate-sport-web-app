<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $booking_info->type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
$url = parse_url($_SERVER['REQUEST_URI']);
$orderUrl = '';

if (isset($url['query'])){
	$orderUrl = '?' . $url['query'];
}
?>
<div class='row'>
    <div class='col-sm-12'>
		<div class='box bordered-box'>
			<div class='box-content box-double-padding'>
				<?php /*<div class='row'>
					<div class='col-sm-12'>
					<div class='box bordered-box<?php if ($search_fields['search'] == '') { echo " box-collapsed"; } ?>'>
							<div class='box-header box-header-small box-collapse'>
								<div class='title'>
									Search
								</div>
								<div class='actions'>
									<a class="btn box-collapse btn-xs btn-link" href="#"><i></i></a>
								</div>
							</div>
							<div class='box-content'>
								<?php echo form_open($page_base . '#results'); ?>
									<hr class="hr-normal" />
									<div class='row'>
										<div class="col-sm-12">
											<button class='btn btn-primary btn-submit' type="submit">
												<i class='far fa-search'></i> Search
											</button>
											<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
												Cancel
											</a>
										</div>
									</div>
								</div>
								<?php echo form_hidden('search', 'true'); ?>
							<?php echo form_close(); ?>
						</div>
					</div>
				</div>*/ ?>
				<div id='results'></div>
				<?php if($subs->num_rows() == 0): ?>
					<div class="alert alert-info">
						<i class="far fa-info-circle"></i>
						No subscriptions found. Do you want to <?php echo anchor('bookings/subscriptions/'.$bookingID.'/new/', 'create one'); ?>?
					</div>
				<?php else: ?>
					<?php echo $this->pagination_library->display($page_base); ?>
					<div class='row'>
						<div class='col-sm-12'>
							<div class='box bordered-box' style='margin-bottom:0;'>
								<div class='box-content box-no-padding'>
									<div class='responsive-table'>
										<div class='scrollable-area'>
											<table class='table table-striped table-bordered' style='margin-bottom:0;'>
												<thead>
													<tr>
														<th>
															Subscription Name
														</th>
														<th>
															Frequency
														</th>
														<th>
															Price
														</th>
														<th>
															No. of Sessions per Week
														</th>
														<th></th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($subs->result() as $row): ?>
														<tr class="subscription name">
															<td class="subName">
																<?php echo $row->subName ?>
															</td>

															<td class="frequency">
																<?php echo ucfirst($row->frequency) ?>
															</td>

															<td class="price">
																<?php echo $row->price ?>
															</td>

															<td>
															<?php
																/*$types = array();
																if (array_key_exists($row->subID, $session_types)) {
																	$types = $session_types[$row->subID];
																}
																// sort and remove empty
																$types = array_filter($types);
																$types = array_unique($types);
																sort($types);*/
																?><span><?php echo $row->no_of_sessions_per_week ?></span>
															</td>

															<td>
																<div class='text-right'>
																	<a class='btn btn-warning btn-xs confirm-update' data-message="Updating this subscription will alert all of the pariticipant customers about the changes?" href='<?php echo site_url('bookings/subscriptions/edit/' . $row->subID); ?>' title="Edit">
																		<i class='far fa-pencil'></i>
																	</a>
																	<a class='btn btn-danger btn-xs confirm-delete' data-message="Removing this subscription will alert all of the participant customers and will cancel any individual direct debits that have been set up." href='<?php echo site_url('bookings/subscriptions/remove/' . $row->subID); ?>' title="Remove">
																		<i class='far fa-trash'></i>
																	</a>
																</div>
															</td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
    </div>
</div>
