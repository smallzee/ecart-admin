<?php

include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');

$fn = new custom_functions;
$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<section class="content-header">
  <h1>Time Slots /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>

</section>
<!-- Main content -->
<section class="content">
  <!-- Main row -->
  <div class="row">
    <div class="col-md-6">
      <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Time Slot Config</h3>

        </div><!-- /.box-header -->
        <?php 
        if ($permissions['settings']['update'] == 0) { ?>
          <div class="alert alert-danger">You have no permission to update settings</div>
        <?php }  ?>
        <!-- form start -->
        <form method="post" id="config_form" action="public/db-operation.php">
          <input type="hidden" id="time_slot_config" name="time_slot_config" required="" value="1" aria-required="true">
          <div class="box-body">
            <div class="form-group">
              <label for="">Enable / Disable Time Slots</label><br>
              <input type="checkbox" id="config-button" class="js-switch" <?php if (!empty($time_slot_config) && isset($time_slot_config['is_time_slots_enabled']) && $time_slot_config['is_time_slots_enabled'] == 1) {
                                                                            echo 'checked';} ?>>
              <input type="hidden" id="is_time_slots_enabled" name="is_time_slots_enabled" value="<?= !empty($time_slot_config) && isset($time_slot_config['is_time_slots_enabled']) && $time_slot_config['is_time_slots_enabled'] == 1 ? 1 : 0; ?>">
            </div>
            <div class="form-group">
              <label for="">Delivery Starts From?</label>
              <select name="delivery_starts_from" class="form-control">
                <option value="">Select</option>
                <option value="1" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 1 ? 'selected' : '' ?>>Today</option>
                <option value="2" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 2 ? 'selected' : '' ?>>Tomorrow</option>
                <option value="3" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 3 ? 'selected' : '' ?>>Third Day</option>
                <option value="4" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 4 ? 'selected' : '' ?>>Fourth Day</option>
                <option value="5" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 5 ? 'selected' : '' ?>>Fifth Day</option>
                <option value="6" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 6 ? 'selected' : '' ?>>Sixth Day</option>
                <option value="7" <?= !empty($time_slot_config) && isset($time_slot_config['delivery_starts_from']) && $time_slot_config['delivery_starts_from'] == 7 ? 'selected' : '' ?>>Seventh Day</option>
              </select>
            </div>
            <div class="form-group">
              <label for="">How many Days you want to allow?</label>
              <select name="allowed_days" class="form-control">
                <option value="">Select</option>
                <option value="1" <?= $time_slot_config['allowed_days'] == 1 ? 'selected' : '' ?>>1</option>
                <option value="7" <?= $time_slot_config['allowed_days'] == 7 ? 'selected' : '' ?>>7</option>
                <option value="15" <?= $time_slot_config['allowed_days'] == 15 ? 'selected' : '' ?>>15</option>
                <option value="30" <?= $time_slot_config['allowed_days'] == 30 ? 'selected' : '' ?>>30</option>
              </select>
            </div>

          </div><!-- /.box-body -->

          <div class="box-footer">
            <button type="submit" class="btn btn-primary" id="btn" name="btn">Save</button>

          </div>
          <div class="form-group">

            <div id="config_result" style="display: none;"></div>
          </div>
        </form>
      </div><!-- /.box -->

      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Add Time Slot</h3>

        </div><!-- /.box-header -->
        <?php if ($permissions['settings']['update'] == 0) { ?>
          <div class="alert alert-danger">You have no permission to add time slot</div>
        <?php }  ?>
        <!-- form start -->
        <form method="post" id="add_form" action="public/db-operation.php">
          <input type="hidden" id="add_time_slot" name="add_time_slot" required="" value="1" aria-required="true">
          <div class="box-body">
            <div class="form-group">
              <label for="">Title</label>
              <input type="text" class="form-control" name="title" placeholder="Morning 9AM to 12PM">
            </div>
            <div class="form-group">
              <label for="">From Time <small>(24 hrs format)</small></label>
              <input type="time" class="form-control" name="from_time">
            </div>
            <div class="form-group">
              <label for="">To Time <small>(24 hrs format)</small></label>
              <input type="time" class="form-control" name="to_time">
            </div>
            <div class="form-group">
              <label for="">Last Order Time <small>(24 hrs format)</small></label>
              <input type="time" class="form-control" name="last_order_time">
            </div>
            <div class="form-group">
              <label for="">Status</label>
              <select name="status" class="form-control">
                <option value="">Select</option>
                <option value="1">Active</option>
                <option value="0">Deactive</option>
              </select>
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

    <div class="col-md-6">
      <?php if ($permissions['settings']['read'] == 1) { ?>
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Time Slots</h3>
          </div>
          <div class="box-body">
              <div class="table-responsive">
                <table class="table no-margin" data-toggle="table" id="time-slots" data-url="api-firebase/get-bootstrap-table-data.php?table=time-slots" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="last_order_time" data-sort-order="asc">
              <thead>
                <tr>
                  <th data-field="id" data-sortable="true">ID</th>
                  <th data-field="title" data-sortable="true">Title</th>
                  <th data-field="from_time" data-sortable="true">From Time</th>
                  <th data-field="to_time" data-sortable="true">To Time</th>
                  <th data-field="last_order_time" data-sortable="true">Last Order Time</th>
                  <th data-field="status">Status</th>
                  <th data-field="operate" data-events="actionEvents">Action</th>
                </tr>
              </thead>
            </table>  
              </div>
            
          </div>
        </div>
    </div>
  <?php } else { ?>
    <div class="alert alert-danger">You have no permission to view settings</div>
  <?php } ?>
  <div class="separator"> </div>
  </div>
  <div class="modal fade" id='editTimeSlotModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md" role="document">

      <div class="modal-content">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Edit Time Slot</h4>
        </div>


        <div class="modal-body">
          <?php if ($permissions['settings']['update'] == 0) { ?>
            <div class="alert alert-danger">You have no permission to update settings</div>
          <?php }  ?>
          <div class="box-body">
            <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
              <input type='hidden' name="time_slot_id" id="time_slot_id" value='' />
              <input type='hidden' name="update_time_slot" id="update_time_slot" value='1' />
              <!-- <input type='hidden' name="image_url" id="image_url" value=''/> -->
              <div class="form-group">
                <label for="">Title</label>
                <input type="text" class="form-control" name="update_title" id="update_title">
              </div>
              <div class="form-group">
                <label for="">From Time</label>
                <input type="time" class="form-control" name="update_from_time" id="update_from_time">
              </div>
              <div class="form-group">
                <label for="">To Time</label>
                <input type="time" class="form-control" name="update_to_time" id="update_to_time">
              </div>
              <div class="form-group">
                <label for="">Last Order Time</label>
                <input type="time" class="form-control" name="update_last_order_time" id="update_last_order_time">
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
</section>
<script>
  var changeCheckbox = document.querySelector('#config-button');
  var init = new Switchery(changeCheckbox);
  changeCheckbox.onchange = function() {
    if ($(this).is(':checked')) {
      $('#is_time_slots_enabled').val(1);
    } else {
      $('#is_time_slots_enabled').val(0);
    }
  };
