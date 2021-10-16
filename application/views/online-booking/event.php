<h1 class="with-line"><?php echo $block->booking; ?></h1>
<?php
display_messages('fas');
if (count($block->images) > 0) {
	?><div class="row event-gallery"><?php
		foreach ($block->images as $image) {
			?><div class="col-xs-3"><?php
				$data = array(
					'src' => $image['thumb'],
					'alt' => $block->booking
				);
				$img = img($data);
				echo anchor($image['full'], $img);
			?></div><?php
		}
	?></div><?php
}
?>
<div class="row event-detail">
	<div class="col-xs-12 col-md-8">
		<p class="event-specs">
			<?php
			if (!empty($block->location)) {
				?><span><i class="fas fa-map-marker-alt"></i> <?php echo $block->location; ?></span> <?php
			}
			?>
			<span><i class="fas fa-arrow-circle-right"></i> <?php echo $block->block; ?></span>
		</p>
		<p class="event-specs">
			<span><i class="fas fa-calendar-alt"></i> <?php echo mysql_to_uk_date($block->startDate); ?> to <?php echo mysql_to_uk_date($block->endDate); ?></span>
			<?php
			if (!empty($block->min_age) && !empty($block->max_age)) {
				?><span><i class="fas fa-user"></i> Age Range: <?php
				echo $block->min_age;
				if ($block->max_age > $block->min_age) {
					echo '-' . $block->max_age;
				}
				?></span><?php
			} else if (!empty($block->min_age) && empty($block->max_age)) {
				?><span><i class="fas fa-user"></i> Minimum Age: <?php
				echo $block->min_age;
				?></span><?php
			} else if (empty($block->min_age) && !empty($block->max_age)) {
				?><span><i class="fas fa-user"></i> Maximum Age: <?php
				echo $block->max_age;
				?></span><?php
			}
			?>
		</p>
		<?php
		if (count($block->lesson_types_summary) > 0) {
			?><p class="event-specs"><?php
			foreach ($block->lesson_types_summary as $label => $price) {
				?><span><i class="fas fa-info-circle"></i> <?php echo $label . ': ' . $price; ?></span><?php
			}
			?></p><?php
		}
		if(count($block->subs) > 0) {
			?><p class="event-specs"><span><i class="fas fa-credit-card"></i> Available Subscriptions: 
			<?php
			$i = 1;
				foreach($block->subs as $sub) {
					echo $sub;
					if($i < count($block->subs)) {
						echo ', ';
					}
					$i++;
				} ?>
			</span></p><?php
		}
		

		if (!empty($block->availability_status)) {
			?><p class="availability <?php echo $block->availability_status_class; ?>">
				<i class="fas fa-circle"></i> Availability - <?php echo $block->availability_status; ?>
			</p><?php
		}
		if (!empty($block->website_description)) {
			echo "<p class=\"desc\">" . nl2br($block->website_description) . "</p>";
		}
		?>
	</div>
	<div class="col-xs-12 col-md-4">
		<?php
		if (is_array($block->coordinates)) {
			$markers = array(
				array(
					'label' => $block->booking,
					'link' => site_url('event/' . $blockID),
					'color' => $block->colour,
					'lat' => $block->coordinates[0],
					'lng' => $block->coordinates[1]
				)
			);
			?><script>
				var map_markers = <?php echo json_encode($markers); ?>;
			</script>
			<div id="map"></div><?php
		}
		if ($block->disable_online_booking != 1 && $block->availability_status != 'Sold Out') {
			?><p><a href="<?php echo site_url('book/' . $blockID); ?>" class="btn btn-block btn-hollow btn-lg">Book Now</a></p><?php
		}
		?>
	</div>
</div>
