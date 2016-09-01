<?php
// for testing
$cfname = "dimitry.lukin@gmail.com";
include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/Logger.php";

include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";
include dirname(__FILE__)."/CloudFlare/Zone.php";

$log = new FileLogger("/var/log/cfapi.log");

use Cloudflare\Zone\Dns;

$db = new Db() or die($db->error()."\n");
$rows = $db->select("select cfkey, id from users where cfname = '".$cfname."'") or die($db->error()."\n");

$key = $rows[0]['cfkey'];
$id = $rows[0]['id'];

$rows = $db->select("select zonename from zones where userid = ".$id) or die($db->error()."\n");



$client = new Cloudflare\Api($cfname, $key);
if($client  === false){ print $client->error(); exit; }
$q = "create temporary table zonestemp (name varchar(255))";
//$q = "create table zonestemp (name varchar(255))";
$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
$zone = new Cloudflare\Zone($client);
if($zone  === false){ print $dns->error(); exit; }
$res = $zone->zones();
$resarr = get_object_vars($res);
$result = $resarr['result'];
foreach($result as $i => $r) {
	$zonearr = get_object_vars($r);
	$q = "INSERT INTO zonestemp VALUES ('".$zonearr['name']."')";
	$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
}
$q = "select zonename, ns from zones where userid = ".$id." and zones.zonename not in ( select name from zonestemp )";
$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');

foreach($dbres as $i => $zone2insert ){
//	print($zone2insert['zonename']."\n");
	
	$res = $zone->create($zone2insert['zonename'], );
	$resarr = get_object_vars($res);
	$result = $resarr['result'];
	
 
//print_r($result);
	
}


?>
