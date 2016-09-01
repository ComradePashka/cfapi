<?php
/*
 Descr: Script copy data from db to cf
 @author dimitry.lukin@gmail.com
 @version 0.201608282303
*/

$email = "dimitry.lukin@gmail.com";

/*include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";
include dirname(__FILE__)."/CloudFlare/Zone.php";
include dirname(__FILE__)."/Logger.php"; */ 

require_once dirname(__FILE__)."/cflib.php";


$log = new FileLogger("/var/log/cfapi.log");


$db = new Db() or die($db->error()."\n");
$rows = $db->select("select cfkey, id from users where cfname = '".$email."'") or die($db->error()."\n");

$key = $rows[0]['cfkey'];
$id = $rows[0]['id'];


// Get all zone records from cf to array
$res = syncAllZones2CF($id, $db, $email, $key, $log);

?>
