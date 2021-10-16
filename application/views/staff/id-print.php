<!DOCTYPE HTML>
<html>
<head>
	<title>Coach ID</title>
	<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/coach-id.css'); ?>" />
</head>
<body class="account-<?php echo $this->auth->user->accountID; ?>">
	<div class="noprint intro">
		<h1>Coach ID</h1>
		<p class="select">1. Select Style: <?php
		$list = array();
		$list[] = '<a href="#" class="active" data-logo="/attachment/setting/logo">Default</a>';
		$styles = array();
		if ($brands->num_rows() > 0)  {
			foreach ($brands->result() as $brand) {
				$logo = '/attachment/setting/logo';
				if (!empty($brand->logo_path)) {
					$logo = '/attachment/brand/' . $brand->logo_path;
				}
				$list[] = '<a href="#" class="brand-' . $brand->brandID . '" data-logo="' . $logo . '">' . $brand->name . '</a>';
				$styles[] = ".brand-" . $brand->brandID . " .job, .brand-" . $brand->brandID . " .name, .brand-" . $brand->brandID . " .crb, .brand-" . $brand->brandID . " .quals, .brand-" . $brand->brandID . " .statement, .brand-" . $brand->brandID . " .specialism, .brand-" . $brand->brandID . " .quote, .brand-" . $brand->brandID . " .hero {
					background:rgba(" . implode(",", hex_to_rgb(brand_colour($brand->colour))) . ", .1);
					border-color:" . brand_colour($brand->colour) . ";
					color: #000;
				}
				.brand-" . $brand->brandID . " .name, .brand-" . $brand->brandID . " .specialism, .brand-" . $brand->brandID . " .quals, .brand-" . $brand->brandID . " .hero {
					background:rgba(" . implode(",", hex_to_rgb(brand_colour($brand->colour))) . ", .3);
				}
				.brand-" . $brand->brandID . " .contact {
					background:" . brand_colour($brand->colour) . ";
					border-color:" . brand_colour($brand->colour) . ";
				}";
			}
		}
		echo implode(", ", $list);
		?></p>
		<p>2. <a href="#" class="print">Print</a></p>
	</div>
	<style>
		<?php echo implode("\n", $styles); ?>
	</style>
	<div class="page">
		<div class="top_box">
			<div class="half">
				<img src="<?php echo site_url('attachment/setting/logo'); ?>" alt="Logo" id="logo" />
				<div class="box job">
					<h2>Job Title:</h2>
					<p><?php echo $staff_info->jobTitle; ?></p>
				</div>
			</div>
			<div class="half">
				<div class="photo">
					<?php
					if (!empty($staff_info->profile_pic)) {
						$data = array(
							'src' => 'attachment/staff_profile_pic/profile_pic/' . $staff_info->staffID,
							'alt' => $staff_info->first,
						);
						echo img($data);
					}
					elseif (!empty($staff_info->id_photo_path)) {
						?><img src="<?php echo site_url('attachment/staff-id/' . $staff_info->id_photo_path); ?>" alt="<?php echo $staff_info->first; ?>" /><?php
					}
					?>
				</div>
			</div>
		</div>

		<div class="box name">
			<h2>Name:</h2>
			<p><?php echo (!empty($staff_info->title))?ucwords($staff_info->title) . ". ":""; echo $staff_info->first.' '.$staff_info->surname; ?></p>
		</div>

		<?php
		$crb_number = 'Unknown';
		$crb_expiry = 'Unknown';
		if ($staff_info->qual_fsscrb == 1) {
			$crb_number = $staff_info->qual_fsscrb_ref;
			if (!empty($staff_info->qual_fsscrb_expiry_date)) {
				$crb_expiry = date("d/m/Y", strtotime($staff_info->qual_fsscrb_expiry_date));
			}
		} else if ($staff_info->qual_othercrb == 1) {
			$crb_number = $staff_info->qual_othercrb_ref;
			if (!empty($staff_info->qual_othercrb_expiry_date)) {
				$crb_expiry =  date("d/m/Y", strtotime($staff_info->qual_othercrb_expiry_date));
			}
		}
		?>

		<div class="box crb">
			<div class="half">
				<h2>DBS Number:</h2>
				<p><?php echo $crb_number; ?></p>
			</div>
			<div class="half">
				<h2>Expiry Date:</h2>
				<p><?php echo $crb_expiry; ?></p>
			</div>
		</div>

		<div class="box quals">
			<h2>Qualifications:</h2>
			<p><?php
			$qualList = array();

			if ($quals->num_rows() > 0) {
				foreach ($quals->result() as $row) {
					if (!empty($row->level)) {
						$qual = "Level " . $row->level . " ";
					} else {
						$qual = null;
					}
					$qual .= $row->name;
					$qualList[] = $qual;
				}
			}

			if ($staff_info->qual_child == 1 && (empty($staff_info->qual_child_expiry_date) || strtotime($staff_info->qual_child_expiry_date) > time())) {
				$qualList[] = "Child Protection";
			}

			if ($staff_info->qual_first == 1 && (empty($staff_info->qual_first_expiry_date) || strtotime($staff_info->qual_first_expiry_date) > time())) {
				$qualList[] = "First Aid";
			}

			if (count($qualList) > 0) {
				echo implode(", ", $qualList);
			} else {
				echo "<em>None</em>";
			}
			?></p>
		</div>

	</div>

	<div class="page page-2">

		<div class="box statement">
			<h2>Personal Statement:</h2>
			<?php
			// convert pre-wysiwyg fields to html
			if ($staff_info->id_personalStatement == strip_tags($staff_info->id_personalStatement)) {
				$staff_info->id_personalStatement = '<p>' . nl2br($staff_info->id_personalStatement) . '</p>';
			}
			echo $staff_info->id_personalStatement;
			?>
		</div>

		<div class="box specialism">
			<h2>Specialism:</h2>
			<p><?php echo $staff_info->id_specialism; ?></p>
		</div>

		<div class="box quote">
			<h2>Favourite Quote:</h2>
			<p>&quot;<?php echo $staff_info->id_favQuote; ?>&quot;</p>
		</div>

		<div class="box hero">
			<h2>Sporting Hero:</h2>
			<p><?php echo $staff_info->id_sportingHero; ?></p>
		</div>

		<div class="box contact">
			<div class="half">
				<p><?php echo $this->settings_library->get('address'); ?></p>
			</div>
			<div class="half">
				<?php
				$parts = array();
				if (!empty($this->settings_library->get('phone'))) {
					$parts[] = 't. ' . $this->settings_library->get('phone');
				}
				if (!empty($this->settings_library->get('email'))) {
					$parts[] = 'e. ' . $this->settings_library->get('email');
				}
				if (!empty($this->settings_library->get('website'))) {
					$parts[] = 'w. ' . parse_url($this->settings_library->get('website'), PHP_URL_HOST);
				}
				if (count($parts) > 0) {
					echo '<p>' . implode("<br />", $parts) . '</p>';
				}
				?>
			</div>
		</div>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="<?php echo $this->crm_library->asset_url('dist/js/components/coach-id.js'); ?>"></script>
</body>
</html>
