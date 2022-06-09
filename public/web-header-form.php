<section class="content-header">
    <h1>Web Front-End Settings</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">

    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <?php if ($permissions['settings']['read'] == 1) {
                if ($permissions['settings']['update'] == 0) { ?>
                    <div class="alert alert-danger">You have no permission to update settings</div>
                <?php } ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update Url Settings</h3>
                    </div>
                    <!-- /.box-header -->
                    <?php
                    $db->sql("SET NAMES 'utf8'");
                    $sql = "SELECT * FROM settings WHERE  variable='front_end_settings'";
                    $db->sql($sql);

                    $res_time = $db->getResult();
                    if (!empty($res_time)) {
                        foreach ($res_time as $row) {
                            $data = json_decode($row['value'], true);
                        }
                    }
                    ?>
                    <!-- form start -->
                    <form id="system_configurations_form" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="front_end_settings" name="front_end_settings" required="" value="1" aria-required="true">
                        <div class="box-body">
                            
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Android Application's URL: </label>
                                    <input type="text" class="form-control" name="android_app_url" value="<?= isset($data['android_app_url']) ? $data['android_app_url'] : '' ?>" placeholder='Android App URL' />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Front Website Url: </label> 
                                    <input type="text" class="form-control" name="call_back_url" value="<?= isset($data['call_back_url']) ? $data['call_back_url'] : '' ?>" placeholder='Front Website Url' />
                                </div>
                                
                            </div>
                             <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="">Common Meta Keywords: </label> 
                                    <textarea id="common_meta_keywords" class="form-control" name="common_meta_keywords" placeholder="Common Meta Keywords" rows="4" cols="30"><?= isset($data['common_meta_keywords']) ? $data['common_meta_keywords'] : '' ?></textarea>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="">Common Meta Description: </label>
                                    <textarea id="common_meta_description" class="form-control" name="common_meta_description" placeholder="Common Meta Description" rows="4" cols="30"><?= isset($data['common_meta_description']) ? $data['common_meta_description'] : '' ?></textarea>
                                </div>
                        
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="app_name">Favicon Icon: </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="<?= isset($data['favicon']) ? DOMAIN_URL . 'dist/img/' . $data['favicon'] : ''; ?>" style="max-width: 100%;" /><br>
                                        </div>
                                    </div>
                                    <br> <input type='file' name='favicon' id='favicon' accept="image/*" />
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label for="app_name">Web Logo: </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="<?= isset($data['web_logo']) ? DOMAIN_URL . 'dist/img/' . $data['web_logo'] : ''; ?>" style="max-width: 100%;" /><br>
                                        </div>
                                    </div>
                                    <br> <input type='file' name='web_logo' id='web_logo' accept="image/*" />
                                </div>
                                
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div id="result"></div>
                        <div class="box-footer">
                            <input type="submit" id="settings_btn_update" class="btn-primary btn" value="Update" name="settings_btn_update" />
                            <!-- <input type="submit" class="btn-danger btn" value="Cancel" name="btn_cancel"/> -->
                        </div>
                    </form>
                <?php } else { ?>
                    <div class="alert alert-danger">You have no permission to view settings</div>
                <?php } ?>
                </div>
                <!-- /.box -->
        </div>

    </div>
</section>
<div class="separator"> </div>

<script>
    $('#system_configurations_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: 'public/db-operation.php',
            data: formData,
            beforeSend: function() {
                $('#settings_btn_update').html('Please wait..');
            },
            cache: false,
            contentType: false,
            processData: false,
            success: function(result) {
                $('#result').html(result);
                $('#result').show().delay(5000).fadeOut();
                $('#settings_btn_update').html('Save Settings');
            }
        });
    });
</script>