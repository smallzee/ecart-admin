<section class="content-header">
    <h1>eCart Purchase Code <a href='purchase-code.php'></h1> 
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Validate the authentic purchase of customer</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <div class="box-body">
                    <form id="validate_code_form" method="GET">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Enter the Purchase Code shared by the customer for eCart</label>
                            <input type="text" class="form-control" id="purchase_code" name="purchase_code" placeholder="Enter the purchase code here" required>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary" name="btnValidate" id="btnValidate">Validate Now</button>
                            <input type="reset" class="btn-warning btn" value="Clear" />
                            <div class="form-group">
                                <div><br><p id="result_success" class="alert alert-success"></p></div>
                                <div><br><p id="result_fail" class="alert alert-danger"></p></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div><!-- /.box -->
        </div>
    </div>
</section>

<div class="separator"> </div>
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script>
    var domain_url = '<?=DOMAIN_URL ?>';
</script>
<script src="dist/js/covert.js"></script>
<?php $db->disconnect(); ?>