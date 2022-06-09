<?php
// start session
session_start();
// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;
// if session not set go to login page
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}
// if current time is more than session timeout back to login page
if ($currentTime > $_SESSION['timeout']) {
    session_destroy();
    header("location:index.php");
}
// destroy previous session timeout and create new one
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;
?>
<?php include "header.php"; ?>
<html>

<head>
    <meta charset="UTF-8">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Media | <?= $settings['app_name'] ?> - Dashboard</title>

    <style type="text/css">
        .dropzone {
            min-height: 150px;
            border: 3px dashed rgb(10 71 114 / 84%);
            background: white;
            padding: 20px 20px;
        }
    </style>
</head>

<body>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <ol class="breadcrumb">
                <li>
                    <a href="home.php"><i class="fa fa-home"></i> Home</a>
                </li>
            </ol>
            <hr />
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <form method="POST" enctype="multipart/form-data">
                        <div id="dropzone" class="dropzone"></div>
                    </form>
                    <div class="" style="margin-top: 10px;">
                        <input type="submit" id="upload-files-btn" value="Upload" class="btn btn-primary" name="btnAdd" style="float: right;" />
                    </div>
                </div>
            </div>

            <hr />
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="media_table" class="table table-hover table no-margin" data-toggle="table" data-url="api-firebase/get-bootstrap-table-data.php?table=media" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true" data-visible="false">ID</th>
                                            <th data-field="image">Image</th>
                                            <th data-field="name">Name</th>
                                            <th data-field="extension">Extension</th>
                                            <th data-field="type">Type</th>
                                            <th data-field="sub_directory">Sub Directory</th>
                                            <th data-field="size">size</th>
                                            <th data-field="operate">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div><!-- /.content-wrapper -->
    <div class="separator"> </div>

    <?php include "footer.php"; ?>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzone", {
            paramName: "documents",
            url: 'add-media.php',
            autoProcessQueue: false,
            parallelUploads: 1000,
            autoDiscover: false,
            addRemoveLinks: true,
            dictResponseError: 'Error',
            uploadMultiple: true,
            // dictDefaultMessage: '<p><input type="submit" value="Select Files" class="btn btn-primary" /><br> or <br> Drag & Drop Images here</p>',
            dictDefaultMessage: '<p class="text-dark"><b>Select Files <br> or <br> Drag & Drop Images here</b></p>',
        });
        myDropzone.on("addedfile", function(file) {
            var i = 0;
            if (this.files.length) {
                var _i, _len;
                for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
                    if (this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString()) {
                        this.removeFile(file);
                        i++;
                    }
                }
            }
        });

        $('#upload-files-btn').on('click', function(e) {
            e.preventDefault();
            myDropzone.processQueue();
        });
    </script>

    <script>
        $(document).on('click', '.copy_to_clipboard', function() {
            var element = $(this).closest('tr').find('.copy-path');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();
            $(this).html("Copied");
            setTimeout(function() {
                $(this).html(old_html);
            }, 3000);
        });
    </script>

    <script>
        $(document).on('click', '.delete_media', function() {
            if (confirm('Are you sure?')) {
                id = $(this).data("id");
                image = $(this).data("image");
                $.ajax({
                    url: 'public/db-operation.php',
                    type: "post",
                    data: 'id=' + id + '&image=' + image + '&delete_media=1',
                    success: function(result) {
                        $('#media_table').bootstrapTable('refresh');
                    }
                });
            }
        });
    </script>
</body>

</html>