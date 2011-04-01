<?php

include('edb.php');


$db = new edb('example.com','username','password','databasename');

$result = $db->db("select * from `users`limit 3");

//get result using array numbers
foreach($result as $a){
        echo $a[0].' '.$a[1].' '.$a[2].' '.$a[3].'</br>';
}

//get result by collon names
foreach($result as $a){
        echo $a['name'].' '.$a['surname'].' '.$a['email'].' '.$a['country'].'</br>';
}


//or
//my favorite :)
foreach($result as $a){
        $a = (object) $a;
        echo $a->id.' '.$a->name.' '.$a->url.' '.$a->img.'</br>';
}


//using line(); function

$result = $db->line("select * from `users` where id = '300' limit 1");
echo $result['name']; 
echo $result['surname']; 

//insert data
$db->s("INSERT INTO example (name, age) VALUES('Bobby Wallace', '15' ) ");

//update data
$db->s("UPDATE example SET age='23' WHERE age='23'");

//using one
$name = $db->one("select name from `ilike_pics` where id = '300' limit 1");

echo $name;

?>