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
    <h1>Social Media /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['settings']['update'] == 0) { ?>
                <div class="alert alert-danger">You have no permission to add social media</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Social Media</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form method="post" id="add_form" action="public/db-operation.php">
                    <input type="hidden" id="add_social_media" name="add_social_media" required="" value="1" aria-required="true">
                     <div class="box-body">
                               
                        <div class="form-group">
                            <label for="exampleInputEmail1">Icon</label>
                            <select class="form-control fa" id="icon" name="icon">
                                <option value="fa-facebook">&#xf09a; Facebook</option>
                                <option value="fa-linkedin">&#xf0e1; LinkedIn</option>
                                <option value="fa-instagram">&#xf16d; Instagram</option>
                                <option value="fa-twitter">&#xf099; Twitter</option>
                                <option value="fa-whatsapp">&#xf232; Whatsapp</option>
                                <option value="fa-youtube">&#xf167; Youtube</option>
                                <option value="fa-qq">&#xf1d6; QQ</option>
                                <option value="fa-wechat">&#xf1d7; WeChat</option>
                                <option value="fa-tumblr">&#xf173; Tumblr</option>
                                <option value="fa-google-plus">&#xf1a0; Google+</option>
                                <option value="fa-skype">&#xf17e;  Skype</option>
                                <option value='fa-flickr'>&#xf16e; fa-flickr</option>
                                <option value="fa-pinterest">&#xf0d2; Pinterest</option>
                            
                                <option value="fa-reddit">&#xf1a1; Reddit</option>
                                <option value="fa-foursquare">&#xf180; Foursquare</option>
                                <option value="fa-renren">&#xf18b;  Renren</option>
                                <option value="fa-delicious">&#xf1a5; Delicious </option>
                         
                               
                            </select>
                        </div>
                        <div class="form-group ">
                            <label for="exampleInputEmail1">Link</label>
                            <input type="url" id="link" placeholder="link" class="form-control" name="link">
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
            <?php if ($permissions['settings']['update'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Social Media</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="social_media" data-url="api-firebase/get-bootstrap-table-data.php?table=social_media" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="asc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="social_icon" data-sortable="true">Icon</th>
                                    <th data-field="link" data-sortable="true">Link</th>
                                    <th data-field="operate" data-events="actionEvents">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view Social Media</div>
            <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
    <div class="modal fade" id='editSocialMediaModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Edit Social Media </h4>
                    
                </div>

                <div class="modal-body">
                    <?php if ($permissions['settings']['update'] == 0) { ?>
                        <div class="alert alert-danger">You have no permission to update social media</div>
                    <?php } ?>
                    <div class="box-body">
                        <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="social_media_id" id="social_media_id" value='' />
                            <input type='hidden' name="update_social_media" id="update_social_media" value='1' />

                            <div class="form-group">
                                <label class="" for="">Icon</label>
                                <select class="form-control fa" id="update_icon" name="update_icon">
                                    <option value="fa-facebook">&#xf09a; Facebook</option>
                                <option value="fa-linkedin">&#xf0e1; LinkedIn</option>
                                <option value="fa-instagram">&#xf16d; Instagram</option>
                                <option value="fa-twitter">&#xf099; Twitter</option>
                                <option value="fa-whatsapp">&#xf232; Whatsapp</option>
                                <option value="fa-youtube">&#xf167; Youtube</option>
                                <option value="fa-qq">&#xf1d6; QQ</option>
                                <option value="fa-wechat">&#xf1d7; WeChat</option>
                                <option value="fa-tumblr">&#xf173; Tumblr</option>
                                <option value="fa-google-plus">&#xf1a0; Google+</option>
                                <option value="fa-skype">&#xf17e;  Skype</option>
                                <option value='fa-flickr'>&#xf16e; fa-flickr</option>
                                <option value="fa-pinterest">&#xf0d2; Pinterest</option>
                          
                                <option value="fa-reddit">&#xf1a1; Reddit</option>
                                <option value="fa-foursquare">&#xf180; Foursquare</option>
                                <option value="fa-renren">&#xf18b;  Renren</option>
                                <option value="fa-delicious">&#xf1a5; Delicious </option>
                               
                                </select>
                            </div>
           
                            <div class="form-group">
                                <label class="" for="">Link</label>
                                <input type="text" id="update_link" name="update_link" class="form-control col-md-7 col-xs-12">
                            </div>
                            
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
                    <div class="row">
                        <div class="col-md-offset-3 col-md-8" style="display:none;" id="transfer_result"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    $('#add_form').validate({
        rules: {
            icon: "required",
            link: "required",
        }
    });
</script>
<script>
    $('#update_form').validate({
        rules: {
            update_icon: "required",
            update_link: "required",
        }
    });
</script>
<script>
    $('#add_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#add_form").validate().form()) {
            if (confirm('Are you sure?Want to Add Social Media')) {
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
                        $('#social_media').bootstrapTable('refresh');
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
                    $('#social_media').bootstrapTable('refresh');
                    setTimeout(function() {
                        $('#editSocialMediaModal').modal('hide');
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
        'click .edit-social-media': function(e, value, row, index) {
            $('#social_media_id').val(row.id);
            $('#update_icon').val(row.icon);
            $('#update_link').val(row.link);
           
        }
    }
</script>

<script>
    $(document).on('click', '.delete-social-media', function() {
        if (confirm('Are you sure? Want to delete social media.')) {

            id = $(this).data("id");

            // image = $(this).data("image");
            $.ajax({
                url: 'public/db-operation.php',
                type: "get",
                data: 'id=' + id + '&delete_social_media=1',
                success: function(result) {
                    $('#social_media').bootstrapTable('refresh');
                }
            });
        }
    });
</script>