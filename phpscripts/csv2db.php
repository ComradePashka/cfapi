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
 @version 0.201609142318
*/

$cfname = "dimitry.lukin@gmail.com";
$fname = dirname(__FILE__)."/domains.csv";

include dirname(__FILE__)."/cflib.php";


$userArr = array();
$userArr = getUserData($cfname);

$db = new Db() or die($db->error()."\n");

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
	$res = $db->query($q) or die("$db->error()\n");
	if($res->num_rows == 0 ){
	        if (isset($record[1]) and $record[1] != "" ) { $NS = $record[1]; } else { $NS = $userArr['defaultNS']; };
	        if (isset($record[2]) and $record[2] != "" ) { $A = $record[2]; } else { $A = $userArr['defaultA']; };
	        if (isset($record[3]) and $record[3] != "" ) { $WWW = $record[3]; } else { $WWW = $userArr['defaultWWW']; };
	        if (isset($record[4]) and $record[4] != "" ) { $WCARD = $record[4]; } else { $WCARD = $userArr['defaultWCARD']; };
	        if (isset($record[5]) and $record[5] != "" ) { $CNAME = $record[5]; } else { $CNAME = $userArr['defaultCNAME']; };
		if (isset($record[5]) and $record[5] != "" ) { $MX = $record[6]; } else { $MX = $userArr['defaultMX']; };

		$def = array('A' => $A, 'NS' => $NS, 'WWW' => $WWW, 'WCARD' => $WCARD, 'CNAME' => $CNAME, 'MX' => $MX);
		writeDefaults2zone($db, $record[0], $userArr['id'], $def);
	} else {
		logSQL("CSV2DB", "Writing ".$record[0]." to db.", "There is a duplicate zone name ".$record[0],'[WARNING]');
	}
}

