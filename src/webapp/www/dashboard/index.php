<?php
/**
 * This is the main wrapper script of service dashboard. 
 *
 * Uses:
 * By default display all product, env and services.
 * Display hosts services in service dashboard inside the iframe. 
 * Write custom superviosrd.php based on selection of product,env and services. 
 */
define('WEBAPP_URL', 'http://' . getenv('HTTP_HOST'));
define('DOC_ROOT', getenv('DOCUMENT_ROOT'));
define('SUPERVISORD_DASHBOARD_URL', WEBAPP_URL . '/supervisord-dashboard/');
define('DASHBOARD_URL', WEBAPP_URL. '/dashboard/');
define("TALOS_WRAPPER", '/home/prod/talos/src/wrapper/talos');
 
include_once('common.php');

$products = getAllEntity('product');
$services = getAllEntity('service');
$environments = getAllEntity('env');

?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Service Dashboard</title>
        <link type="text/css" rel="stylesheet" href="<?=SUPERVISORD_DASHBOARD_URL?>css/bootstrap.min.css"/>
        <link type="text/css" rel="stylesheet" href="<?=SUPERVISORD_DASHBOARD_URL?>css/bootstrap-responsive.min.css"/>
        <link type="text/css" rel="stylesheet" href="<?=SUPERVISORD_DASHBOARD_URL?>css/custom.css"/>
        <script type="text/javascript" src="<?=DASHBOARD_URL?>assets/js/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="<?=SUPERVISORD_DASHBOARD_URL?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?=DASHBOARD_URL?>assets/js/dashboard.js"></script>
    </head>
    <body>
        <div class="navbar navbar-fixed-top navbar-default">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" style="margin: 0; float: none;" href="#">
                      Service Dashboard
                    </a>
                </div>
            </div>
        </div> 
        
        <div class="container">
            <!-- filter section start -->
            <div class="row">
                <div class="span3">
                    <select name="product" class="dropdown" id="product">
                        <option value="">--Select Product--</option>
                        <?php
                           if(!empty($products)) {
                            foreach($products as $product) { ?>
                            <option value="<?php echo $product['name']; ?>"><?php echo ucfirst($product['name'])?></option>
                        <?php }
                            }
                        ?>
                    </select>
                </div>
                <div class="span3">
                    <select name="env" class="dropdown" id="env">
                        <option value="">--Select Environment--</option>
                        <?php
                           if(!empty($environments)) {
                            foreach($environments as $env) { ?>
                            <option value="<?php echo $env['name']; ?>"><?php echo ucfirst($env['name'])?></option>
                        <?php }
                            }
                        ?>
                    </select>
                </div>
                <div class="span3">
                    <select name="service" class="dropdown" id="service">
                        <option value="">--Select Service--</option>
                        <?php foreach($services as $service) { ?>
                            <option value="<?php echo $service['name']; ?>"><?php echo ucfirst($service['name'])?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <!-- filter section end -->
            
            <!-- iframe container start -->
                <iframe id="dashboard-iframe" src="<?=WEBAPP_URL?>/supervisord-dashboard/" width="90%" style="position: absolute; height: 100%; border: none"></iframe>
            <!-- iframe container end -->
        </div>
    </body>
</html>