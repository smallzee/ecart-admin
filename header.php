<?php include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');
include_once('includes/functions.php');
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);
$config = $fn->get_configurations();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$settings['app_name'] = $config['app_name'];
$words = explode(" ", $settings['app_name']);
$acronym = "";
foreach ($words as $w) {
    $acronym .= $w[0];
}

$currency = $fn->get_settings('currency');
$settings['currency'] = $currency;
$isAuth = $fn->get_settings('doctor_brown');
$cal_time_check = $time_check = '';
if (!empty($isAuth)) {
    $isAuth = json_decode($isAuth);
    $time_check = $isAuth->time_check;
    $str = trim($isAuth->code_bravo) . "|" . trim($isAuth->code_adam) . "|" . trim($isAuth->dr_firestone);
    $cal_time_check = hash('sha256', $str);
}

$role = $fn->get_role($_SESSION['id']);
$sql_logo = "select value from `settings` where variable='Logo' OR variable='logo'";
$db->sql($sql_logo);
$res_logo = $db->getResult();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/ico" href="<?= 'dist/img/' . $res_logo[0]['value'] ?>">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">


    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link href="dist/css/multiple-select.css" rel="stylesheet" />
    <!--<link rel="stylesheet" href="plugins/select2/select2.min.css">-->
    <!--	 <link rel="stylesheet" href="plugins/select2/select2.min.css">=
        <link rel="stylesheet" href="plugins/select2/select2.css">-->
    <!-- AdminLTE Skins. Choose a skin from the css/skins
            folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/print.css" type="text/css" media="print">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
    <!-- Morris chart 
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.css" integrity="sha256-2kJr1Z0C1y5z0jnhr/mCu46J3R6Uud+qCQHA39i1eYo=" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js" integrity="sha256-CgrKEb54KXipsoTitWV+7z/CVYrQ0ZagFB3JOvq2yjo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            var date = new Date();
            var currentMonth = date.getMonth() - 10;
            var currentDate = date.getDate();
            var currentYear = date.getFullYear() - 10;

            $('.datepicker').datepicker({
                minDate: new Date(currentYear, currentMonth, currentDate),
                dateFormat: 'yy-mm-dd',
            });
        });
    </script>
    <script language="javascript">
        function printpage() {
            window.print();
        }
    </script>

    <link rel="stylesheet" href="https://rawgit.com/enyo/dropzone/master/dist/dropzone.css">
    <link rel="stylesheet" href="plugins/morris/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/extensions/filter-control/bootstrap-table-filter-control.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.4.1/jquery.fancybox.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.4.1/jquery.fancybox.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.1/css/lightbox.min.css" integrity="sha256-tBxlolRHP9uMsEFKVk+hk//ekOlXOixLKvye5W2WR5c=" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.1/js/lightbox.min.js" integrity="sha256-CtKylYan+AJuoH8jrMht1+1PMhMqrKnB8K5g012WN5I=" crossorigin="anonymous"></script>
</head>

<body class="hold-transition skin-blue fixed sidebar-mini">
    <div class="wrapper">
