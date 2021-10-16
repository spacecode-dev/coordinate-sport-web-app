<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons-scroll fixed-1 mb-0">
			<?php
			foreach ($resources as $key => $value) {
				?><li class='nav-item'>
					<a href='<?php echo site_url('resources/' . $key); ?>' class="nav-link<?php if ($resource['resourceID'] == $key) { echo ' active'; } ?>">
						<?php echo $value['resourceName']; ?>
					</a>
				</li><?php
			}
			?>
		</ul>
	</div>
</div>
