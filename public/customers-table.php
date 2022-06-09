<section class="content-header">
    <h1>Customers List</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<style>
    .btn {
        padding: 9px 12px;
        line-height: 0.42857143;
    }
</style>
<!-- search form -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <?php if ($permissions['customers']['read'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Customers</h3>
                    </div>
                    <?php $db->sql("SET NAMES 'utf8'");
                    $sql = "SELECT * FROM city ORDER BY id + 0 ASC";
                    $db->sql($sql);
                    $cities = $db->getResult();
                    ?>

                    <div class="box-body table-responsive">
                        <div class="form-group">
                            <select id="filter_user" name="filter_user" required class="form-control" style="width: 300px;">
                                <option value="">Select City</option>
                                <?php foreach ($cities as $row) { ?>
                                    <option value='<?= $row['id'] ?>'><?= $row['name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <table class="table table-hover" id="user_table" data-toggle="table" data-url="api-firebase/get-bootstrap-table-data.php?table=users" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-filter-show-clear="true" data-query-params="queryParams_1" data-sort-name="id" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="profile" data-sortable="true">Profile</th>
                                    <th data-field="email" data-sortable="true">Email</th>
                                    <th data-field="mobile" data-sortable="true">M.No</th>
                                    <th data-field="balance" data-sortable="true">Balance</th>
                                    <th data-field="referral_code" data-sortable="true" data-visible="false">Referral code</th>
                                    <th data-field="friends_code" data-sortable="true">Friends code</th>
                                    <th data-field="street" data-sortable="true" data-visible="false">Street</th>
                                    <th data-field="area" data-sortable="true" data-visible="false">Area</th>
                                    <th data-field="city" data-sortable="true" data-visible="false">City</th>
                                    <th data-field="status" data-sortable="true">Status</th>
                                    <th data-field="created_at" data-sortable="true">Date & Time</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <!-- /.box-body -->
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view customers</div>
            <?php } ?>
            <!-- /.box -->
        </div>
    </div>
    <!-- /.row (main row) -->
</section>
<script>
    $('#filter_user').on('change', function() {
        $('#user_table').bootstrapTable('refresh');

    });

    function queryParams_1(p) {
        return {
            "filter_user": $('#filter_user').val(),
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
</script>
<!-- /.content -->