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
    <h1>Delivery Boys /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['delivery_boys']['create'] == 0) { ?>
                <div class="alert alert-danger">You have no permission to create delivery boy</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Delivery Boy</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form method="post" id="add_form" action="public/db-operation.php">
                    <input type="hidden" id="add_delivery_boy" name="add_delivery_boy" required="" value="1" aria-required="true">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" class="form-control" name="name">
                        </div>
                        <div class="form-group">
                            <label for="">Mobile</label>
                            <input type="number" class="form-control" name="mobile">
                        </div>
                        <div class="form-group">
                            <label for="">Password</label>
                            <input type="password" class="form-control" name="password" id="password">
                        </div>
                        <div class="form-group">
                            <label for="">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                        <label for="">Address</label>
                        <div class="form-group">

                            <textarea name="address" id="address"class="form-control" ></textarea>
                        </div>
                        <div class="form-group">
                            <label for="">Bonus (%)</label>
                            <input type="number" class="form-control" name="bonus" id="bonus" value="<?= $config['delivery-boy-bonus-percentage'] ?>"><br>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputFile">Driving License</label>
                            <input type="file" name="driving_license" id="driving_license" required /><br>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputFile">National Identity Card</label>
                            <input type="file" name="national_identity_card" id="national_identity_card" required /><br>
                        </div>
                        <div class="form-group">
                            <label for="">Date Of Birth</label>
                            <input type="date" class="form-control" name="dob" id="dob" required>
                        </div>
                        <div class="form-group">
                            <label for="">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" required>
                        </div>
                        <div class="form-group">
                            <label for="">Account Number</label>
                            <input type="number" class="form-control" name="account_number" required>
                        </div>
                        <div class="form-group">
                            <label for="">Bank Account Name</label>
                            <input type="text" class="form-control" name="account_name" required>
                        </div>
                        <div class="form-group">
                            <label for="">Bank's IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" required>
                        </div>
                        <div class="form-group">
                            <label for="">Other Payment Information</label>
                            <textarea name="other_payment_info" id="other_payment_info" rows='3' class="form-control"></textarea>
                        </div>


                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                        <input type="reset" class="btn-warning btn" value="Clear" />

                    </div>
                    <div class="form-group">

                        <div id="result" style="display: none;"></div>
                    </div>
                </form>
            </div><!-- /.box -->
        </div>
        <!-- Left col -->
        <div class="col-md-12">
            <?php if ($permissions['delivery_boys']['read'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Delivery Boys</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="delivery-boys" data-url="api-firebase/get-bootstrap-table-data.php?table=delivery-boys" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="mobile" data-sortable="true">Mobile</th>
                                    <th data-field="address" data-sortable="true">Address</th>
                                    <th data-field="bonus" data-sortable="true">Bonus(%)</th>
                                    <th data-field="balance" data-sortable="true">Balance</th>
                                    <th data-field="driving_license" data-sortable="true" data-visible="false">Driving License</th>
                                    <th data-field="national_identity_card" data-sortable="true" data-visible="false">National Identity Card</th>
                                    <th data-field="dob" data-sortable="true" data-visible="false">Date of Birth</th>
                                    <th data-field="bank_account_number" data-sortable="true" data-visible="false">Bank Account Number</th>
                                    <th data-field="bank_name" data-sortable="true" data-visible="false">Bank Name</th>
                                    <th data-field="account_name" data-sortable="true" data-visible="false">Account Name</th>
                                    <th data-field="ifsc_code" data-sortable="true" data-visible="false">IFSC Code</th>
                                    <th data-field="other_payment_information" data-sortable="true">Other Payment Information</th>
                                    <th data-field="status">Status</th>
                                    <th data-field="operate" data-events="actionEvents">Action</th>
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
    <div class="modal fade" id='editDeliveryBoyModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Edit Delivery Boy</h4>
                </div>

                <div class="modal-body">
                    <?php if ($permissions['delivery_boys']['update'] == 0) { ?>
                        <div class="alert alert-danger">You have no permission to update delivery boy</div>
                    <?php } ?>
                    <div class="box-body">
                        <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="delivery_boy_id" id="delivery_boy_id" value='' />
                            <input type='hidden' name="update_delivery_boy" id="update_delivery_boy" value='1' />
                            <input type='hidden' name="dr_image1" id="dr_image" value='' />
                            <input type='hidden' name="nic_image" id="nic_image" value='' />

                            <div class="form-group">
                                <label class="" for="">Name</label>
                                <input type="text" id="update_name" name="update_name" class="form-control col-md-7 col-xs-12">
                            </div>
                            <div class="form-group">
                                <label class="" for="">Mobile</label>
                                <input type="text" id="update_mobile" name="update_mobile" class="form-control col-md-7 col-xs-12" readonly>
                            </div>
                            <div class="form-group">
                                <label class="" for="">Password</label><small>( Leave it blank for no change )</small>
                                <input type="password" id="update_password" name="update_password" class="form-control col-md-7 col-xs-12">
                            </div>
                            <div class="form-group">
                                <label class="" for="">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control col-md-7 col-xs-12">
                            </div>
                            <div class="form-group">
                                <label class="" for="">Address</label>
                                <textarea name="update_address" id="update_address" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="">Bonus (%)</label>
                                <input type="number" class="form-control" name="update_bonus" id="update_bonus">
                            </div>
                            <div class="row">
                                <a data-lightbox='product' id="dr_container" href=''><img id="dr_img" src='' height='50' /></a><br>
                                <p id="no_dr_img"></p>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile">Driving License</label>
                                <input type="file" name="update_driving_license" id="update_driving_license" /><br>
                            </div>
                            <div class="row">
                                <a data-lightbox='product' id="nic_container" href=''><img id="nic_img" src='' height='50' /></a><br>
                                <p id="no_nic_img"></p>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile">National Identity Card</label>
                                <input type="file" name="update_national_identity_card" id="update_national_identity_card" /><br>
                            </div>
                            <div class="form-group">
                                <label for="">Date Of Birth</label>
                                <input type="date" class="form-control" name="update_dob" id="update_dob" required>
                            </div>
                            <div class="form-group">
                                <label for="">Bank Name</label>
                                <input type="text" class="form-control" name="update_bank_name" id="update_bank_name" required>
                            </div>
                            <div class="form-group">
                                <label for="">Account Number</label>
                                <input type="text" class="form-control" name="update_account_number" id="update_account_number" required>
                            </div>
                            <div class="form-group">
                                <label for="">Bank Account Name</label>
                                <input type="text" class="form-control" name="update_account_name" id="update_account_name" required>
                            </div>
                            <div class="form-group">
                                <label for="">Bank's IFSC Code</label>
                                <input type="text" class="form-control" name="update_ifsc_code" id="update_ifsc_code" required>
                            </div>
                            <div class="form-group">
                                <label for="">Other Payment Information</label>
                                <textarea name="update_other_payment_info" id="update_other_payment_info" rows='3' class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div id="status" class="btn-group">
                                        <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0"> Deactive
                                        </label>
                                        <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="id" name="id">
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                </div>
                            </div>
                            <div class="form-group">

                                <div class="row">
                                    <div class="col-md-offset-3 col-md-8" style="display:none;" id="update_result"></div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id='fundTransferModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Transfer Fund</h4>
                </div>
                <div class="modal-body">
                    <?php if ($permissions['delivery_boys']['update'] == 0) { ?>
                        <div class="alert alert-danger">You have no permission to update delivery boy</div>
                    <?php } ?>
                    <form id="transfer_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                        <input type='hidden' name="boy_id" id="boy_id" value='' />
                        <input type='hidden' name="transfer_fund" id="transfer_fund" value='1' />
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Name</label><input type="text" name="delivery_boy_name" id="delivery_boy_name" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Mobile</label><input type="text" name="delivery_boy_mobile" id="delivery_boy_mobile" class="form-control" readonly>
                            </div>


                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Balance</label><input type="text" name="delivery_boy_balance" id="delivery_boy_balance" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <label>Transfer Amount</label><input type="text" name="amount" id="amount" class="form-control" onkeyup="validate_amount(this.value);">
                            </div>
                            <div class="col-md-12 col-sm-6 col-xs-12">
                                <label>Message</label><input type="text" name="message" id="message" class="form-control">
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
            </div>
        </div>
    </div>
</section>
<script>
    $('#transfer_form').validate({
        rules: {
            amount: "required",
        }
    });
</script>
<script>
    $('#transfer_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#transfer_form").validate().form()) {
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
                    $('#amount').val('');
                    $('#delivery-boys').bootstrapTable('refresh');
                    setTimeout(function() {
                        $('#fundTransferModal').modal('hide');
                    }, 3000);
                }
            });
        }
    });
