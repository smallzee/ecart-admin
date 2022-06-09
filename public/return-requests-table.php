<?php
include_once('includes/functions.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<section class="content-header">
    <h1>Return Requests /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
 
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <?php if ($permissions['return_requests']['read'] == 1) { ?>
                    <div class="box-header">
                        <h3 class="box-title">Return Requests</h3>
                    </div>

                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="return-requests" data-url="api-firebase/get-bootstrap-table-data.php?table=return-requests" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="user_id" data-sortable="true">U.ID</th>
                                    <th data-field="order_id" data-sortable="true" data-visible="false">O.ID</th>
                                    <th data-field="order_item_id" data-sortable="true" data-visible="false">Item ID</th>
                                    <th data-field="product_id" data-sortable="true" data-visible="false">Product ID</th>
                                    <th data-field="product_variant_id" data-sortable="true" data-visible="false">Product Variant ID</th>
                                    <th data-field="name" data-sortable="true">U.Name</th>
                                    <th data-field="product_name" data-sortable="true">Product Name</th>
                                    <th data-field="price" data-sortable="true">Price</th>
                                    <th data-field="discounted_price" data-sortable="true">Discounted Price</th>
                                    <th data-field="quantity" data-sortable="true">Quantity</th>
                                    <th data-field="total" data-sortable="true">Total</th>
                                    <th data-field="status">Status</th>
                                    <th data-field="date_created" data-sortable="true">Date</th>
                                    <th data-field="operate" data-events="actionEvents">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

            </div>
        <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view return requests.</div>
        <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
    <div class="modal fade" id='editReturnRequestModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Update Return Request</h4>
                </div>

                <div class="modal-body">
                    <div class="box-body">
                        <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="return_request_id" id="return_request_id" value='' />
                            <input type='hidden' name="order_item_id" id="order_item_id" value='' />
                            <input type='hidden' name="order_id" id="order_id" value='' />
                            <input type='hidden' name="update_return_request" id="update_return_request" value='1' />

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
                            <div class="form-group">
                                <label class="" for="">Remark</label>
                                <textarea id="update_remarks" name="update_remarks" class="form-control col-md-7 col-xs-12" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
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
</section>

<script>
    $('#update_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#update_form").validate().form()) {
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
                    $('#return-requests').bootstrapTable('refresh');
                    setTimeout(function() {
                        $('#editReturnRequestModal').modal('hide');
                    }, 3000);
                }
            });
        }
    });
</script>
<script>
    window.actionEvents = {
        'click .edit-return-request': function(e, value, row, index) {
            if ($(row.status).text() == 'Pending')
                $("input[name=status][value=0]").prop('checked', true);
            if ($(row.status).text() == 'Approved')
                $("input[name=status][value=1]").prop('checked', true);
            if ($(row.status).text() == 'Cancelled')
                $("input[name=status][value=2]").prop('checked', true);
            $('#return_request_id').val(row.id);
            $('#order_item_id').val(row.order_item_id);
            $('#order_id').val(row.order_id);
            $('#update_remarks').val(row.remarks);
        }
    }
</script>
<script>
    $(document).on('click', '.delete-return-request', function() {
        if (confirm('Are you sure? Want to delete return request.')) {
            id = $(this).data("id");
            $.ajax({
                url: 'public/db-operation.php',
                type: "get",
                data: 'id=' + id + '&delete_return_request=1',
                success: function(result) {
                    if (result == 0) {
                        $('#return-requests').bootstrapTable('refresh');
                    }
                    if (result == 2) {
                        alert('You have no permission to delete return request');
                    }
                    if (result == 1) {
                        alert('Error! Return request could not be deleted.');
                    }
                }
            });
        }
    });
</script>