<div class='card card-custom'>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class="fas fa-laptop"></i></span>
			<h3 class="card-label">Customer Types</h3>
		</div>
		<div class="card-toolbar">
			<a href="<?php echo site_url('settings/customers/type/new'); ?>" class="btn btn-sm btn-success" title="Create New" data-original-title="Toggle Card">
				<i class="far fa-plus"></i> Create New
			</a>
		</div>
	</div>
</div>
<?php if($customers_org_type->num_rows() > 0){ ?>
	<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
				<tr>
					<th>
						Organisation Type
					</th>
					<th>
						Active
					</th>
					<th>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($customers_org_type->result() as $org){?>
					<tr>
						<td><?php echo $org->name; ?></td>
						<td class="center ajax_toggle">
							<?php
							if($org->active == 1) {
								?><a class='btn btn-success btn-sm' href="<?php echo site_url('settings/customers/type/active/' . $org->org_typeID); ?>/0" title="Yes">
									<i class='far fa-check'></i>
								</a><?php
							} else {
								?><a class='btn btn-danger btn-sm' href="<?php echo site_url('settings/customers/type/active/' . $org->org_typeID); ?>/1" title="No">
									<i class='far fa-times'></i>
								</a><?php
							}
							?>
						</td>
						<td class="width-1p nowrap">
							<div class='text-right'>
								<a class='btn btn-warning btn-sm' href='<?php echo site_url('settings/customers/type/edit/' . $org->org_typeID); ?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
								<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('settings/customers/type/remove/' . $org->org_typeID); ?>' title="Delete">
									<i class='far fa-trash'></i>
								</a>
							</div>
						</td>
				<?php }?>
				</tbody>
			</table>
		</div>
<?php } ?>

