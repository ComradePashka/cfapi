<?php
// for testing
$cfname = "dimitry.lukin@gmail.com";
$quotedCfname = "'".$cfname."'";

$zone2delete = "piton.ru";

include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/Logger.php";

include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";
include dirname(__FILE__)."/CloudFlare/Zone.php";

$log = new FileLogger("/var/log/cfapi.log");

use Cloudflare\Zone\Dns;


$db = new Db() or die($db->error()."\n");

//Get api key from db
$rows = $db->select("select cfkey from users where cfname = ".$quotedCfname) or die($db->error()."\n");
$key = $rows[0]['cfkey'];



$client = new Cloudflare\Api($cfname, $key);
if($client  === false){ print $client->error(); exit; }


$zone = new Cloudflare\Zone($client);
if($zone  === false){ print $dns->error(); exit; }
$res = $zone->delete_zone($zone2delete);
$resarr = get_object_vars($res);
$result = $resarr['result'];
$reserr = $resarr['error'];
//foreach($result as $i => $r) {

//}
 
print_r($result);
print_r($resarr);
print("finish\n");


?>
