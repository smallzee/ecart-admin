<?php
if ($permissions['orders']['read'] == 1) {
?>
    <section class="content-header">
        <h1>Order List</h1>
        <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        </ol>
        <hr />
    </section>
    <style>
        .uppercase {
            text-transform: uppercase;
        }

        .btn {
            padding: 9px 12px;
            line-height: 0.42857143;
        }
    </style>

    <!-- search form -->
    <section class="content">
        <!-- Main row -->

        <div class="row">
            <div class="col-md-12">

                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Latest Orders</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                        <form method="POST" id="filter_form" name="filter_form">
                            <div class="form-group">
                                <label for="from" class="control-label col-md-1 col-sm-3 col-xs-12">From & To Date</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="date" name="date" autocomplete="off" />
                                </div>
                                <input type="hidden" id="start_date" name="start_date">
                                <input type="hidden" id="end_date" name="end_date">
                            </div>
                            <div class="form-group">
                                <select id="filter_order" name="filter_order" placeholder="Select Status" required class="form-control" style="width: 300px;">
                                    <option value="">All Orders</option>
                                    <option value='awaiting_payment'>Awaiting Payment</option>
                                    <option value='received'>Received</option>
                                    <option value='processed'>Processed</option>
                                    <option value='shipped'>Shipped</option>
                                    <option value='delivered'>Delivered</option>
                                    <option value='cancelled'>Cancelled</option>
                                    <option value='returned'>Returned</option>
                                </select>

                            </div>
                            <input type="hidden" id="filter_order_status" name="filter_order_status">
                            <div class="form-group">
                            </div>
                        </form>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table no-margin" data-toggle="table" id="order_list" data-url="api-firebase/get-bootstrap-table-data.php?table=orders" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-columns="true" data-show-refresh="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams_1" data-show-footer="true" data-footer-style="footerStyle">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable='true'>O.ID</th>
                                        <th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
                                        <th data-field="qty" data-sortable='true' data-visible="false">Qty</th>
                                        <th data-field="name" data-sortable='true'>U.Name</th>
                                        <th data-field="mobile" data-sortable='true' data-visible="true" data-footer-formatter="totalFormatter">Mob.</th>
                                        <th data-field="order_note" data-sortable='false' data-visible="false">Order Note</th>
                                        <th data-field="items" data-sortable='true' data-visible="false">Items</th>
                                        <th data-field="total" data-sortable='true' data-visible="true" data-footer-formatter="priceFormatter">Total(<?= $settings['currency'] ?>)</th>
                                        <th data-field="delivery_charge" data-sortable='true' data-footer-formatter="delivery_chargeFormatter">D.Chrg</th>
                                        <th data-field="tax" data-sortable='false'>Tax <?= $settings['currency'] ?>(%)</th>
                                        <th data-field="discount" data-sortable='true' data-visible="true">Disc.<?= $settings['currency'] ?>(%)</th>
                                        <th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
                                        <th data-field="promo_discount" data-sortable='true' data-visible="true">Promo Disc.(<?= $settings['currency'] ?>)</th>
                                        <th data-field="wallet_balance" data-sortable='true' data-visible="true">Wallet Used(<?= $settings['currency'] ?>)</th>
                                        <th data-field="final_total" data-sortable='true' data-footer-formatter="final_totalFormatter">F.Total(<?= $settings['currency'] ?>)</th>
                                        <th data-field="deliver_by" data-sortable='true' data-visible='false'>Deliver By</th>
                                        <th data-field="payment_method" data-sortable='true' data-visible="true">P.Method</th>
                                        <th data-field="address" data-sortable='true' data-visible="false">Address</th>
                                        <th data-field="delivery_time" data-sortable='true' data-visible='true'>D.Time</th>
                                        <th data-field="status" data-sortable='true' data-visible='false'>Status</th>
                                        <th data-field="active_status" data-sortable='true' data-visible='true'>A.Status</th>
                                        <th data-field="date_added" data-sortable='true' data-visible="false">O.Date</th>
                                        <th data-field="operate">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } else { ?>
    <div class="alert alert-danger topmargin-sm leftmargin-sm">You have no permission to view orders.</div>
<?php } ?>
<script>
    $('#filter_order').on('change', function() {
        status = $('#filter_order').val();
        $('#filter_order_status').val(status);

    });
</script>
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
            $('#order_list').bootstrapTable('refresh');
        });
        $('#filter_order').on('change', function() {
            $('#order_list').bootstrapTable('refresh');
        });
    });

    function queryParams_1(p) {
        return {
            "start_date": $('#start_date').val(),
            "end_date": $('#end_date').val(),
            "filter_order": $('#filter_order_status').val(),
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }

    function totalFormatter() {
        return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>'
    }

    function orderFormatter(data) {
        return '<span style="color:green;font-weight:bold;font-size:large;">' + data.length + ' Order'
    }
    var total = 0;

    function priceFormatter(data) {
        var field = this.field
        return '<span style="color:green;font-weight:bold;font-size:large;"> <?= $settings['currency'] ?> ' + data.map(function(row) {
                return +row[field]
            })
            .reduce(function(sum, i) {
                return sum + i
            }, 0);
    }

    function delivery_chargeFormatter(data) {
        var field = this.field
        return '<span style="color:green;font-weight:bold;font-size:large;"><?= $settings['currency'] ?> ' + data.map(function(row) {
                return +row[field]
            })
            .reduce(function(sum, i) {
                return sum + i
            }, 0);
    }

    function final_totalFormatter(data) {
        var field = this.field
        return '<span style="color:green;font-weight:bold;font-size:large;"><?= $settings['currency'] ?> ' + data.map(function(row) {
                return +row[field]
            })
            .reduce(function(sum, i) {
                return sum + i
            }, 0);
    }
</script>
<?php
$db->disconnect();
?>