</script>
<script>
    $(document).on('click', '.transfer-fund', function() {
        id = $(this).data("id");
        name = $(this).data("name");
        mobile = $(this).data("mobile");
        address = $(this).data("address");
        balance = $(this).data("balance");

        $('#boy_id').val(id);
        $('#delivery_boy_name').val(name);
        // alert(row.city_id);
        $('#delivery_boy_mobile').val(mobile);
        $('#delivery_boy_address').val(address);
        $('#delivery_boy_balance').val(balance);

    });
</script>
<script>
    function validate_amount() {
        var balance = $('#delivery_boy_balance').val();
        var amount = $('#amount').val();
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
</script>
<script>
    $('#add_form').validate({
        rules: {
            name: "required",
            mobile: "required",
            password: "required",
            address: "required",
            confirm_password: {
                required: true,
                equalTo: "#password"
            }
        }
    });
</script>
<script>
    $('#update_form').validate({
        rules: {
            update_name: "required",
            update_mobile: "required",
            update_address: "required",
            confirm_password: {
                equalTo: "#update_password"
            }
        }
    });
</script>
<script>
    $('#add_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#add_form").validate().form()) {
            if (confirm('Are you sure?Want to Add Delivery Boy')) {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    beforeSend: function() {
                        $('#submit_btn').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        $('#result').html(result);
                        $('#result').show().delay(6000).fadeOut();
                        $('#submit_btn').html('Submit');
                        $('#add_form')[0].reset();
                        $('#delivery-boys').bootstrapTable('refresh');
                        // $('#area_tp_form').find(':input').each(function(){
                        //      $('#area_tp').val('');
                        // });
                        // $('#area_tp_list').bootstrapTable('refresh');
                    }
                });
            }
        }
    });
