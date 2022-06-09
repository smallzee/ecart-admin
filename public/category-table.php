<?php
include_once('includes/functions.php');
?>
<section class="content-header">
    <h1>Categories /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-category.php"><i class="fa fa-plus-square"></i> Add New Category</a>
    </ol>
</section>
<?php
 $data = $fn->get_settings('categories_settings', true);
if ($permissions['categories']['read'] == 1) {
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
                        <h3 class="box-title">Categories</h3>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form id="add_form" action="public/db-operation.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="add_category_settings" name="add_category_settings" required="" value="1" aria-required="true">

                                <div class="box-body">
                                    <div class="form-group">
                                        <label for='style'>Section Style</label>
                                        <select name='cat_style' id='cat_style' class='form-control' />
                                        <option value="">Select Style</option>
                                        <option value="style_1" <?= (isset($data['cat_style']) && $data['cat_style'] == 'style_1') ? "selected" : "" ?>>Style 1</option>
                                        <option value="style_2" <?= (isset($data['cat_style']) && $data['cat_style'] == 'style_2') ? "selected" : "" ?>>Style 2</option>
                                        </select>
                                        <br>
                                    </div>
                                    <?php
                                    $display = (isset($data['cat_style']) && $data['cat_style'] == 'style_2') ? "style='display:none;'" : "";
                                     ?>
                                    <div class="form-group" id="col1" <?=$display?>>
                                        <label for="max_value">Maximum Number of Visible Categories in Home Page</label>
                                        <input type="number" class="form-control" id="max_visible_categories" value="<?php echo $data['max_visible_categories']; ?>" name="max_visible_categories">
                                    </div>
                                    <div class="form-group" id="col2" <?=$display?>>
                                        <label for="exampleInputEmail1">Maximum Columns in Single Row in Home Page</label>
                                        <input type="number" class="form-control" name="max_col_in_single_row"  value="<?php echo $data['max_col_in_single_row']; ?>" id="max_col_in_single_row" required>
                                    </div>
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                                    </div>
                                    <div class="form-group">
                                        <div id="result" style="display: none;"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="cateory_list" data-url="api-firebase/get-bootstrap-table-data.php?table=category" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams_1">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="subtitle" data-sortable="true">Subtitle</th>
                                    <th data-field="image">Image</th>
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
<?php } else { ?>
    <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view categories.</div>
<?php } ?>
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
    $("#cat_style").change(function() {
        var style = $(this).val();
        if (style == "style_1") {
            $("#col1, #col2").show();
        } else {
            $(" #col2").hide();
        }
    });
    $('#add_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
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
                $('#submit_btn').html('Add');
            }
        });
    });
</script>