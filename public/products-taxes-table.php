<?php
include_once('includes/functions.php');
?>
<section class="content-header">
    <h1>Taxes /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-tax.php"><i class="fa fa-plus-square"></i> Add New Taxes</a>
    </ol>
</section>
<?php
if ($permissions['products']['read'] == 1) {
?>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <form method="POST" id="filter_form" name="filter_form">

                            <div class="form-group col-md-3">
                            </div>
                        </form>
                    </div>
                    <div class="box-header">
                        <h3 class="box-title">Taxes</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="tax_list" data-url="api-firebase/get-bootstrap-table-data.php?table=taxes" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams_1">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="title" data-sortable="true">Title</th>
                                    <th data-field="percentage" data-sortable="true">Percentage</th>
                                    <th data-field="status">Status</th>
                                    <th data-field="operate">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="separator"> </div>
        </div>
    </section>
    <script>
        function queryParams_1(p) {
            return {
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }
    </script>
<?php } else { ?>
    <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view taxes.</div>
<?php } ?>