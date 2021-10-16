


<div class='card'>
	<div class="fixed-scrollbar"></div>
	<div class='table-responsive'>
		<table border="1" class='table table-striped table-bordered'>
			<thead>
			<tr>
				<th align="left" >Account Holder</th>
				<th align="left" >Account Balance</th>
				<th align="left" >Email Address</th>
				<th align="left" >Phone Number</th>
			</tr>
			</thead>
			<tbody>
				<?php if ($families->num_rows() == 0) { ?>
					<tr>
						<td colspan="4"> No data </td>
					</tr>
				<?php }else{
					foreach ($families->result() as $row) {
						if($row->account_balance < 0){
							echo "<tr>
								<td>".$row->contact_first.' '.$row->contact_last."</td>
								<td>".$row->account_balance."</td>
								<td>".$row->account_balance."</td>
								<td>".$row->phone."</td>
							</tr>";
						}
					}
				} ?>
			</tbody>
		</table>
	</div>
</div>
