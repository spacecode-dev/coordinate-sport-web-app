<?php
display_messages();

if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
?>
<div class='row'>
	<div class='col-sm-12'>
		<div class='box bordered-box'>
			<div class='box-content box-double-padding'><?php
				echo form_open_multipart($submit_to, 'class="family"');
					$data = array(
						'contact_info' => $contact_info,
						'brands' => $brands
					);
					$this->load->view('participants/privacy-inlay.php', $data);
					?>
					<div class='form-actions'>
						<div class="row">
							<div class='col-sm-7 col-sm-offset-5'>
								<button class='btn btn-primary btn-submit' type="submit">
									<i class='far fa-save'></i> Update
								</button>
								<a href="<?php echo site_url($return_to); ?>" class="btn">Cancel</a>
							</div>
						</div>
					</div><?php
				echo form_close();
				?>
			</div>
		</div>
	</div>
</div>