<!--        --><?php
//        if ($time_check != $cal_time_check || empty($cal_time_check) || empty($time_check)) {
//            $file = basename($_SERVER['PHP_SELF']);
//            if ($file != 'purchase-code.php') { ?>
<!--                <div class="overlay" style="background: #000000d9; position: absolute; width: 100%; height: 307%; z-index: 9999999;">-->
<!--                    <div class="container text-center " style="background: white; padding: 100px; margin-top: 10%;">-->
<!--                        <div>-->
<!--                            <h4>Please activate the system use it further</h4>-->
<!--                            <a href="purchase-code.php"><i class="fa fa-check"></i> Register system</a>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--        --><?php //exit();
//            }
//        } ?>
        <header class="main-header">
            <!-- Logo -->
            <a href="home.php" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini">
                    <h2><?= $acronym ?></h2>
                </span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">
                    <h3><?= $settings['app_name'] ?></h3>
                </span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- User Account: style can be found in dropdown.less -->
                        <?php
                        $sql_query = "SELECT * FROM admin where id=" . $_SESSION['id'];

                        $db->sql($sql_query);
                        $result = $db->getResult();
                        foreach ($result as $row) {
                            $user = $row['username'];
                            $email = $row['email'];
                        ?>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="images/avatar.png" class="user-image" alt="User Image">
                                    <span class="hidden-xs"><?= $user; ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="images/avatar.png" class="img-circle" alt="User Image">
                                        <p>
                                            <?= $user; ?>
                                            <small><?= $email; ?></small>
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="admin-profile.php" class="btn btn-default btn-flat"> Edit Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="logout.php" class="btn btn-default btn-flat">Log out</a>
                                        </div>
                                    </li>
                                    <!-- Menu Body -->
                                    <!-- Menu Footer-->
                                </ul>
                            </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <!-- <div class="user-panel">
                    <div class="pull-left image">
                      <img src="images/avatar.png" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                      <p><?//=$user;?></p>
                    <?php } ?>
                      <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                    </div>-->
                <!-- search form -->
                <!--<form action="#" method="get" class="sidebar-form">
                    <div class="input-group">
                      <input type="text" name="q" class="form-control" placeholder="Search...">
                      <span class="input-group-btn">
                        <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
                      </span>
                    </div>
                    </form>-->
                <!-- /.search form -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu">
                    <li class="treeview">
                        <a href="home.php">
                            <i class="fa fa-home" class="active"></i> <span>Home</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="orders.php">
                            <i class="fa fa-shopping-cart"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-bullseye"></i>
                            <span>Categories</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="categories.php"><i class="fa fa-sliders"></i> Manage Categories</a></li>
                            <li><a href="categories-order.php"><i class="fa fa-reorder"></i> Categories Order</a></li>
                        </ul>
                        <!-- <a href="categories.php">
                            <i class="fa fa-bullseye"></i> <span>Categories</span>
                        </a> -->
                    </li>
                    <li>
                        <a href="subcategories.php">
                            <i class="fa fa-bullseye"></i> <span>Sub Categories</span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-cubes"></i>
                            <span>Products</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="add-product.php"><i class="fa fa-plus"></i> Add Product</a></li>
                            <li><a href="products.php"><i class="fa fa-sliders"></i> Manage Products</a></li>
                            <li><a href="bulk-upload.php"><i class="fa fa-upload"></i> Bulk Upload</a></li>
                            <li><a href="bulk-update.php"><i class="fa fa-pencil"></i> Bulk Update</a></li>
                            <li><a href="products-taxes.php"><i class="fa fa-plus"></i> Taxes</a></li>
                            <li><a href="products-order.php"><i class="fa fa-reorder"></i> Products Order</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="main-slider.php">
                            <i class="fa fa-picture-o"></i>
                            <span>Home Slider Images</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="new-offers.php">
                            <i class="fa fa-gift"></i>
                            <span>New Offers Images</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="media.php">
                            <i class="fa fa-file-image-o"></i>
                            <span>Media</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="promo-code.php">
                            <i class="fa fa-gift"></i>
                            <span>Promo code</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="sections.php">
                            <i class="fa fa-puzzle-piece"></i>
                            <span>Featured Sections</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-male"></i>
                            <span>Customers</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="customers.php"><i class="fa fa-users"></i> Customers </a></li>
                            <li><a href="transaction.php"><i class="fa fa-exchange"></i> Transactions</a></li>
                            <li><a href="wallet-transactions.php"><i class="fa fa-exchange"></i> Wallet Transactions</a></li>
                            <li><a href="manage-customer-wallet.php"><i class="fa fa-line-chart"></i> Manage Customer Wallet</a></li>

                        </ul>
                    </li>
                    <!-- <li class="treeview">
                        <a href="payment-requests.php">
                            <i class="fa fa-money"></i> <span>Payment Requests</span>
                        </a>
                    </li> -->
                    <li class="treeview">
                        <a href="return-requests.php">
                            <i class="fa fa-retweet"></i> <span>Return Requests</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="withdrawal-requests.php">
                            <i class="fa fa-credit-card"></i> <span> Withdrawal Requests </span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-male"></i>
                            <span>Delivery Boys</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="delivery-boys.php"><i class="fa fa-line-chart"></i> Manage Delivery Boys </a></li>
                            <li><a href="fund-transfers.php"><i class="fa fa-inr"></i> Fund Transfers</a></li>

                        </ul>
                    </li>

                    <li class="treeview">
                        <a href="notification.php"> <i class="fa fa-share-square-o"></i><span>Send notification</span>
                        </a>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-wrench"></i>
                            <span>Web Front-End Settings</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="web-header.php"><i class="fa fa-chevron-circle-up"></i> Header</i></a>

                            </li>
                            <li><a href="web-category.php"><i class="fa fa-picture-o"></i> Category Image </a></li>

                            <li class=""><a href="#"><i class="fa fa-chevron-circle-down"></i> Footer <i class="fa fa-angle-right pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="social-media.php"><i class="fa fa-facebook"></i> Social Media </a></li>
                                    <!-- <li><a href="support-timing.php"><i class="fa fa-life-ring"></i> Support Timing </a></li> -->
                                </ul>
                            </li>

                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-wrench"></i>
                            <span>System</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="settings.php"><i class="fa fa-cogs"></i> Store Settings </a></li>
                            <li><a href="payment-methods-settings.php"><i class="fa fa-dollar"></i> Payment Methods </a></li>
                            <li><a href="time-slots.php"><i class="fa fa-clock-o"></i> Time slots </a></li>
                            <li><a href="notification-settings.php"><i class="fa fa-bell-o"></i> Notification Settings</a></li>
                            <li><a href="units.php"><i class="fa fa-dot-circle-o"></i> Units</a></li>
                            <li><a href="contact-us.php"><i class="fa fa-phone"></i> Contact Us </a></li>
                            <li><a href="privacy-policy.php"><i class="fa fa-user-secret"></i> Privacy Policy </a></li>
                            <li><a href="delivery-boy-privacy-policy.php"><i class="fa fa-exclamation-triangle"></i> Delivery Boy Privacy Policy </a></li>
                            <li><a href="manager-app-privacy-policy.php"><i class="fa fa-lock"></i> Manager App Privacy Policy </a></li>
                            <!-- <li><a href="manager-app-privacy-policy.php"><i class="fa fa-lock"></i> Manager App Privacy Policy </a></li> -->
                            <li><a href="about-us.php"><i class="fa fa-info"></i> About Us </a></li>
                            <li><a href="purchase-code.php"><i class="fa fa-check"></i> System Registration </a></li>

                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-map-marker"></i>
                            <span>Location</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="city.php"><i class="fa fa-location-arrow"></i> Cities</a></li>
                            <li><a href="areas.php"><i class="fa fa-reorder"></i> Areas </a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-folder-open"></i>
                            <span>Reports</span>
                            <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="product-sales-report.php"><i class="fa fa-money"></i>Product Sales Report</a></li>
                            <li><a href="sales-report.php"><i class="fa fa-money"></i>Sales Report</a></li>
                            <li><a href="invoices.php"><i class="fa fa-money"></i>Invoice Report</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="faq.php">
                            <i class="fa fa-info"></i> <span>FAQs</span>
                            <?php
                            $query = "select * from faq where status=1 ";
                            $db->sql($query);
                            $result = $db->getResult();
                            $count = $db->numRows($result);
                            if ($count) { ?>
                                <span class="label label-primary pull-right"><?php echo $count; ?></span>
                            <?php    } ?>
                        </a>
                    </li>
                    <?php
                    if ($role == 'admin' || $role == 'super admin') {
                    ?>
                        <li class="treeview">
                            <a href="system-users.php">
                                <i class="fa fa-users" class="active"></i> <span>System Users</span>
                            </a>
                        </li>
                    <?php }
                    $query = "SELECT version FROM updates ORDER BY id DESC LIMIT 1";
                    $db->sql($query);
                    $result = $db->getResult();
                    if (!empty($result)) {
                    ?>
                        <center><a href="home.php" class="label label-success"><?= $result[0]['version'] ?></a></center>
                    <?php } ?>
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
</body>

</html>