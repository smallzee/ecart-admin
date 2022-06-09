<?php
include_once('includes/functions.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<section class="content-header">
    <h1>Withdrawal Requests /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <?php if ($permissions['return_requests']['read'] == 1) { ?>
                    <div class="box-header">
                        <div class="col-md-3">
                            <h4 class="box-title">Filter by type</h4>
                            <form method="post">
                                <select id="user_type" name="type" class="form-control col-xs-3" style="width: 300px;">
                                    <option value="">All</option>
                                    <option value='user'>Users</option>
                                    <option value='delivery_boy'>Delivery Boys</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="withdrawal-requests" data-url="api-firebase/get-bootstrap-table-data.php?table=withdrawal-requests" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="false" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams_1">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="type_id" data-sortable="true" data-visible="false">Type ID</th>
                                    <th data-field="type" data-sortable="true">Type</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="amount" data-sortable="true">Amount</th>
                                    <th data-field="balance" data-sortable="true">Balance</th>
                                    <th data-field="message" data-sortable="true">Message</th>
                                    <th data-field="status" data-sortable="true">Status</th>
                                    <th data-field="date_created" data-sortable="true">Date Created</th>
                                    <th data-field="last_updated" data-sortable="true" data-visible="false">Last Updated</th>
                                    <th data-field="operate" data-events="actionEvents">Action</th>

                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view withdrawal requests.</div>
        <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
    <div class="modal fade" id='editWithdrawalRequestModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Update Withdrawal Request</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="withdrawal_request_id" id="withdrawal_request_id" value='' />
                            <input type='hidden' name="type" id="type" value='' />
                            <input type='hidden' name="type_id" id="type_id" value='' />
                            <input type='hidden' name="amount" id="amount" value='' />
                            <input type='hidden' name="update_withdrawal_request" id="update_withdrawal_request" value='1' />

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                <div class="col-md-7 col-sm-6 col-xs-12">
                                    <div id="status" class="btn-group">
                                        <label class="btn btn-warning" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0"> Pending
                                        </label>
                                        <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Approved
                                        </label>
                                        <label class="btn btn-danger" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="2"> Cancelled
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="display:none;">
                                <label class="" for="">Message</label>
                                <textarea id="message" name="message" class="form-control col-md-7 col-xs-12" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
                            </div>
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
</section>

<script>
    $('#update_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#update_form").validate().form()) {
            if (confirm('Are you sure?')) {
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
                        $('#withdrawal-requests').bootstrapTable('refresh');
                        setTimeout(function() {
                            $('#editWithdrawalRequestModal').modal('hide');
                        }, 3000);
                    }
                });
            }
        }
    });
    window.actionEvents = {
        'click .edit-withdrawal-request': function(e, value, row, index) {
            if ($(row.status).text() == 'Pending')
                $("input[name=status][value=0]").prop('checked', true);
            if ($(row.status).text() == 'Approved')
                $("input[name=status][value=1]").prop('checked', true);
            if ($(row.status).text() == 'Cancelled')
                $("input[name=status][value=2]").prop('checked', true);
            $('#withdrawal_request_id').val(row.id);
            $('#type').val(row.type);
            $('#type_id').val(row.type_id);
            $('#amount').val(row.amount);
        }
    }
    $(document).on('click', '.delete-withdrawal-request', function() {
        if (confirm('Are you sure? Want to delete withdrawal request.')) {
            id = $(this).data("id");
            $.ajax({
                url: 'public/db-operation.php',
                type: "get",
                data: 'id=' + id + '&delete_withdrawal_request=1',
                success: function(result) {
                    if (result == 0) {
                        $('#withdrawal-requests').bootstrapTable('refresh');
                    }
                    if (result == 2) {
                        alert('You have no permission to delete withdrawal request');
                    }
                    if (result == 1) {
                        alert('Error! withdrawal request could not be deleted.');
                    }
                }
            });
        }
    });

    function queryParams_1(p) {
        return {
            "type": $('#user_type').val(),
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
    $('#user_type').on('change', function() {
        $('#withdrawal-requests').bootstrapTable('refresh');
    });
</script>