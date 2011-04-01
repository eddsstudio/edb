You can add database configuration in two ways:

<?php 
include('edb.class.php');


$db_data = array('resource.com','user','pa$$','dbname');
$db = new edb($db_data);

//or

$db2 = new edb('resource.com','user','pa$$','dbname2');

?>

To access database connection You can use: $db->connection;

<?

$db = new edb($db_data);
$query = mysql_query("select * from example", $db->connection);

?>