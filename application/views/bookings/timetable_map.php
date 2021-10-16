<?php
display_messages();
?>
<div class='row'>
    <div class='col-sm-12'>
        <div class='box bordered-box'>
            <div class='box-content box-double-padding'>
                <div id="results"></div>
                <?php
                if (count($markers) == 0) {
                    ?>
                    <div class="alert alert-info">
                        <i class="far fa-info-circle"></i>
                        No data found.
                    </div>
                    <?php
                } else {
                    ?><script>
                        var checkin_markers = <?php echo json_encode($markers); ?>;
                    </script>
                    <div id="checkin_map"></div><?php
                }
                ?>
            </div>
        </div>
    </div>
</div>