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
  <h1>Bulk Upload /<small><a href="products.php"><i class="fa fa-cubes"></i> Products</a></small></h1>

</section>
<!-- Main content -->

<section class="content">
  <!-- Main row -->

  <div class="row">

    <div class="col-md-6">
      <?php
      if ($permissions['products']['create'] == 0) { ?>
        <div class="alert alert-danger">You have no permission to upload products</div>
      <?php } ?>

      <!-- general form elements -->
      <div class="alert alert-info">Read and follow instructions carefully before proceed.</div>
      <div class="box box-primary">
        <div class="box-header with-border">



        </div><!-- /.box-header -->
        <!-- form start -->
        <form method="post" id="add_form" action="public/db-operation.php" enctype="multipart/form-data">
          <input type="hidden" id="bulk_upload" name="bulk_upload" required="" value="1" aria-required="true">
          <div class="box-body">
            <div class="form-group">
              <label for="">Type</label>
              <select name="type" id="type" class="form-control">
                <option value="">Select</option>
                <option value="products">Products</option>
                <option value="variants">Variants</option>
              </select>
            </div>
            <div class="form-group">
              <label for="">CSV File</label>
              <input type="file" name="upload_file" class="form-control" accept=".csv" />
            </div>


          </div><!-- /.box-body -->

          <div class="box-footer">
            <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Upload</button>
            <input type="reset" class="btn-warning btn" value="Clear" />

            <a class='btn btn-info' id='sample' href='#' download> <em class='fa fa-download'></em> Download Sample File</a>
            <a class='btn btn-warning' id='instructions' href='#' download> <em class='fa fa-download'></em> Download Instructions</a>

          </div>
          <div class="form-group">

            <div id="result" style="display: none;"></div>
          </div>
          <input type="hidden" name="type1" id="type1" value="" />
        </form>
      </div><!-- /.box -->
    </div>
    <div class="separator"> </div>
  </div>
</section>
<script>
  $(document).ready(function() {
    $('#type').val('');
  });
  $('#type').on('change', function(e) {
    var type = $('#type').val();
    $("#type1").val(type);
  });
  $('.box-footer > #sample').click(function(e) {
    e.preventDefault(); //stop the browser from following
    //whenever you click off an input element
    type1 = $("#type1").val();
    if (type1 != 'products' && type1 != 'variants') {
      alert('Please select type.');
    }
    if (type1 == 'products') {
      window.location.href = 'library/products.csv';
    } else if (type1 == 'variants') {
      window.location.href = 'library/variants.csv';
    }

  });
  $('.box-footer > #instructions').click(function(e) {

    e.preventDefault(); //stop the browser from following
    //whenever you click off an input element
    type2 = $("#type1").val();
    if (type2 != 'products' && type2 != 'variants') {
      alert('Please select type.');
    }
    if (type2 == 'products') {
      window.location.href = 'library/products.txt';
    } else if (type2 == 'variants') {
      window.location.href = 'library/variants.txt';
    }

  });
</script>

<script>
  $('#add_form').validate({
    rules: {
      upload_file: "required",
      type: "required"
    }
  });
</script>
<script>
  $('#add_form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    if ($("#add_form").validate().form()) {
      if (confirm('Are you sure?Want to upload')) {
        $.ajax({
          type: 'POST',
          url: $(this).attr('action'),
          data: formData,
          beforeSend: function() {
            $('#submit_btn').html('Please wait..').attr('disabled', 'true');
          },
          cache: false,
          contentType: false,
          processData: false,
          success: function(result) {
            $('#result').html(result);
            $('#result').show().delay(6000).fadeOut();
            $('#submit_btn').html('Upload').removeAttr('disabled');
            $('#add_form')[0].reset();
          }
        });
      }
    }
  });
</script>