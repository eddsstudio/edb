<?php
    //example usage for insert function
    
    include('edb.class.php');
    
    $config = array('host','user','pass','db');
    
    $db = new edb($config);

	$ref = '';
	if(isset($_SERVER['HTTP_REFERER'])){
		$ref = $_SERVER['HTTP_REFERER'];
	}
    $db->insert("views",array(
        'url'=>$_SERVER['REQUEST_URI'],
        'ip'=>$_SERVER['REMOTE_ADDR'],
        'browser'=>$_SERVER['HTTP_USER_AGENT'],
        'date'=>date("Y-m-d H:i:s "),
        'ref'=>$ref
	));
    

    
?>