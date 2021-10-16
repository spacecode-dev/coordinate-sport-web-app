<?php
if (!isset($in_crm) || $in_crm !== TRUE) {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
	display_messages('fas');
} else {
	display_messages();
}
