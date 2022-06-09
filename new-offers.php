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
<?php include"header.php";
$allowed = ALLOW_MODIFICATION;
?>
<html>
    <head>
        <title>New Offers Images | <?=$settings['app_name']?> - Dashboard</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script> -->
        <script type="text/javascript">
            $(document).ready(function () {
            
            });
        </script>
        <style type="text/css">
            .container{
            width: 950px;
            margin: 0 auto;
            padding: 0;
            }
            h1{
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 24px;
            color: #777;
            }
            h1 .send_btn
            {
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -webkit-linear-gradient(0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -moz-linear-gradient(center top, #0096FF, #005DFF);
            background: linear-gradient(#0096FF, #005DFF);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
            border-radius: 3px;
            color: #fff;
            padding: 3px;
            }
            div.clear{
            clear: both;
            }
            ul.devices{
            margin: 0;
            padding: 0;
            }
            ul.devices li{
            float: left;
            list-style: none;
            border: 1px solid #dedede;
            padding: 10px;
            margin: 0 15px 25px 0;
            border-radius: 3px;
            -webkit-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.35);
            -moz-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.35);
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.35);
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #555;
            width:100%;
            height:150px;
            background-color:#ffffff;
            }
            ul.devices li label, ul.devices li span{
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            font-style: normal;
            font-variant: normal;
            font-weight: bold;
            color: #393939;
            display: block;
            float: left;
            }
            ul.devices li label{
            height: 25px;
            width: 50px;                
            }
            ul.devices li textarea{
            float: left;
            resize: none;
            }
            ul.devices li .send_btn{
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -webkit-linear-gradient(0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -moz-linear-gradient(center top, #0096FF, #005DFF);
            background: linear-gradient(#0096FF, #005DFF);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
            border-radius: 7px;
            color: #fff;
            padding: 4px 24px;
            }
            a{text-decoration:none;color:rgb(245,134,52);}
        </style>
    </head>
    <body>
        <div class="content-wrapper">
        <section class="content-header">
            <h1>Images for New Offers for Customers</h1>
            <ol class="breadcrumb">
                <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
            </ol>
            <hr/>
        </section>
        <?php
            include_once('includes/functions.php');
            

            
          
            ?>
        <section class="content">
            <div class="row">
                <div class="col-md-6">
                    <?php if($permissions['new_offers']['create']==0) { ?>
                        <div class="alert alert-danger">You have no permission to create new offers</div>
                    <?php } ?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Add New Offers Images here</h3>
                        </div>
                        <form id="offer_form" method="post" action="api-firebase/offer-images.php" enctype="multipart/form-data">
                            <div class="box-body">
                                <input type='hidden' name='accesskey' id='accesskey' value='90336'/>
                                <input type='hidden' name='add-image' id='add-image' value='1'/>
                                <input type='hidden' name='ajax_call' value='1'/>
                                <div class="form-group">
                                    <label for="image">Offer Image :</label>
                                    <input type='file' name="image" id="image" required/> 
                                </div>
                            </div>
                            <div class="box-footer">
                                <input type="submit" id="submit_btn" class="btn-primary btn" value="Upload"/>
                            </div>
                        </form>
                        <div id="result"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php if($permissions['new_offers']['read']==1){?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">New Offer Images</h3>
                        </div>
                        <table id="offers_table" class="table table-hover" data-toggle="table" 
                            data-url="api-firebase/get-bootstrap-table-data.php?table=offers"
                            data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-show-refresh="true" data-show-columns="true"
                            data-side-pagination="server" data-pagination="true"
                            data-search="true" data-trim-on-search="false"
                            data-sort-name="id" data-sort-order="desc">
                            <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="image">Image</th>
                                <th data-field="date_created" data-visible="false">Date Created</th>
                                <th data-field="operate">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view new offer images.</div>
            <?php } ?>
                </div>
            </div>
        </section>
    </div>
    <script>

var allowed  = '<?= $allowed; ?>';
      $('#offer_form').on('submit',function(e){
        e.preventDefault();
        if (allowed == 0) {
            alert('Sorry! This operation is not allowed in demo panel!.');
            window.location.reload();
            return false;
        }
        var formData = new FormData(this);
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            dataType:'json',
            beforeSend:function(){$('#submit_btn').val('Please wait..').attr('disabled',true);},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#result').html(result.message);
                $('#result').show().delay(2000).fadeOut();
                $('#submit_btn').val('Upload').attr('disabled',false);
                $('#image').val('');
                $('#offers_table').bootstrapTable('refresh');
            }
            });
        
    }); 
    </script>
    <script>
    $(document).on('click','.delete-offer',function(){
        if(confirm('Are you sure?')){
            id = $(this).data("id");
            image = $(this).data("image");
            $.ajax({
                url : 'api-firebase/offer-images.php',
                type: "get",
                data: 'accesskey=90336&id='+id+'&image='+image+'&type=delete-offer&ajax_call=1',
                success: function(result){
                    if(result==1){
                        $('#offers_table').bootstrapTable('refresh');
                    }
                    if(result==2){
                        alert('You have no permission to delete new offers');
                    }if(result==0){
                        alert('Error! offer could not be deleted');

                    }
                        
                }
            });
        }
    });
    </script>
</body>
</html>
<?php include"footer.php"; ?>