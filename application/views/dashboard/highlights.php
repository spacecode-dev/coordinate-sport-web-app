<?php
if (count($items) > 0) {
	foreach ($items as $item) {
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
		?><div class="col-sm-6 col-md-2">
			<div class="card card-custom card-compact card-statistic">
				<a href="<?php echo $item['link']; ?>" class="card-body">
					<h3 class='title text-<?php echo $color; ?>'><?php echo $item['count']; ?></h3>
					<small><?php echo $item['text']; ?></small>
					<div class='text-<?php echo $color; ?> far fa-<?php echo $icon; ?> align-right'></div>
				</a>
			</div>
		</div><?php
	}
}
