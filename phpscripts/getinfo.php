<?php

include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/Logger.php";

include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";
include dirname(__FILE__)."/CloudFlare/Zone.php";

$log = new FileLogger("/var/log/cfapi.log");

use Cloudflare\Zone\Dns;

$db = new Db() or die($db->error()."\n");
$rows = $db->select("select id, cfname, cfkey from users;") or die($db->error()."\n");


foreach($rows as $index => $userarray ) {
	$client = new Cloudflare\Api($userarray['cfname'], $userarray['cfkey']);
	if($client  === false){ print $client->error(); exit; }
	$zone = new Cloudflare\Zone($client);
	if($zone  === false){ print $dns->error(); exit; }
	$res = $zone->zones();
	$resarr = get_object_vars($res);
	$result = $resarr['result'];
	foreach($result as $i => $r) {
		$zonearr = get_object_vars($r);
		$q = "INSERT INTO `zones` (`id`, `userid`, `zonename`) VALUES ('".$zonearr['id']."', '".$userarray['id']."', '".$zonearr['name']."')";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
	}
}
?>
