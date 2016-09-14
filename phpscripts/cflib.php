<?php
/*
 Description: Useful functions to work with cf API
 @author <dimitry.lukin@gmail.com>
 @version 0.201608241730
*/

include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";
include dirname(__FILE__)."/CloudFlare/Zone.php";
include dirname(__FILE__)."/Logger.php";




//Connect to cf api
function connect2CF($client_id, $client_key) {
	$client = new Cloudflare\Api($client_id, $client_key);
	if($client  === false){ print $client->error(); exit; }
	$zone = new Cloudflare\Zone($client);
	if($zone  === false){ print $dns->error(); exit; }
	$dns = new Cloudflare\Zone\Dns($client);
	if($dns  === false){ print $dns->error(); exit; }
	return array($zone, $dns);
}

// Get all zone names for client and place in array: id => array [ zonename => zonename, ns => nses, status
function getAllZones($client_id, $client_key) {
	
	list ($zone, $dns) = connect2CF($client_id, $client_key);
	
	$res = $zone->zones();
	$resarr = get_object_vars($res);
	$result = $resarr['result'];


	foreach($result as $i => $r) {
		$zonearr = get_object_vars($r);
		$zonesArray[$zonearr['id']]['zonename'] = $zonearr['name'];
		$nameservers = "";
		foreach($zonearr['name_servers'] as $j => $s ) { $nameservers = $nameservers.",".$s; }
		$zonesArray[$zonearr['id']]['id'] = $zonearr['id'];
		$zonesArray[$zonearr['id']]['ns'] = $nameservers;
		$zonesArray[$zonearr['id']]['status'] = $zonearr['status'];
		$zonesArray[$zonearr['id']]['records'] = getZoneData($dns, $zonearr['id'], $zonearr['name']);
	}
	return $zonesArray;	
}

// Get dns data from cf for zone $id
function getZoneData($d, $id, $zname){
	$zoneData = array();
	$res = $d->list_records($id);
	$resarr = get_object_vars($res);
	foreach($resarr['result'] as $i => $records){
		$record = get_object_vars($records);
		if($record['type'] == 'CNAME'){
			$zoneData['cname'] = $record['content'];
		} elseif($record['type'] == 'A' and $record['name'] == $zname ){
			$zoneData['a'] = $record['content'];
		} elseif($record['type'] == 'A' and $record['name'] == "www.".$zname){
			$zoneData['www'] = $record['content'];
		} elseif($record['type'] == 'A' and $record['name'] == "*.".$zname){
			$zoneData['wcard'] = $record['content'];
		}
	}
	return $zoneData;
}
/////////////////////////////////////////////////////
// write all info from array to db without testing
function array2db($user_id, $array, $db, $l){
	foreach($array as $i => $zone ){
		$q = "INSERT INTO `zones` ( id, userid, zonename, ns, status ) VALUES ('".$zone['id']."', 
			".$user_id.", '".$zone['zonename']."', 
			'".$zone['ns']."', '".$zone['status']."')";
		$dbres = $db->query($q) or $l->log($db->error()." with query ".$q, '[ERROR]');
		foreach($zone['records'] as $j => $record){

			$q = "INSERT INTO `records` ( zoneid, type, content ) VALUES ('".$zone['id']."', 
				'".$j."', '".$record."')";
		$dbres = $db->query($q) or $l->log($db->error()." with query ".$q, '[ERROR]');
//print($q."\n");
		}
	}
}
// sync all data from db to cf
function db2cf($email, $id, $key, $db, $l){
	// Get records from db, record by record
	$q = "SELECT zonename from zones where sync = 0 and userid = ".$id;
	$res = $db->query($q) or die($db->error()."\n");
	$zones = getAllZones($email, $key);
	print_r($zones);
	foreach($res as $index => $zone){
		$zoneName = $zone['zonename'];
	// Check if zone exists
		foreach($zones as $id => $zoneFromCF ){
			if($zoneName != $zoneFromCF['zonename']) {
				$log->log("To create ".$zoneName."\n");
	// Upload zone and records to cf
				$res = syncZone2CF($zonename, $email, $key);
						
				break;
			} else {
				$log->log("Duplicate ".$zoneName."\n");
				break;
			}
		}
		
	}
}
// Sync all db data to cf. Zones, records 
// 1. Get existing zones from cf into temp table
// 2. Get only different zones from db
// 3. create zone in cf with zone attributes from db
// 4. get zone id from cf and update zoneid field to cf id and sync fiels to true
// 5. create all needed dns records in zone
function syncAllZones2CF($userdbid, $db, $email, $key, $log){

	list ($z, $d) = connect2CF($email, $key);
	$res = $z->zones(null,null,null,1000);
	$resarr = get_object_vars($res);
	$q = "create temporary table zonestemp (name varchar(255))";
	$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
	if(count($resarr) == 0 ) {
		$q = "INSERT INTO zonestemp VALUES ('empty_array')";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
	} else {
		$result = $resarr['result'];
		foreach($result as $i => $r) {
			$zonearr = get_object_vars($r);
			$q = "INSERT INTO zonestemp VALUES ('".$zonearr['name']."')";
			$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		}
	}
	$q = "select id, zonename, ns from zones where userid = ".$userdbid." and zones.zonename not in ( select name from zonestemp )";
	$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
	foreach($dbres as $i => $zone2insert ){
		$res = $z->create($zone2insert['zonename']);
		$resarr = get_object_vars($res);
		$result = $resarr['result'];
		$created = get_object_vars($result);
		$zid = $created['id']; $status = $created['status'];
		$q = " SET `foreign_key_checks` = 0; ";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		$q = " UPDATE zones SET `sync` = true,  `id` = '".$zid."', `status` = '".$status."' 
			where `zonename` = '".$zone2insert['zonename']."';";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		$q = " UPDATE records set `zoneid` = '".$zid."' where `zoneid` = '".$zone2insert['id']."';";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		$q = " SET `foreign_key_checks` = 1;";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		$q = "SELECT content from records where `type` = 'a' and zoneid = '".$zid."'";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		foreach($dbres as $i => $record2insert ){
			$res = $d->create($zid, 'A', $zone2insert['zonename'], $record2insert['content'], 120);
		}
		$q = "SELECT content from records where `type` = 'www' and zoneid = '".$zid."'";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		foreach($dbres as $i => $record2insert ){
			$res = $d->create($zid, 'A', "www.".$zone2insert['zonename'], $record2insert['content'], 120);
		}
		$q = "SELECT content from records where `type` = 'wcard' and zoneid = '".$zid."'";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		foreach($dbres as $i => $record2insert ){
			$res = $d->create($zid, 'A', "*.".$zone2insert['zonename'], $record2insert['content'], 120);
		}
		$q = "SELECT content from records where `type` = 'cname' and zoneid = '".$zid."'";
		$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
		foreach($dbres as $i => $record2insert ){
			$res = $d->create($zid, 'CNAME', $zone2insert['zonename'], $record2insert['content'], 120);
		}

	}

}


