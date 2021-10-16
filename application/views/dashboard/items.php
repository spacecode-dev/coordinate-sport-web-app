<div<?php
	if (isset($id) && !empty($id)) {
		echo ' id="' . $id . '"';
	}
	/*if (isset($type) && !empty($type)) {
		echo ' id="' . $type . '"';
	}*/
	if (isset($postcode) && !empty($postcode)) {
		echo ' data-postcode="' . $postcode . '"';
	}
	if (isset($date) && !empty($date)) {
		echo ' data-date="' . $date . '"';
	}
	if (isset($startTime) && !empty($startTime)) {
		echo ' data-start_time="' . $startTime . '"';
	}
	if (isset($endTime) && !empty($endTime)) {
		echo ' data-end_time="' . $endTime . '"';
	}
	?>>
	<?php
	if (isset($name) && !empty($name)) {
		?><div class="card card-custom card-compact">
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label"><?php echo $name; ?></h3>
				</div>
			</div>
			<div class="card-body no-padding"><?php
	}
	?><ol class="item-list">
		<?php
		if (count($items) == 0) {
			$items[] = array(
				'text' => 'None',
				'status' => 'green'
			);
		}
		foreach ($items as $item) {
			?><li data-status="<?php echo $item['status']; ?>"<?php
			if (isset($item['prev_postcode']) && !empty($item['prev_postcode'])) {
				echo ' data-postcode-prev="' . $item['prev_postcode'] . '"';
			}
			if (isset($item['next_postcode']) && !empty($item['next_postcode'])) {
				echo ' data-postcode-next="' . $item['next_postcode'] . '"';
			}
			if (isset($item['prev_time']) && !empty($item['prev_time'])) {
				echo ' data-time-prev="' . $item['prev_time'] . '"';
			}
			if (isset($item['next_time']) && !empty($item['next_time'])) {
				echo ' data-time-next="' . $item['next_time'] . '"';
			}
			?>>
				<?php
				if (isset($item['link'])) {
					echo '<a class="view-bookings-toggle" href="' . $item['link'] .'" class="dd-handle"';
					if (isset($item['target'])) {
						echo ' target="' . $item['target'] . '"';
					}
					echo '>';
				} else {
					echo '<span class="dd-handle">';
				}
				switch ($item['status']) {
					case 'green':
					default:
						$icon = 'check';
						$color = 'success';
						break;
					case 'amber':
						$icon = 'exclamation';
						$color = 'warning';
						break;
					case 'red':
						$icon = 'times';
						$color = 'danger';
						break;
				}
				// show different icons depending on id
				if (isset($id)) {
					switch ($id) {
			 			case 'staff_birthdays':
							$icon = 'birthday-cake';
							break;
						case 'top_staff':
							$icon = 'star';
							$color = 'info';
							break;
					}
				}
				echo $item['text'];
				if (isset($item['link'])) {
					echo '</a>';
				} else {
					echo '</span>';
				}
				?>
				<div class="actions"><i class="text-<?php echo $color; if (!isset($item['count'])) { echo ' far fa-' . $icon; }?>"><?php if (isset($item['count'])) { echo $item['count']; } ?></i></div>
			</li><?php
		}
		?>
	</ol><?php
	if (isset($name) && !empty($name)) {
			?></div>
		</div><?php
	}
	?>
</div>
