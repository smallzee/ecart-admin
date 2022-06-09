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
    <h1>Units /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
    
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row"> 
        <!-- Left col -->
        <div class="col-xs-12">
            
        <?php if ($permissions['settings']['read'] == 1) { ?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Units</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="fund-transfers"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=unit"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>    
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="short_code" data-sortable="true">Short Code</th>
                            <th data-field="parent_id" data-sortable="true">Parent Id</th>
                            <th data-field="conversion" data-sortable="true">Conversion</th>     
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view settings.</div>
        <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
</section>



