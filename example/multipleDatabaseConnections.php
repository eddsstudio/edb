<?php 
include('edb.class.php');

$db_data = array('resource.com','user','pa$$','dbname');
$db_data2 = array('resource.com','user','pa$$','dbname2');

$db = new edb($db_data);

$db2 = new edb($db_data2);



$data1 = $db->q("select * from users limit 3");

$data2 = $db->q("select * from users limit 5,3");

$data3 = $db->q("select * from users limit 9,3");

$data4 = $db2->s("Set names utf8");

$data5 = $db2->q("select * from users");


?>