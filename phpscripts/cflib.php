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
		$zonesArray[$zonearr['id']]['ns'] = $nameservers;
		$zonesArray[$zonearr['id']]['status'] = $zonearr['status'];
		$zonesArray[$zonearr['id']]['test'] = getZoneData($dns, $zonearr['id']);
	}
	return $zonesArray;	
}

// Get dns data for zone $id
function getZoneData($d, $id){
	$res = $d->list_records($id);
	$resarr = get_object_vars($res);
print_r($resarr);
//	$zoneData['a'] = $a;
//	$zoneData['www'] = $www;
//	$zoneData['wcard'] = $wcard;
//	$zoneData['cname'] = $cname;
//	return $zoneData;
	return 1;
}

?>
