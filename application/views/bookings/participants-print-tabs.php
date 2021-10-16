<div class='noprint'>
	<?php
	
	if ($print_view === TRUE) {
		?><p>Select register to view:</p><?php
	}
	?>
	<div class="card card-custom noprint">
		<div class="card-header card-header-tabs-line">
			<ul class="nav nav-tabs nav-bold nav-tabs-line">
				<li class="nav-item">
					<?php if((empty($lessonID))){ ?>
						<a href='javascript:void(0)' style="color:#000" class="nav-link">Overview </a>
					<?php }else{ ?>
						<a href='<?php echo site_url($page_base); ?>' class="nav-link<?php if (empty($lessonID)) { echo " active"; } ?>">
							Overview
						</a>
					<?php } ?>
				</li>
				<?php
				if (count($tabs) > 0) {
					foreach ($tabs as $key => $value) {
						if($lessonID == $key){
						?><li class="nav-item">
							<a href='javascript:void(0)' style="color:#000" class="nav-link"><?php echo $value['desc']; ?></a>
						</li><?php
						}else{ ?>
						<li class="nav-item">
							<a href='<?php echo site_url($page_base . '/' . $key); ?>' class="nav-link <?php echo ($lessonID == $key)?'active':''; ?>"><?php echo $value['desc']; ?></a>
						</li>
						<?php }
					}
				}
				?>
			</ul>
		</div>
	</div>
</div>