</script>
<script>
    $('#update_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#update_form").validate().form()) {
            //if(confirm('Are you sure?Want to Update Delivery Boy')){
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                beforeSend: function() {
                    $('#update_btn').html('Please wait..');
                },
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#update_result').html(result);
                    $('#update_result').show().delay(6000).fadeOut();
                    $('#update_btn').html('Update');
                    $('#update_form')[0].reset();
                    $('#delivery-boys').bootstrapTable('refresh');
                    setTimeout(function() {
                        $('#editDeliveryBoyModal').modal('hide');
                    }, 3000);
                    // $('#area_tp_form').find(':input').each(function(){
                    //      $('#area_tp').val('');
                    // });
                    // $('#area_tp_list').bootstrapTable('refresh');
                }
            });
            //}
        }
    });
</script>
<script>
    window.actionEvents = {
        'click .edit-delivery-boy': function(e, value, row, index) {
            $('#dr_image').val('');
            $('#dr_img').attr("src", '');
            $('#dr_container').attr("href", '');
            $('#nic_image').val('');
            $('#nic_img').attr("src", '');
            $('#nic_container').attr("href", '');
            $('#no_dr_img, #no_nic_img').text("");
            $('#update_other_payment_info').val('');
            // alert('You click remove icon, row: ' + JSON.stringify(row));
            var path1 = 'upload/delivery-boy/';
            var path = 'upload/delivery-boy/';
            var driving_license = $(this).data('driving_license');
            var national_identity_card = $(this).data('national_identity_card');
            if (driving_license != '') {
                path += driving_license;
                $('#dr_image').val(driving_license);
                $('#dr_img').attr("src", path);
                $('#dr_container').attr("href", path);
                driving_license = "";
            } else if (driving_license == "") {
                $('#no_dr_img').text("No Driving License");
            }
            if (national_identity_card != '') {
                path1 += national_identity_card;
                $('#nic_image').val(national_identity_card);
                $('#nic_img').attr("src", path1);
                $('#nic_container').attr("href", path1);
                national_identity_card = "";
            } else if (national_identity_card == "") {
                $('#no_nic_img').text("No National Identity Card");
            }
            $("input[name=status][value=1]").prop('checked', true);
            if ($(row.status).text() == 'Deactive')
                $("input[name=status][value=0]").prop('checked', true);
            $('#delivery_boy_id').val(row.id);
            $('#update_name').val(row.name);
            // alert(row.city_id);
            $('#update_mobile').val(row.mobile);
            $('#update_address').val(row.address);
            $('#update_bonus').val(row.bonus);
            $('#update_dob').val(row.dob);
            $('#update_bank_name').val(row.bank_name);
            $('#update_account_number').val(row.bank_account_number);
            $('#update_account_name').val(row.account_name);
            $('#update_ifsc_code').val(row.ifsc_code);
            if (row.other_payment_information != "") {
                $('#update_other_payment_info').val(row.other_payment_information);
            }
        }
    }
</script>
<script>
    $(document).on('click', '.delete-delivery-boy', function() {
        if (confirm('Are you sure? Want to delete delivery boy. All related data will also deleted.')) {

            id = $(this).data("id");
            var driving_license1 = $(this).data('driving_license');
            var national_identity_card1 = $(this).data('national_identity_card');

            // image = $(this).data("image");
            $.ajax({
                url: 'public/db-operation.php',
                type: "get",
                data: 'id=' + id + '&delete_delivery_boy=1&driving_license=' + driving_license1 + '&national_identity_card=' + national_identity_card1,
                success: function(result) {
                    if (result == 0) {
                        $('#delivery-boys').bootstrapTable('refresh');
                    }
                    if (result == 2) {
                        alert('You have no permission to delete delivery boy');
                    }
                    if (result == 1) {
                        alert('Error! Delivery boy could not be deleted.');
                    }
                    if (result == 3) {
                        alert('You can not delete this delivery boy.');
                    }

                }
            });
        }
    });
</script>