</script>
<script>
  $('#add_form').validate({
    rules: {
      title: "required",
      from_time: "required",
      to_time: "required",
      last_order_time: "required",
      status: "required",
    }
  });
</script>
<script>
  $('#config_form').validate({
    rules: {
      allowed_days: "required",
      delivery_starts_from:"required",
    }
  });
</script>
<script>
  $('#update_form').validate({
    rules: {
      update_title: "required",
      update_from_time: "required",
      update_to_time: "required",
      update_last_order_time: "required",

    }
  });
</script>
<script>
  $('#add_form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    if ($("#add_form").validate().form()) {
      if (confirm('Are you sure?Want to Add Time Slot')) {
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
            $('#time-slots').bootstrapTable('refresh');
          }
        });
      }
    }
  });
</script>
<script>
  $('#config_form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    if ($("#config_form").validate().form()) {
      if (confirm('Are you sure?')) {
        $.ajax({
          type: 'POST',
          url: $(this).attr('action'),
          data: formData,
          beforeSend: function() {
            $('#btn').html('Please wait..');
          },
          cache: false,
          contentType: false,
          processData: false,
          success: function(result) {
            $('#config_result').html(result);
            $('#config_result').show().delay(6000).fadeOut();
            $('#btn').html('Save');
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
          $('#time-slots').bootstrapTable('refresh');
          setTimeout(function() {
            $('#editTimeSlotModal').modal('hide');
          }, 3000);
        }
      });
    }
  });
</script>
<script>
  window.actionEvents = {
    'click .edit-time-slot': function(e, value, row, index) {
      $("input[name=status][value=1]").prop('checked', true);
      if ($(row.status).text() == 'Deactive')
        $("input[name=status][value=0]").prop('checked', true);
      $('#time_slot_id').val(row.id);
      $('#update_title').val(row.title);
      $('#update_from_time').val(row.from_time);
      $('#update_to_time').val(row.to_time);
      $('#update_last_order_time').val(row.last_order_time);
    }
  }
</script>
<script>
  $(document).on('click', '.delete-time-slot', function() {
    if (confirm('Are you sure? Want to delete time slot.')) {
      id = $(this).data("id");
      $.ajax({
        url: 'public/db-operation.php',
        type: "get",
        data: 'id=' + id + '&delete_time_slot=1',
        success: function(result) {
          if (result == 0) {
            $('#time-slots').bootstrapTable('refresh');
          }
          if (result == 1) {
            alert('Error! Time slot could not be deleted.');
          }
          if (result == 2) {
            alert('You have no permission to delete time slot');
          }
        }
      });
    }
  });
</script>