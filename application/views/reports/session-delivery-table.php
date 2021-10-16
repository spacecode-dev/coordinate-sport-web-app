<div class='card'>
	<div class="fixed-scrollbar"></div>
	<div class='table-responsive'>
		<table class='table table-striped table-bordered table-pages'>
			<thead>
			<tr>
				<th>School/Organisation Name</th>
				<th>Date</th>
				<th>Session Times</th>
				<th>Day</th>
				<th>Activity</th>
				<th>Session Type</th>
				<th>Post Code</th>
				<th>Class Size</th>
				<th>Head Coach</th>
				<th>Lead Coach</th>
				<th>Assistant Coach</th>
				<th>Main Contact</th>
				<th>Telephone Number</th>
			</tr>
			</thead>
			<tbody>
			<?foreach ($lessons as $lesson) { ?>
				<tr>
					<td><? echo $lesson['org']; ?></td>
					<td><? echo $lesson['date']; ?></td>
					<td><? echo $lesson['time']; ?></td>
					<td><? echo ucfirst($lesson['day']); ?></td>
					<td><? echo $lesson['activity_name']; ?></td>
					<td><? echo $lesson['type_name']; ?></td>
					<td><? echo $lesson['post_code']; ?></td>
					<td><? echo $lesson['class_size']; ?></td>
					<td>
						<? foreach ($lesson['headcoaches'] as $coach) {
							echo $coach; ?> <br>
						<? } ?>
					</td>
					<td>
						<? foreach ($lesson['leadcoaches'] as $coach) {
							echo $coach; ?> <br>
						<? } ?>
					</td>
					<td>
						<? foreach ($lesson['assistantcoaches'] as $coach) {
							echo $coach; ?> <br>
						<? } ?>
					</td>
					<td><? echo $lesson['main_contact']; ?></td>
					<td><? echo $lesson['main_tel']; ?></td>
				</tr>
			<?} ?>
			</tbody>
		</table>
	</div>
</div>
