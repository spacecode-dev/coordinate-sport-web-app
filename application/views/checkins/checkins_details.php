<?php
if (count($details_data) == 0) {
    ?>
    <div class="alert alert-info">
        No data found.
    </div>
    <?php
} else { ?>
    <div class='card card-custom'>
    	<div class='table-responsive'>
	        <table class='table table-striped table-bordered'>
	            <thead>
	            <tr>
	                <th>Staff Name</th>
	                <th>First Session Details</th>
	                <th>First Session Time</th>
	                <th>Check-in Times</th>
	                <th>Check-in Status</th>
	                <th>Last Session Details</th>
	                <th>Last Session Time</th>
	                <th>Check-out Times</th>
	            </tr>
	            </thead>
	            <tbody>
	            <?foreach ($details_data as $data) { ?>
	                <tr>
	                    <td><? echo $data['staff']; ?></td>
	                    <td><? echo $data['first_lesson_org'] ?></td>
	                    <td><? echo $data['first_lesson_time'] ?></td>
	                    <td>
	                        <?php if ($data['not_checked_in'] == 0) {
	                            foreach ($data['check_in_times'] as $value){
	                                echo $value . "<br>";
	                            }
	                        }?>
	                    </td>
	                    <td class="center">
	                        <i style="color: #<? echo $data['check_in_status'] ?>; font-size: 25px;" class="far fa-map-marker-alt" aria-hidden="true"></i>
	                    </td>
	                    <td><? echo $data['last_lesson_ord'] ?></td>
	                    <td><? echo $data['last_lesson_time'] ?></td>
	                    <td>
	                        <? foreach ($data['check_out_times'] as $value){
	                            echo $value . "<br>";
	                        } ?>
	                    </td>
	                </tr>
	            <?} ?>
	            </tbody>
	        </table>
	    </div>
    </div>
<? } ?>
