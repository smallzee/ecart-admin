<section class="content-header">
	<h1>Invoice reports</h1>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
	<hr />
</section>

<!-- search form -->
<section class="content">
	<!-- Main row -->
	<div class="row">
		<div class="col-md-12">
			<?php if ($permissions['reports']['read'] == 1) { ?>
				<div class="box box-info">
					<div class="box-header with-border">
						<form method="POST" id="filter_form" name="filter_form">
							<div class="form-group">
								<label for="from" class="control-label col-md-1 col-sm-3 col-xs-12">From & To Date</label>
								<div class="col-md-6">
									<input type="text" class="form-control" id="date" name="date" autocomplete="off" />
								</div>
								<input type="hidden" id="start_date" name="start_date">
								<input type="hidden" id="end_date" name="end_date">
							</div>
						</form>
					</div>

					<div class="box-body">
						<div class="table-responsive">

							<table class="table no-margin" data-toggle="table" id="reports_list" data-url="api-firebase/get-bootstrap-table-data.php?table=invoice_reports" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams">
								<thead>
									<tr>
										<th data-field="id" data-sortable='true'>I.No.</th>
										<th data-field="order_id" data-sortable='true' data-visible='false'>Order ID</th>
										<th data-field="name" data-sortable='true'>Name</th>
										<th data-field="address" data-sortable='true' data-visible='false'>Address</th>
										<th data-field="phone_number" data-sortable='true'>Phone</th>
										<th data-field="items" data-sortable='true' data-visible='false'>Item List</th>
										<th data-field="email" data-sortable='true'>Email</th>

										<th data-field="total_sale" data-sortable='true' data-visible="true">Total(<?= $settings['currency'] ?>)</th>
										<th data-field="shipping_charge" data-sortable='true'>D.Charge</th>
										<th data-field="tax" data-sortable='false'>Tax <?= $settings['currency'] ?>(%)</th>
										<th data-field="discount" data-sortable='true' data-visible="true">Disc.<?= $settings['currency'] ?>(%)</th>
										<th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
										<th data-field="promo_discount" data-sortable='true' data-visible="true">Promo Disc.(<?= $settings['currency'] ?>)</th>
										<th data-field="wallet_balance" data-sortable='true' data-visible="true">Wallet Used(<?= $settings['currency'] ?>)</th>
										<th data-field="payment" data-sortable='true'>F.Total(<?= $settings['currency'] ?>)</th>

										<th data-field="order_date" data-sortable='true' data-visible='false'>Order Date</th>
										<th data-field="invoice_date" data-sortable='true' data-visible='true'>Invoice Date</th>
										<th data-field="action" data-sortable='true'>Action</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<div class="alert alert-danger">You have no permission to view sales reports.</div>
			<?php } ?>
		</div>
	</div>
</section>
<!-- /.content -->
<script>
	$(document).ready(function() {
		$('#date').daterangepicker({
			"autoApply": true,
			"showDropdowns": true,
			"alwaysShowCalendars": true,
			"startDate": moment(),
			"endDate": moment(),
			"locale": {
				"format": "DD/MM/YYYY",
				"separator": " - "
			},
		});

		$('#date').on('apply.daterangepicker', function(ev, picker) {
			var drp = $('#date').data('daterangepicker');
			$('#start_date').val(drp.startDate.format('YYYY-MM-DD'));
			$('#end_date').val(drp.endDate.format('YYYY-MM-DD'));
		});
		$('#date').on('apply.daterangepicker', function(ev, picker) {
			var drp = $('#date').data('daterangepicker');
			$('#start_date').val(drp.startDate.format('YYYY-MM-DD'));
			$('#end_date').val(drp.endDate.format('YYYY-MM-DD'));
			$('#reports_list').bootstrapTable('refresh');
		});

	});

	function queryParams(p) {
		return {
			"start_date": $('#start_date').val(),
			"end_date": $('#end_date').val(),
			limit: p.limit,
			sort: p.sort,
			order: p.order,
			offset: p.offset,
			search: p.search
		};
	}
</script>
<?php
$db->disconnect();
?>