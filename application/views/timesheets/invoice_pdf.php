<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Invoice</title>
		<style>
		body {
			font-family: Arial, sans-serif;
			font-size:14px;
		}
		table {
			width:100%;
			border:0;
			border-spacing:0;
			border-collapse:collapse;
		}
		th, td {
			border:0;
			vertical-align: top;
		}
		table.items th, table.items td {
			border:1px solid #000;
			padding:5px;
		}
		h1, h2 {
			font-size:30px;
			margin-top:0;
		}
		h2 {
			text-align:right;
			color:#CCC;
		}
		h3, h4 {
			font-size:18px;
			margin-bottom:10px;
		}
		h4 {
			padding-top:15px;
		}
		</style>
	</head>
	<body>
		<table>
			<tr>
				<td width="50%">
					<h1><?php echo $staff->first . ' ' . $staff->surname; ?></h1>
					<p><strong>Address</strong>:<br /><?php
					$addresses_array = array();
					if (!empty($staff->address1)) {
						$addresses_array[] = $staff->address1;
					}
					if (!empty($staff->address2)) {
						$addresses_array[] = $staff->address2;
					}
					if (!empty($staff->town)) {
						$addresses_array[] = $staff->town;
					}
					if (!empty($staff->county)) {
						$addresses_array[] = $staff->county;
					}
					if (!empty($staff->postcode)) {
						$addresses_array[] = $staff->postcode;
					}
					if (count($addresses_array) > 0) {
						echo implode("<br />", $addresses_array);
					}
					?></p>
					<p><strong>Tel:</strong> <?php
					if (!empty($staff->phone)) {
						echo $staff->phone;
					} else if (!empty($staff->mobile)) {
						echo $staff->mobile;
					}
					?></p>
					<?php
					if (!empty($staff->email)) {
						?><p><strong>Email:</strong> <?php echo $staff->email; ?></p><?php
					}
					if (!empty($invoice->utr)) {
						?><p><strong>UTR:</strong> <?php echo $invoice->utr; ?></p><?php
					}
					if (!empty($invoice->buyer_id)) {
						?><h3>Buyer ID: <?php echo $invoice->buyer_id; ?></h3><?php
					}
					?>
				</td>
				<td width="50%">
					<h2>INVOICE</h2>
					<p><strong>Invoice No:</strong> <?php echo $invoice_prefix . $invoice->number; ?></p>
					<p><strong>Date:</strong> <?php echo mysql_to_uk_date($invoice->date); ?></p>
					<?php
					if ($this->settings_library->get('staff_invoice_address') != '') {
						?><p><strong>To:</strong><br />
						<?php echo nl2br($this->settings_library->get('staff_invoice_address')); ?></p><?php
					}
					?>
					<p><strong>For:</strong><br />
					<?php echo $invoice->subject; ?></p>
				</td>
			</tr>
		</table>
		<table class="items">
			<thead>
				<tr>
					<th>
						Description
					</th>
					<th>
						Amount
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if (count($invoice_rows) > 0) {
					foreach ($invoice_rows as $row) {
						?><tr>
							<td>
								<?php echo $row['desc']; ?>
							</td>
							<td>
								<?php echo currency_symbol() . number_format($row['amount'], 2); ?>
							</td>
						</tr><?php
					}
				}
				?>
				<tr>
					<td align="right"><strong>Total</strong></td>
					<td><?php echo currency_symbol() . number_format($invoice->amount, 2); ?></td>
				</tr>
			</tbody>
		</table>
		<h4>BACS details</h4>
		<p>Account name: <?php echo $invoice->bank_name; ?><br />
		Account no.: <?php echo $invoice->bank_account; ?><br />
		Sort code: <?php echo $invoice->bank_sort_code; ?></p>
	</body>
</html>
