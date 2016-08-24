<?php
/*
 Description: Useful functions to work with cf API
 @author <dimitry.lukin@gmail.com>
 @version 0.201608241730
*/
// Get all zone names for client and place in array: id => array [ zonename => zonename, ns => nses, status
function getAllZones($client_id, $client_key) {
	$client = new Cloudflare\Api($client_id, $client_key);
	if($client  === false){ print $client->error(); exit; }
	$zone = new Cloudflare\Zone($client);
	if($zone  === false){ print $dns->error(); exit; }
	$dns = new Cloudflare\Zone\Dns($client);
	if($dns  === false){ print $dns->error(); exit; }
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
// write all info from array to db without testing
function array2db($user_id, $array, $db, $l){
	foreach($array as $i => $zone ){
		$q = "INSERT INTO `zones` ( id, userid, zonename, ns, status ) VALUES ('".$zone['id']."', 
			".$user_id.", '".$zone['zonename']."', 
			'".$zone['ns']."', '".$zone['status']."')";
//print($q."\n");
		$dbres = $db->query($q) or $l->log($db->error()." with query ".$q, '[ERROR]');
		foreach($zone['records'] as $j => $record){

			$q = "INSERT INTO `records` ( zoneid, type, content ) VALUES ('".$zone['id']."', 
				'".$j."', '".$record."')";
		$dbres = $db->query($q) or $l->log($db->error()." with query ".$q, '[ERROR]');
//print($q."\n");
		}
	}
}

?>
