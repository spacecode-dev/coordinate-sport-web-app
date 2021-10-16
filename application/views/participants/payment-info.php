<html>
	<head>
		<title>Payment Info</title>
		<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/print.css'); ?>" />
	</head>
	<body>
		<h1>Payment Info</h1>
		<?php
		echo "<p><strong>Amount:</strong> " . currency_symbol() . number_format($amount, 2) . "</p>";
		if (!empty($vendortxcode)) {
			echo "<p><strong>VendorTxCode:</strong> " . $vendortxcode . "</p>";
		}
		echo "<p><strong>Name:</strong> " . trim(ucwords($contact_info->title) . " " . $contact_info->first_name . " " . $contact_info->last_name) . "</p>";
		$address_parts = array();
		if (!empty($contact_info->address1)) {
			$address_parts[] = $contact_info->address1;
		}
		if (!empty($contact_info->address2)) {
			$address_parts[] = $contact_info->address2;
		}
		if (!empty($contact_info->address3)) {
			$address_parts[] = $contact_info->address3;
		}
		if (!empty($contact_info->town)) {
			$address_parts[] = $contact_info->town;
		}
		if (!empty($contact_info->county)) {
			$address_parts[] = $contact_info->county;
		}
		if (!empty($contact_info->postcode)) {
			$address_parts[] = $contact_info->postcode;
		}
		if (count($address_parts) > 0) {
			echo "<p><strong>Address:</strong> " . implode("<br />", $address_parts) . "</p>";
		}
		$numbers = array();
		if (!empty($contact_info->phone)) {
			$numbers[] = $contact_info->phone;
		}
		if (!empty($contact_info->mobile)) {
			$numbers[] = $contact_info->mobile . " (Mobile)";
		}
		if (!empty($contact_info->workPhone)) {
			$numbers[] = $contact_info->workPhone . " (Work)";
		}
		if (count($numbers) > 0) {
			echo "<p><strong>Phone Numbers:</strong> " . implode("<br /> ", $numbers) . "</p>";
		}
		if (!empty($contact_info->email)) {
			echo "<p><strong>Email:</strong> " . $contact_info->email . "</p>";
		}
		?>
	</body>
</html>
