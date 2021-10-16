<?php
display_messages();
if ($staffID != NULL) {
    $data = array(
        'staffID' => $staffID,
        'tab' => $tab
    );
    $this->load->view('staff/tabs.php', $data);
}
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
	<div class="card-header">
		<div class="card-title">
			<h3 class="card-label">Search</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
        <div class='row'>
            <div class='col-sm-2'>
                <p>
                    <strong><label for="field_date_from">Date From</label></strong>
                </p>
                <?php
                $data = array(
                    'name' => 'search_date_from',
                    'id' => 'field_date_from',
                    'class' => 'form-control datepicker',
                    'value' => $search_fields['date_from']
                );
                echo form_input($data);
                ?>
            </div>
            <div class='col-sm-2'>
                <p>
                    <strong><label for="field_date_to">Date To</label></strong>
                </p>
                <?php
                $data = array(
                    'name' => 'search_date_to',
                    'id' => 'field_date_to',
                    'class' => 'form-control datepicker',
                    'value' => $search_fields['date_to']
                );
                echo form_input($data);
                ?>
            </div>
        </div>
    </div>
    <div class='card-footer'>
    	<div class="d-flex justify-content-between">
    		<button class='btn btn-primary btn-submit' type="submit">
    			<i class='far fa-search'></i> Search
    		</button>
    		<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
    			Cancel
    		</a>
    	</div>
    </div>
    <?php echo form_hidden('search', 'true'); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
if (count($markers) == 0) {
    ?>
    <div class="alert alert-info">
        No data found.
    </div>
    <?php
} else {
    ?><script>
        var checkin_markers = <?php echo json_encode($markers); ?>;
    </script>
    <div id="checkin_map"></div><?php
}
