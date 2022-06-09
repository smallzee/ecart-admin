<?php

include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');

$fn = new custom_functions;
$config = $fn->get_configurations();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<section class="content-header">
    <h1>Fund Transfers /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>

</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-6">

            <?php if ($permissions['delivery_boys']['read'] == 1) { ?>
                <div class="box box-primary">
                    <!-- form start -->
                    <form id="transfer_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                        <input type='hidden' name="boy_id" id="boy_id" value='' />
                        <input type='hidden' name="delivery_boy_balance" id="delivery_boy_balance" value='' />
                        <input type='hidden' name="transfer_fund" id="transfer_fund" value='1' />
                        <div class="box-body">
                            <div class="form-group">

                                <div class="col-md-12 col-sm-6 col-xs-12">
                                    <label for="details">Details:</label>
                                    <textarea class="form-control" rows="3" id="details" readonly></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 col-sm-6 col-xs-12">
                                    <label>Transfer Amount</label>
                                    <input type="text" name="amount" id="amount" class="form-control" onkeyup="validate_amount(this.value);">
                                </div>
                                <div class="col-md-12 col-sm-6 col-xs-12">
                                    <label>Message</label>
                                    <textarea class="form-control" rows="3" id="details" name="message" id="message"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <button type="submit" id="submit_button" class="btn btn-success">Submit</button>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-8" style="display:none;" id="transfer_result"></div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view fund transfer of delivery boys</div>
            <?php } ?>
        </div>
        <div class="col-md-6">
            <?php if ($permissions['delivery_boys']['read'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Delivery Boys</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="delivery_boys" data-url="api-firebase/get-bootstrap-table-data.php?table=delivery-boys" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-click-to-select="true" data-mobile-responsive="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-maintain-selected="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                            "fileName": "users-list-<?= date('d-m-y') ?>",
                            "ignoreColumn": ["state"]   
                        }'>
                            <thead>
                                <tr>
                                    <th data-field="state" data-radio="true"></th>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="balance" data-sortable="true">Balance</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view delivery boys</div>
            <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
</section>
<section class="content">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">

            <?php if ($permissions['delivery_boys']['read'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Fund Transfers</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="fund_transfers" data-url="api-firebase/get-bootstrap-table-data.php?table=fund-transfers" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="delivery_boy_id" data-sortable="true">D.Boy ID</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="mobile" data-sortable="true">Mobile</th>
                                    <th data-field="address" data-sortable="true">Address</th>
                                    <th data-field="opening_balance" data-sortable="true">Opening Balance</th>
                                    <th data-field="closing_balance" data-sortable="true">Closing Balance</th>
                                    <th data-field="amount" data-sortable="true">Amount</th>
                                    <th data-field="type" data-sortable="true">Type</th>
                                    <th data-field="message" data-sortable="true">Message</th>
                                    <th data-field="status" data-sortable="true">Status</th>
                                    <th data-field="date_created" data-sortable="true">Date Created</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view fund transfer of delivery boys</div>
            <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
</section>


<script>
    var d_boy_balance = "";
    $('#transfer_form').validate({
        rules: {
            amount: "required"
        }
    });

    $('#transfer_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#transfer_form").validate().form()) {
            var details_val = $('#details').val();
            if (details_val == "") {
                alert("you have to select delivery boy to transfer the funds.");
                $('#amount').val('');
            } else {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    beforeSend: function() {
                        $('#submit_button').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {

                        $('#transfer_result').html(result);
                        $('#transfer_result').show().delay(3000).fadeOut();
                        $('#submit_button').html('Submit');
                        $('#transfer_form')[0].reset();
                        $('#delivery_boys, #fund_transfers').bootstrapTable('refresh');
                        // $('#fund_transfers').bootstrapTable('refresh');
                    }
                });
            }
        }
    });
    $('#delivery_boys').on('check.bs.table', function(e, row) {
        d_boy_balance = row.balance;
        $('#details').val("Id: " + row.id + " | Name:" + row.name + " | Mobile: " + row.mobile + " | Balance: " + row.balance);
        $('#boy_id').val(row.id);
        $('#delivery_boy_balance').val(row.balance);
    });

    function validate_amount() {
        var balance = d_boy_balance;
        var amount = $('#amount').val();
        var details_val = $('#details').val();
        if (details_val == "") {
            alert("you have to select delivery boy to transfer the funds.");
            $('#amount').val('');
        } else {
            if (parseInt(balance) > 0) {
                if (parseInt(amount) > parseInt(balance)) {
                    alert('You Can not enter amount greater than balance.');
                    $('#amount').val('');

                }
            } else {
                alert('Balance must be greater than zero.');
                $('#amount').val('');
            }
            if (parseInt(amount) <= 0) {
                alert('Amount must be greater than zero.');
                $('#amount').val('');
            }
        }

    }
</script>