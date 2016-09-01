<?php

require_once dirname(__FILE__)."/cflib.php";

// for testing
$email = "dimitry.lukin@gmail.com";


$db = new Db() or die($db->error()."\n");

//Get api key from db
$rows = $db->select("select cfkey from users where cfname = '".$email."'") or die($db->error()."\n");
$key = $rows[0]['cfkey'];


$res = delAllZonesFromCF($email, $key);

