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
    <h1>Web Category /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        
        <!-- Left col -->
        <div class="col-md-12">
            <?php if ($permissions['categories']['read'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Category</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="web-category" data-url="api-firebase/get-bootstrap-web-category-table-data.php?table=category" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="subtitle" data-sortable="true">Subtitle</th>
                                    <th data-field="web_image" data-sortable="true">Image</th>
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
    <div class="modal fade" id='editWebCategoryModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Edit Category</h4>
                </div>

                <div class="modal-body">
                    <?php if ($permissions['categories']['update'] == 0) { ?>
                        <div class="alert alert-danger">You have no permission to update delivery boy</div>
                    <?php } ?>
                    <div class="box-body">
                        <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="web_category_id" id="web_category_id" value='' />
                            <input type='hidden' name="update_web_category" id="update_web_category" value='1' />
                            <input type='hidden' name="ci_image1" id="ci_image" value='' />
                           

                            <div class="row">
                                <img id="ci_img" src='' height="50" />
                                <p id="no_ci_img"></p>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile">Image</label>
                                <input type="file" name="c_image" id="c_image" /><br>
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
    $('#update_form').validate({
        rules: {
            web_category_image: "required",
            
        }
    });
</script>

<script>
    $('#update_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#update_form").validate().form()) {
            //if(confirm('Are you sure?Want to Update Category Image')){
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
                    $('#web-category').bootstrapTable('refresh');
                    setTimeout(function() {
                        $('#editWebCategoryModal').modal('hide');
                    }, 4000);
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
        'click .edit-web-category': function(e, value, row, index) {
            
            $('#web_category_id').val(row.id);
            $('#ci_image').val(row.web_image_db);
           $('#ci_img').attr("src", row.web_image_db);
            
    }
    }
</script>
