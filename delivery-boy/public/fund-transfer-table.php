<?php 

    include_once('../includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    
    include('../includes/variables.php');
    include_once('../includes/custom-functions.php');
    
    $fn = new custom_functions;
    $config = $fn->get_configurations();
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<section class="content-header">
    <h1>Fund Transfers /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
    
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Fund Transfers</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="fund-transfers"
                        data-url="get-bootstrap-table-data.php?table=fund-transfers"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="delivery_boy_id" data-sortable="true">D.Boy ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile</th>
                            <th data-field="address" data-sortable="true">Address</th>
                            <th data-field="opening_balance" data-sortable="true">Opening Balance</th>
                            <th data-field="closing_balance" data-sortable="true">Closing Balance</th>
                            <th data-field="amount" data-sortable="true">Amount</th>
                            <th data-field="type" data-sortable="true">Type</th>
                            <th data-field="message" data-sortable="true">Message</th>
                            
                            <th data-field="status" data-sortable="true">Status</th>
                             <th data-field="date_created" data-sortable="true">Date Created</th>
     
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>
</section>



