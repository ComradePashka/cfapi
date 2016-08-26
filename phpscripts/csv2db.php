<?php
/*
 Descr: Script read data from csv file and load to db. 
 CSV format:
 optional first line is headers
 zonename;nsservers;arecord;wwwrecord;wildcardrecord;cnamerecord
 next lines contens the data, if there are few subrecords, devide it by comma
 example:
 example.com;ns0.example.com,ns1.example.com;1.2.3.4;1.2.3.5;1.2.3.6;test.com
 @author dimitry.lukin@gmail.com
 @version 0.201608251122
*/

$defaultNS = "ns.default.ex";
$defaultA = "1.1.1.1";
$defaultWWW = "1.1.1.1";
$defaultWCARD = "1.1.1.1";
$defaultCNAME = "test.test";

$cfname = "dimitry.lukin@gmail.com";
$quotedCfname = "'".$cfname."'";
$fname = dirname(__FILE__)."/domains.csv";

include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/Logger.php";
require_once dirname(__FILE__)."/cflib.php";

$log = new FileLogger("/var/log/cfapi.log");


$db = new Db() or die($db->error()."\n");
$rows = $db->select("select cfkey, id from users where cfname = ".$quotedCfname) or die($db->error()."\n");

$key = $rows[0]['cfkey'];
$id = $rows[0]['id'];

$handle = fopen($fname, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
	$data[] = str_getcsv($line,";");
    }

    fclose($handle);
} else {
    die("Error! Can not open ".$fname."!\n");
} 

foreach($data as $i => $record){
	if( $record[0] == "zonename" ) continue;
	$q = "SELECT `zonename` from `zones` where `zonename` = '".$record[0]."'";
	$res = $db->query($q);
	if($res->num_rows == 0 ){
	        if (isset($record[1]) and $record[1] != "" ) { $NS = $record[1]; } else { $NS = $defaultNS; };
	        if (isset($record[2]) and $record[2] != "" ) { $A = $record[2]; } else { $A = $defaultA; };
	        if (isset($record[3]) and $record[3] != "" ) { $WWW = $record[3]; } else { $WWW = $defaultWWW; };
	        if (isset($record[4]) and $record[4] != "" ) { $WCARD = $record[4]; } else { $WCARD = $defaultWCARD; };
	        if (isset($record[5]) and $record[5] != "" ) { $CNAME = $record[5]; } else { $CNAME = $defaultCNAME; };

		$md5zone = md5($record[0]);
		$q = "INSERT INTO `zones` (`id`, `userid`, `zonename`, `ns`) 
			VALUES ('".$md5zone."', ".$id.", '".$record[0]."', '".$NS."')";
			$res = $db->query($q); if (!$res) {  printf("Errormessage: %s\n", $db->error()); }
			$q = "INSERT INTO `records` (`zoneid`,`type`,`content`)
			VALUES ('".$md5zone."', 'a', '".$A."') ";
			$res = $db->query($q); if (!$res) {  printf("Errormessage: %s\n", $db->error()); }
			$q = "INSERT INTO `records` (`zoneid`,`type`,`content`)
			VALUES ('".$md5zone."', 'www', '".$WWW."') ";
			$res = $db->query($q);
			$q = "INSERT INTO `records` (`zoneid`,`type`,`content`)
			VALUES ('".$md5zone."', 'wcard', '".$WCARD."') ";
			$res = $db->query($q);
			$q = "INSERT INTO `records` (`zoneid`,`type`,`content`)
			VALUES ('".$md5zone."', 'cname', '".$CNAME."') ";
			$res = $db->query($q);

	} else {
//		$log->log("There is duplicate zone name ".$record[0],'[WARNING]');
		print("There is duplicate zone name ".$record[0]."\n");
	}
}

