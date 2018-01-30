<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $_SESSION['adminUser']['company']['subname']?> | 管理系统</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/Ionicons/css/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link href="Public/AdminLTE/bower_components/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
    <!-- Theme style -->
    <link rel="stylesheet" href="Public/AdminLTE/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="Public/AdminLTE/dist/css/skins/_all-skins.min.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/morris.js/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/jvectormap/jquery-jvectormap.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <!-- Datetime picker -->
    <link rel="stylesheet" href="Public/AdminLTE/bower_components/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="Public/AdminLTE/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <link rel="stylesheet" href="Public/Plugin/bootstrap-fileinput/css/fileinput.css">

    <link rel="stylesheet" href="Public/Plugin/bootstrap-select/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="Public/Home/css/common.css">
    <link rel="stylesheet" href="Public/Home/css/pagination.css">

    <!--<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">-->
    <!--<link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">-->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic"> -->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="#" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b><?php echo $_SESSION['adminUser']['company']['smallname']?></b></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b><?php echo $_SESSION['adminUser']['company']['name']?></b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- Messages: style can be found in dropdown.less-->
                    <!-- <li class="dropdown messages-menu">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-envelope-o"></i>
                        <span class="label label-success">4</span>
                      </a>
                      <ul class="dropdown-menu">
                        <li class="header">You have 4 messages</li>
                        <li>
                          inner menu: contains the actual data
                          <ul class="menu">
                            <li>start message
                              <a href="#">
                                <div class="pull-left">
                                  <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                                </div>
                                <h4>
                                  Support Team
                                  <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                </h4>
                                <p>Why not buy a new awesome theme?</p>
                              </a>
                            </li>
                            end message
                            <li>
                              <a href="#">
                                <div class="pull-left">
                                  <img src="dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                                </div>
                                <h4>
                                  AdminLTE Design Team
                                  <small><i class="fa fa-clock-o"></i> 2 hours</small>
                                </h4>
                                <p>Why not buy a new awesome theme?</p>
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <div class="pull-left">
                                  <img src="dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                                </div>
                                <h4>
                                  Developers
                                  <small><i class="fa fa-clock-o"></i> Today</small>
                                </h4>
                                <p>Why not buy a new awesome theme?</p>
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <div class="pull-left">
                                  <img src="dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                                </div>
                                <h4>
                                  Sales Department
                                  <small><i class="fa fa-clock-o"></i> Yesterday</small>
                                </h4>
                                <p>Why not buy a new awesome theme?</p>
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <div class="pull-left">
                                  <img src="dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                                </div>
                                <h4>
                                  Reviewers
                                  <small><i class="fa fa-clock-o"></i> 2 days</small>
                                </h4>
                                <p>Why not buy a new awesome theme?</p>
                              </a>
                            </li>
                          </ul>
                        </li>
                        <li class="footer"><a href="#">See All Messages</a></li>
                      </ul>
                    </li> -->
                    <!-- Notifications: style can be found in dropdown.less -->
                    <!-- <li class="dropdown notifications-menu">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning">10</span>
                      </a>
                      <ul class="dropdown-menu">
                        <li class="header">You have 10 notifications</li>
                        <li>
                          inner menu: contains the actual data
                          <ul class="menu">
                            <li>
                              <a href="#">
                                <i class="fa fa-users text-aqua"></i> 5 new members joined today
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <i class="fa fa-warning text-yellow"></i> Very long description here that may not fit into the
                                page and may cause design problems
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <i class="fa fa-users text-red"></i> 5 new members joined
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                              </a>
                            </li>
                            <li>
                              <a href="#">
                                <i class="fa fa-user text-red"></i> You changed your username
                              </a>
                            </li>
                          </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                      </ul>
                    </li> -->
                    <!-- Tasks: style can be found in dropdown.less -->
                    <!--  <li class="dropdown tasks-menu">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-flag-o"></i>
                        <span class="label label-danger">9</span>
                      </a>
                      <ul class="dropdown-menu">
                        <li class="header">You have 9 tasks</li>
                        <li>
                          inner menu: contains the actual data
                          <ul class="menu">
                            <li>Task item
                              <a href="#">
                                <h3>
                                  Design some buttons
                                  <small class="pull-right">20%</small>
                                </h3>
                                <div class="progress xs">
                                  <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar"
                                       aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only">20% Complete</span>
                                  </div>
                                </div>
                              </a>
                            </li>
                            end task item
                            <li>Task item
                              <a href="#">
                                <h3>
                                  Create a nice theme
                                  <small class="pull-right">40%</small>
                                </h3>
                                <div class="progress xs">
                                  <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar"
                                       aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only">40% Complete</span>
                                  </div>
                                </div>
                              </a>
                            </li>
                            end task item
                            <li>Task item
                              <a href="#">
                                <h3>
                                  Some task I need to do
                                  <small class="pull-right">60%</small>
                                </h3>
                                <div class="progress xs">
                                  <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar"
                                       aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only">60% Complete</span>
                                  </div>
                                </div>
                              </a>
                            </li>
                            end task item
                            <li>Task item
                              <a href="#">
                                <h3>
                                  Make beautiful transitions
                                  <small class="pull-right">80%</small>
                                </h3>
                                <div class="progress xs">
                                  <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar"
                                       aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only">80% Complete</span>
                                  </div>
                                </div>
                              </a>
                            </li>
                            end task item
                          </ul>
                        </li>
                        <li class="footer">
                          <a href="#">View all tasks</a>
                        </li>
                      </ul>
                    </li> -->
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php echo $_SESSION['adminUser']['avatarurl']?>" class="user-image" alt="User Image">
                            <span class="hidden-xs"><?php echo $_SESSION['adminUser']['username']?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?php echo $_SESSION['adminUser']['avatarurl']?>" class="img-circle" alt="User Image">

                                <p>
                                    <?php echo $_SESSION['adminUser']['username']?>
                                    <small><?php echo $_SESSION['adminUser']['department']?>&nbsp;&nbsp;<?php echo $_SESSION['adminUser']['position']?></small>
                                </p>
                            </li>
                            <!-- Menu Body -->
                            <!-- <li class="user-body">
                              <div class="row">
                                <div class="col-xs-4 text-center">
                                  <a href="#">Followers</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                  <a href="#">Sales</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                  <a href="#">Friends</a>
                                </div>
                              </div>

                            </li> -->
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="#" class="btn btn-default btn-flat">个人信息</a>
                                </div>
                                <div class="pull-right">
                                    <a href="<?php echo U('home/login/loginOut');?>" class="btn btn-default btn-flat">退出登陆</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <!-- Control Sidebar Toggle Button -->
                    <li>
                        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
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
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo $_SESSION['adminUser']['avatarurl']?>" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p><?php echo $_SESSION['adminUser']['username']?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> <?php echo $_SESSION['adminUser']['position']?></a>
                </div>
            </div>
            <!-- search form -->
            <form action="#" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="查找...">
                    <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
                </div>
            </form>

            <?php
 $controller_name = $Think.CONTROLLER_NAME; $action_name = $Think.ACTION_NAME; ?>
            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">功能面板</li>
                <!--<li <?php if(($controller_name == 'Index') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>-->
                    <!--<a href="<?php echo U('home/index/index');?>">-->
                        <!--<i class="fa fa-leanpub"></i> <span>业务总览</span>-->
                    <!--</a>-->
                <!--</li>-->

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2) { ?>
                <li <?php if(($controller_name == 'Profit') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/profit/index');?>">
                    <i class="fa fa-ticket"></i> <span>利润报表</span>
                </a>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Collection') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/collection/index');?>">
                    <i class="fa fa-deafness"></i> <span>每日应收</span>
                </a>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if($controller_name == 'Staff'): ?>class=" treeview active"<?php else: ?>class="treeview"<?php endif; ?>>
                    <a href="#">
                        <i class="fa fa-sitemap"></i>
                        <span>员工管理</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li <?php if(($controller_name == 'Staff') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/staff/index');?>"><i class="fa fa-circle-o"></i> 员工信息</a></li>
                        <li <?php if(($controller_name == 'Staff') AND ($action_name == 'add')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/staff/add');?>"><i class="fa fa-circle-o"></i> 添加员工</a></li>
                    </ul>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if($controller_name == 'Customer'): ?>class=" treeview active"<?php else: ?>class="treeview"<?php endif; ?>>
                    <a href="#">
                        <i class="fa fa-users"></i>
                        <span>客户管理</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li <?php if(($controller_name == 'Customer') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/customer/index');?>"><i class="fa fa-circle-o"></i> 客户资料</a></li>
                        <li <?php if(($controller_name == 'Customer') AND ($action_name == 'add')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/customer/add');?>"><i class="fa fa-circle-o"></i> 添加客户</a></li>
                    </ul>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if($controller_name == 'Loan'): ?>class=" treeview active"<?php else: ?>class="treeview"<?php endif; ?>>
                    <a href="#">
                        <i class="fa fa-inbox"></i>
                        <span>借款记录</span>
                        <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
                </span>
                    </a>
                    <ul class="treeview-menu">
                        <li <?php if(($controller_name == 'Loan') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/loan/index');?>"><i class="fa fa-circle-o"></i> 贷款资料</a></li>
                        <li <?php if(($controller_name == 'Loan') AND ($action_name == 'add')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/loan/add');?>"><i class="fa fa-circle-o"></i> 添加记录</a></li>
                    </ul>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Repayments') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/repayments/index');?>">
                    <i class="fa fa-google-wallet"></i> <span>还款记录</span>
                </a>
                </li>
                <?php } ?>


                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Tour') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/tour/index');?>">
                    <i class="fa fa-car"></i> <span>外访记录</span>
                </a>
                </li>
                <?php } ?>


                <li <?php if(($controller_name == 'Overdue') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/overdue/index');?>">
                    <i class="fa fa-firefox"></i> <span>逾期汇总</span>
                </a>
                </li>

                <li <?php if(($controller_name == 'Settle') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/settle/index');?>">
                    <i class="fa fa-tag"></i> <span>结清汇总</span>
                </a>
                </li>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Charge') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/charge/index');?>">
                    <i class="fa fa-shopping-bag"></i> <span>现金记账</span>
                </a>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Wage') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/wage/index');?>">
                    <i class="fa fa-id-badge"></i> <span>工资管理</span>
                </a>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Message') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/message/index');?>">
                    <i class="fa fa-database"></i> <span>短信记录</span>
                </a>
                </li>
                <?php } ?>

                <?php if($_SESSION['adminUser']['jurisdiction'] == 2 || $_SESSION['adminUser']['jurisdiction'] == 1) { ?>
                <li <?php if(($controller_name == 'Image') AND ($action_name == 'index')): ?>class="active"<?php endif; ?>>
                <a href="<?php echo U('home/image/index');?>">
                    <i class="fa fa-map-signs"></i> <span>外访资料</span>
                </a>
                </li>
                <?php } ?>



                <li <?php if($controller_name == 'Loan'): ?>class=" treeview active"<?php else: ?>class="treeview"<?php endif; ?>>
                <a href="#">
                    <i class="fa fa-inbox"></i>
                    <span>其它工具</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
                </span>
                </a>
                <ul class="treeview-menu">
                    <li <?php if(($controller_name == 'Other') AND ($action_name == 'userinfo')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/other/userInfo');?>"><i class="fa fa-circle-o"></i> 个人资料</a></li>
                    <li <?php if(($controller_name == 'Other') AND ($action_name == 'changepwd')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/other/changePwd');?>"><i class="fa fa-circle-o"></i> 修改密码</a></li>
                    <?php if($_SESSION['adminUser']['jurisdiction'] == 2) { ?>
                    <li <?php if(($controller_name == 'Other') AND ($action_name == 'adminlist')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/other/adminList');?>"><i class="fa fa-circle-o"></i> 后台用户管理</a></li>
                    <li <?php if(($controller_name == 'Other') AND ($action_name == 'wxuserlist')): ?>class="active"<?php endif; ?>><a href="<?php echo U('home/other/wxuserList');?>"><i class="fa fa-circle-o"></i> 微信用户管理</a></li>
                    <?php } ?>

                </ul>
                </li>
                <!--<li class="treeview">-->
                    <!--<a href="#">-->
                        <!--<i class="fa fa-edit"></i> <span>上门记录</span>-->
                        <!--<span class="pull-right-container">-->
              <!--<i class="fa fa-angle-left pull-right"></i>-->
            <!--</span>-->
                    <!--</a>-->
                    <!--<ul class="treeview-menu">-->
                        <!--<li><a href="pages/forms/general.html"><i class="fa fa-circle-o"></i> General Elements</a></li>-->
                        <!--<li><a href="pages/forms/advanced.html"><i class="fa fa-circle-o"></i> Advanced Elements</a></li>-->
                        <!--<li><a href="pages/forms/editors.html"><i class="fa fa-circle-o"></i> Editors</a></li>-->
                    <!--</ul>-->
                <!--</li>-->
                <!--<li class="treeview">-->
                    <!--<a href="#">-->
                        <!--<i class="fa fa-table"></i> <span>其它工具</span>-->
                        <!--<span class="pull-right-container">-->
              <!--<i class="fa fa-angle-left pull-right"></i>-->
            <!--</span>-->
                    <!--</a>-->
                    <!--<ul class="treeview-menu">-->
                        <!--<li><a href="pages/tables/simple.html"><i class="fa fa-circle-o"></i> Simple tables</a></li>-->
                        <!--<li><a href="pages/tables/data.html"><i class="fa fa-circle-o"></i> Data tables</a></li>-->
                    <!--</ul>-->
                <!--</li>-->
                <!--<li>-->
                    <!--<a href="pages/calendar.html">-->
                        <!--<i class="fa fa-calendar"></i> <span>Calendar</span>-->
                        <!--<span class="pull-right-container">-->
              <!--<small class="label pull-right bg-red">3</small>-->
              <!--<small class="label pull-right bg-blue">17</small>-->
            <!--</span>-->
                    <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                    <!--<a href="pages/mailbox/mailbox.html">-->
                        <!--<i class="fa fa-envelope"></i> <span>Mailbox</span>-->
                        <!--<span class="pull-right-container">-->
              <!--<small class="label pull-right bg-yellow">12</small>-->
              <!--<small class="label pull-right bg-green">16</small>-->
              <!--<small class="label pull-right bg-red">5</small>-->
            <!--</span>-->
                    <!--</a>-->
                <!--</li>-->
                <!--<li class="treeview">-->
                    <!--<a href="#">-->
                        <!--<i class="fa fa-folder"></i> <span>Examples</span>-->
                        <!--<span class="pull-right-container">-->
              <!--<i class="fa fa-angle-left pull-right"></i>-->
            <!--</span>-->
                    <!--</a>-->
                    <!--<ul class="treeview-menu">-->
                        <!--<li><a href="pages/examples/invoice.html"><i class="fa fa-circle-o"></i> Invoice</a></li>-->
                        <!--<li><a href="pages/examples/profile.html"><i class="fa fa-circle-o"></i> Profile</a></li>-->
                        <!--<li><a href="pages/examples/login.html"><i class="fa fa-circle-o"></i> Login</a></li>-->
                        <!--<li><a href="pages/examples/register.html"><i class="fa fa-circle-o"></i> Register</a></li>-->
                        <!--<li><a href="pages/examples/lockscreen.html"><i class="fa fa-circle-o"></i> Lockscreen</a></li>-->
                        <!--<li><a href="pages/examples/404.html"><i class="fa fa-circle-o"></i> 404 Error</a></li>-->
                        <!--<li><a href="pages/examples/500.html"><i class="fa fa-circle-o"></i> 500 Error</a></li>-->
                        <!--<li><a href="pages/examples/blank.html"><i class="fa fa-circle-o"></i> Blank Page</a></li>-->
                        <!--<li><a href="pages/examples/pace.html"><i class="fa fa-circle-o"></i> Pace Page</a></li>-->
                    <!--</ul>-->
                <!--</li>-->
                <!--<li class="treeview">-->
                    <!--<a href="#">-->
                        <!--<i class="fa fa-share"></i> <span>Multilevel</span>-->
                        <!--<span class="pull-right-container">-->
              <!--<i class="fa fa-angle-left pull-right"></i>-->
            <!--</span>-->
                    <!--</a>-->
                    <!--<ul class="treeview-menu">-->
                        <!--<li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>-->
                        <!--<li class="treeview">-->
                            <!--<a href="#"><i class="fa fa-circle-o"></i> Level One-->
                                <!--<span class="pull-right-container">-->
                  <!--<i class="fa fa-angle-left pull-right"></i>-->
                <!--</span>-->
                            <!--</a>-->
                            <!--<ul class="treeview-menu">-->
                                <!--<li><a href="#"><i class="fa fa-circle-o"></i> Level Two</a></li>-->
                                <!--<li class="treeview">-->
                                    <!--<a href="#"><i class="fa fa-circle-o"></i> Level Two-->
                                        <!--<span class="pull-right-container">-->
                      <!--<i class="fa fa-angle-left pull-right"></i>-->
                    <!--</span>-->
                                    <!--</a>-->
                                    <!--<ul class="treeview-menu">-->
                                        <!--<li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>-->
                                        <!--<li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>-->
                                    <!--</ul>-->
                                <!--</li>-->
                            <!--</ul>-->
                        <!--</li>-->
                        <!--<li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>-->
                    <!--</ul>-->
                <!--</li>-->
                <!--<li class="header">系统提示</li>-->
                <!--<li><a href="#"><i class="fa fa-circle-o text-red"></i> <span>重要信息</span></a></li>-->
                <!--<li><a href="#"><i class="fa fa-circle-o text-yellow"></i> <span>警告信息</span></a></li>-->
                <!--<li><a href="#"><i class="fa fa-circle-o text-aqua"></i> <span>普通信息</span></a></li>-->
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>


  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        工资管理
        <small>员工工资记录表</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> 工资管理</a></li>
        <li><a href="#">员工工资记录表</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">

          <div class="col-xs-12">
              <div class="panel panel-default">
                  <div class="panel-body panel-body-footer">
                      <form class="form-inline search-line">

                          <div class="form-group">
                              <label>开始年月&nbsp;</label>
                              <input type="text" class="form-control sandbox-container-spacile" placeholder="请选择开始年月"  name="search_datepicker_start" id="search_datepicker_start" value="<?php echo ($input_datepicker_start); ?>" >
                          </div>
                          &nbsp;&nbsp;
                          <div class="form-group">
                              <label>截止年月&nbsp;</label>
                              <input type="text" class="form-control sandbox-container-spacile" placeholder="请选择截止年月"  name="search_datepicker_end" id="search_datepicker_end" value="<?php echo ($input_datepicker_end); ?>" >
                          </div>

                          <?php if($userInfo["jurisdiction"] == 2): ?>&nbsp;&nbsp;
                              <div class="form-group">
                                  <label>所属公司&nbsp;</label>
                                  <select class="form-control selectpicker show-tick" title="按所属公司搜索" data-size="8" name="search_department" id="search_company" data-width="fit" multiple>
                                      <?php if(is_array($companys)): $i = 0; $__LIST__ = $companys;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$company): $mod = ($i % 2 );++$i; if($company["selected"] == 1): ?><option value="<?php echo ($company["company_id"]); ?>" selected><?php echo ($company["smallname"]); ?></option>
                                              <?php else: ?>
                                              <option value="<?php echo ($company["company_id"]); ?>"><?php echo ($company["smallname"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                  </select>
                              </div><?php endif; ?>
                          &nbsp;&nbsp;
                          <button type="button" class="btn btn-info" id="search-wage"><i class="fa fa-search fa-fw"></i> 立即搜索</button>
                          &nbsp;&nbsp;
                          <button type="button" class="btn btn-default" data-toggle="modal" data-target="#addInfoModal" id="add-info-modal"><i class="fa fa-plus fa-fw"></i> 添加记录</button>
                          &nbsp;&nbsp;
                          <button type="button" class="btn btn-default" id="export-wage"><i class="fa fa-file-excel-o fa-fw"></i> 导出表格</button>
                      </form>


                  </div>
              </div>



          </div>
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <!--<h3 class="box-title">Data Table With Full Features</h3>-->

                <h5><text class="text-info">工资社保合计：<?php echo ($sumTotal); ?>（元）</text></h5>

            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <!--<th class="text-center">还款ID</th>-->
                    <!--<th class="text-center">借款ID</th>-->
                    <th class="text-center">月份</th>
                    <th class="text-center">员工数（人）</th>
                    <th class="text-center">员工工资总额（元）</th>
                    <th class="text-center">员工社保总额（元）</th>
                    <th class="text-center">合计（元）</th>
                    <th class="text-center">备注</th>
                    <?php if($userInfo["jurisdiction"] == 2): ?><th class="text-center">所属公司</th><?php endif; ?>
                    <th class="text-center">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if(is_array($wages)): $i = 0; $__LIST__ = $wages;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$wage): $mod = ($i % 2 );++$i;?><tr>
                        <!--<td class="text-center"><?php echo ($repayment["repayments_id"]); ?></td>-->
                        <!--<td class="text-center"><?php echo ($repayment["loan_id"]); ?></a></td>-->
                        <td class="text-center"><?php echo ($wage["gmt_wage"]); ?></td>
                        <td class="text-center"><?php echo ($wage["number"]); ?></td>
                        <td class="text-center"><?php echo ($wage["wage"]); ?></td>
                        <td class="text-center"><?php echo ($wage["insur"]); ?></td>
                        <td class="text-center"><?php echo ($wage["total"]); ?></td>
                        <td class="text-center"><?php echo ($wage["remark"]); ?></td>
                        <?php if($userInfo["jurisdiction"] == 2): ?><td class="text-center"><?php echo ($wage["company_name"]); ?></td><?php endif; ?>
                        <td class="text-center"><span id="edit-wageinfo" attr-id="<?php echo ($wage["wage_id"]); ?>" attr-m="home" attr-c="wage" attr-a="editWage" data-toggle="modal" data-target="#editInfoModal"><i class="fa fa-edit fa-fw"></i></span>&nbsp;<span id="delete-info" attr-id="<?php echo ($wage["wage_id"]); ?>" attr-name="<?php echo ($wage["gmt_wage"]); ?>的工资记录" attr-m="home" attr-c="wage" attr-a="deleteWage"><i class="fa fa-trash-o fa-fw"></i></span></td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                </tbody>
              </table>
                <div class="pagination" ><?php echo ($pageRes); ?></div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->



    </section>
    <!-- /.content -->
  </div>

    <div class="modal fade" id="addInfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="data-dismiss"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">新增工资记录</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="baoju-form">
                        <!-- text input -->
                        <div class="form-group col-lg-12">
                            <label>所属年月</label>
                            <input type="text" class="form-control sandbox-container-spacile" name="gmt_wage" id="gmt_wage" placeholder="请选择发放时间">
                        </div>
                        <div class="form-group col-lg-12">
                            <label>员工数</label>
                            <input type="text" class="form-control " name="number" placeholder="请输入员工人数" >
                        </div>
                        <div class="form-group col-lg-12">
                            <label>员工工资合计</label>
                            <input type="text" class="form-control" name="wage" placeholder="请输入员工工资合计" >
                        </div>

                        <div class="form-group col-lg-12">
                            <label>员工社保合计</label>
                            <input type="text" class="form-control" name="insur" placeholder="请输入员工社保合计">
                        </div>
                        <div class="form-group col-lg-12">
                            <label>备注信息</label>
                            <textarea class="form-control" name="remark"></textarea>
                        </div>
                        <?php if($userInfo["jurisdiction"] == 2): ?><div class="form-group col-lg-12">
                                <label>所属公司</label>
                                <select class="form-control selectpicker show-tick" title="选择支出所属公司" data-size="8" name="company_id" >
                                    <?php if(is_array($companys)): $i = 0; $__LIST__ = $companys;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$company): $mod = ($i % 2 );++$i;?><option value="<?php echo ($company["company_id"]); ?>"><?php echo ($company["smallname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div><?php endif; ?>
                        <div class="form-group col-lg-12">
                            <button type="button" class="btn btn-primary btn-block" id="add-record" attr-m="home" attr-c="wage" attr-a="checkAdd">新增记录</button>
                        </div>

                    </form>
                </div>


            </div>
            <div class="modal-footer">
                <div class="text-center"><text class="text-warning"><small>付出不一定有收获，努力了就值得了</small></text></div>
                <!--<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close fa-fw"></i>&nbsp;取消修改</button>-->
                <!--<button type="button" class="btn btn-primary" id="add-record" attr-m="home" attr-c="staff" attr-a="checkEdit"><i class="fa fa-check fa-fw"></i>&nbsp;立即保存</button>-->
            </div>
        </div>
    </div>
</div>


    <div class="modal fade" id="editInfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="data-dismiss"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">编辑工资记录</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="baoju-edit-form">
                        <!-- text input -->
                        <div class="form-group col-lg-12">
                            <label>所属年月</label>
                            <input type="text" class="form-control sandbox-container-spacile" name="gmt_wage" id="edit-gmt_wage" placeholder="请选择发放时间">
                        </div>
                        <div class="form-group col-lg-12">
                            <label>员工数</label>
                            <input type="text" class="form-control " name="number" id="edit-number" placeholder="请输入员工人数" >
                        </div>
                        <div class="form-group col-lg-12">
                            <label>员工工资合计</label>
                            <input type="text" class="form-control" name="wage" id="edit-wage" placeholder="请输入员工工资合计" >
                        </div>

                        <div class="form-group col-lg-12">
                            <label>员工社保合计</label>
                            <input type="text" class="form-control" name="insur" id="edit-insur" placeholder="请输入员工社保合计">
                        </div>
                        <div class="form-group col-lg-12">
                            <label>备注信息</label>
                            <textarea class="form-control" name="remark" id="edit-remark"></textarea>
                        </div>

                        <div class="form-group col-lg-12">
                            <input type="hidden"  name="wage_id" id="edit-wage_id">
                            <button type="button" class="btn btn-primary btn-block" id="edit-record" attr-m="home" attr-c="wage" attr-a="checkEditValue">修改信息</button>
                        </div>

                    </form>
                </div>


            </div>
            <div class="modal-footer">
                <div class="text-center"><text class="text-warning"><small>付出不一定有收获，努力了就值得了</small></text></div>
                <!--<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close fa-fw"></i>&nbsp;取消修改</button>-->
                <!--<button type="button" class="btn btn-primary" id="add-record" attr-m="home" attr-c="staff" attr-a="checkEdit"><i class="fa fa-check fa-fw"></i>&nbsp;立即保存</button>-->
            </div>
        </div>
    </div>
</div>




  <!-- /.content-wrapper -->
  <!--<footer class="main-footer">-->
    <!--<div class="pull-right hidden-xs">-->
        <!--<b>Version</b> 2.4.0-->
    <!--</div>-->
    <!--<strong>Copyright &copy; 2014-2016 <a href="https://adminlte.io">Almsaeed Studio</a>.</strong> All rights-->
    <!--reserved.-->
<!--</footer>-->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
        <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
        <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        <!-- Home tab content -->
        <div class="tab-pane" id="control-sidebar-home-tab">
            <h3 class="control-sidebar-heading">Recent Activity</h3>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                            <p>Will be 23 on April 24th</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-user bg-yellow"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                            <p>New phone +1(800)555-1234</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                            <p>nora@example.com</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-file-code-o bg-green"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                            <p>Execution time 5 seconds</p>
                        </div>
                    </a>
                </li>
            </ul>
            <!-- /.control-sidebar-menu -->

            <h3 class="control-sidebar-heading">Tasks Progress</h3>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Custom Template Design
                            <span class="label label-danger pull-right">70%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Update Resume
                            <span class="label label-success pull-right">95%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Laravel Integration
                            <span class="label label-warning pull-right">50%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Back End Framework
                            <span class="label label-primary pull-right">68%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                        </div>
                    </a>
                </li>
            </ul>
            <!-- /.control-sidebar-menu -->

        </div>
        <!-- /.tab-pane -->
        <!-- Stats tab content -->
        <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
        <!-- /.tab-pane -->
        <!-- Settings tab content -->
        <div class="tab-pane" id="control-sidebar-settings-tab">
            <form method="post">
                <h3 class="control-sidebar-heading">General Settings</h3>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Report panel usage
                        <input type="checkbox" class="pull-right" checked>
                    </label>

                    <p>
                        Some information about this general settings option
                    </p>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Allow mail redirect
                        <input type="checkbox" class="pull-right" checked>
                    </label>

                    <p>
                        Other sets of options are available
                    </p>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Expose author name in posts
                        <input type="checkbox" class="pull-right" checked>
                    </label>

                    <p>
                        Allow the user to show his name in blog posts
                    </p>
                </div>
                <!-- /.form-group -->

                <h3 class="control-sidebar-heading">Chat Settings</h3>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Show me as online
                        <input type="checkbox" class="pull-right" checked>
                    </label>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Turn off notifications
                        <input type="checkbox" class="pull-right">
                    </label>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Delete chat history
                        <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
                    </label>
                </div>
                <!-- /.form-group -->
            </form>
        </div>
        <!-- /.tab-pane -->
    </div>
</aside>
<!-- /.control-sidebar -->
<!-- Add the sidebar's background. This div must be placed
     immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="Public/AdminLTE/bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="Public/AdminLTE/bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="Public/AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="Public/AdminLTE/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="Public/AdminLTE/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="Public/AdminLTE/bower_components/datatables-responsive/dataTables.responsive.js"></script>
<!-- Morris.js charts -->
<script src="Public/AdminLTE/bower_components/raphael/raphael.min.js"></script>
<script src="Public/AdminLTE/bower_components/morris.js/morris.min.js"></script>
<!-- Sparkline -->
<script src="Public/AdminLTE/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="Public/AdminLTE/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="Public/AdminLTE/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="Public/AdminLTE/bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="Public/AdminLTE/bower_components/moment/min/moment.min.js"></script>
<script src="Public/AdminLTE/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="Public/AdminLTE/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="Public/Plugin/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js"></script>
<!--datetimepicker-->
<script src="Public/AdminLTE/bower_components/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script src="Public/AdminLTE/bower_components/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="Public/AdminLTE/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Slimscroll -->
<script src="Public/AdminLTE/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="Public/AdminLTE/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="Public/AdminLTE/dist/js/adminlte.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="Public/AdminLTE/dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="Public/Plugin/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="Public/Plugin/bootstrap-select/js/i18n/defaults-zh_CN.min.js"></script>

<script src="Public/Plugin/bootstrap-fileinput/js/fileinput.js"></script>
<script src="Public/Plugin/bootstrap-fileinput/js/locales/zh.js"></script>

<script src="Public/Dialog/layer/layer.js"></script>
<script src="Public/Dialog/dialog.js"></script>

<script src="Public/AdminLTE/dist/js/demo.js"></script>
<script src="Public/Home/js/common.js"></script>
<script>
    $(document).ready(function() {
        $('#example1').DataTable({
            language: {
                "sProcessing": "处理中...",
                "sLengthMenu": "显示 _MENU_ 项结果",
                "sZeroRecords": "没有匹配结果",
                "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                "sInfoPostFix": "",
                "sSearch": "搜索:",
                "sUrl": "",
                "sEmptyTable": "表中数据为空",
                "sLoadingRecords": "载入中...",
                "sInfoThousands": ",",
                "oPaginate": {
                    "sFirst": "首页",
                    "sPrevious": "上页",
                    "sNext": "下页",
                    "sLast": "末页"
                },
                "oAria": {
                    "sSortAscending": ": 以升序排列此列",
                    "sSortDescending": ": 以降序排列此列"
                }

            },
            /*responsive: true,    //自适应
            paging: true,       //分页
            searching:true,    //搜索
            ordering:true,     //排序
            info:true,         //信息
            lengthChange: true,
            autoWidth   : false,*/

            responsive: true,    //自适应
            paging: false,       //分页
            searching:false,    //搜索
            ordering:false,     //排序
            info:false,         //信息
            lengthChange: true,
            autoWidth   : false,

        });
    });
</script>
</body>
</html>