function delAllZonesFromCF($client_id, $client_key) {
	list ($zone, $dns) = connect2CF($client_id, $client_key);
	
	$res = $zone->zones(null,null, null, 1000);
	$resarr = get_object_vars($res);
	$result = $resarr['result'];


	foreach($result as $i => $r) {
		$zonearr = get_object_vars($r);
		$res = $zone->delete_zone($zonearr['id']);
	}
	
}

function deleteZoneFromDB($db, $zonename, $log){
	
	$q = "DELETE  FROM `records` where `zoneid` = (SELECT id from `zones` where `zonename` = '".$zonename."' LIMIT 1)";
	$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
	$q = "DELETE  FROM `zones` where `zonename` = '".$zonename."'";
	$dbres = $db->query($q) or $log->log($db->error()." with query ".$q, '[ERROR]');
error_log("RESULT of removing:  ".$dbres['result']);
	return true;
}

function printZone($base, $zid, $zname){
	$q = "select data from records where name is NULL and type = 'a' and zoneid = '".$zid."'";
        $a = $base->select($q);
	$arec = $a[0]['data'];
	$q = "select data from records where name is NULL and type = 'ns' and zoneid = '".$zid."'";
        $a = $base->select($q);
	$ns = $a[0]['data'];
	$q = "select data from records where name = 'www' and type = 'a' and zoneid = '".$zid."'";
        $a = $base->select($q);
	$www = $a[0]['data'];
	$q = "select data from records where name = '*' and type = 'a' and zoneid = '".$zid."'";
        $a = $base->select($q);
	$wcard = $a[0]['data'];
	$q = "select data from records where name is NULL and type = 'cname' and zoneid = '".$zid."'";
        $a = $base->select($q);
	$cname = $a[0]['data'];
	$q = "select data from records where name is NULL and type = 'mx' and zoneid = '".$zid."'";
        $a = $base->select($q);
	$mx = $a[0]['data'];
	
	$zoneRecord = "<b>;; ".$zname." zonefile for BIND</b><br><br>
	<table cellspacing ='20' border='0'><tr><td>".$zname."</td><td>IN SOA ".$ns." adm.email.com. (</td></tr>
	<tr><td> </td><td>5858765; serial</td></tr>
	<tr><td> </td><td>28800; refresh</td></tr>
	<tr><td> </td><td>7200; retry</td></tr>
	<tr><td> </td><td>604800; expire</td></tr>
	<tr><td> </td><td>86400; minimum</td></tr>
	<tr><td> </td><td>)</td></tr>
	<tr><td> </td><td>IN NS ".$ns."</td></tr>
	<tr><td> </td><td>IN A ".$arec."</td></tr>
	<tr><td> </td><td>IN CNAME ".$cname."</td></tr>
	<tr><td> </td><td>IN MX ".$mx."</td></tr>
	<tr><td>*</td><td>IN A ".$wcard."</td></tr>
	<tr><td>www</td><td>IN A ".$www."</td></tr>
	</table>";
	

	return $zoneRecord;
}

?>
