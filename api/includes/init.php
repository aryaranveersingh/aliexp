<?php
header('Content-Type:application/json');
include 'dbconfig.php';

/*** include the registry class ***/

include __SITE_PATH . '/controller/' . 'registry.class.php';

/*** include the router class ***/
include __SITE_PATH . '/controller/' . 'router.class.php';

/*** include the database controller class ***/
include __SITE_PATH . '/controller/' . 'pdo_controller.php';


global $registry;
/*** a new registry object ***/
$registry = new Registry();

$registry -> db = new dbConnect();
/*** load the router ***///
$registry -> route = new requestRouter($registry);
?>