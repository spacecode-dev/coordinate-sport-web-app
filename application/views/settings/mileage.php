<?php
display_messages();
echo form_open_multipart('settings/listing/' . $current_page . '/' . $subsection.'/'.$id, array('class' => 'settings'));
	
echo form_hidden(['id' => $id]);
?>
<div class="card card-custom">
	<div class="card-header">
		<div class="card-title">
			<h3 class="card-label">Mileage Rates</h3>
		</div>
	</div>
	<div class="card-body">
		<div class="multi-columns">
			<div class="form-group">
				<?php
				echo form_label("Name <em>*</em>", "name");
				$data = array();
				$data['class'] = 'form-control';
				$data['name'] = 'name';
				$data['value'] = $name;
				if($id != "new" && $name == 'Car')
					$data["readonly"] = true;
				echo form_input($data);
				?>
			</div>
			<div class="form-group">
				<label for="name">Rate <em>*</em></label>
				<div class="input-group">
					<input type="text" name="rate" value="<?php echo $rate ?>" id="rate" class="form-control">
					<div class="input-group-append"><span class="input-group-text"><?php echo currency_small_symbol() ?> Per Mile</span></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class='form-actions d-flex justify-content-between'>
	<button class='btn btn-primary btn-submit' type="submit">
		<i class='far fa-save'></i> Save
	</button>
	<a class='btn btn-default' href="<?php echo site_url("settings/listing/".$sections."/".$subsection) ?>">
		 Cancel
	</a>
</div>
<?php echo form_close();

