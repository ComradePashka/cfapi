<?php

require_once dirname(__FILE__)."/phpscripts/cflib.php";

$db = new Db() or die($db->error()."\n");

$cfname = "dimitry.lukin@gmail.com";
if(isset($_POST['domains'])){
	$res = "";
	$dus = array();
	$dus = getUserData($cfname);

	$domArray = preg_split ('/$\R?^/m', $_POST['domains']);
	echo "<table width='100%'>";
	foreach($domArray as $i=>$d){
		$q = "select id from zones where `zonename` = '".$d."'";
		$res = $db->query($q) or die($db->error()." error in query ".$q);
		$row = $res->fetch_assoc();
		if($res->num_rows == 0 ){
			echo "<tr><td width='80%' >".$d."</td><td align='center' ><span class='label bg-red'>Not found</span></td</tr>";
		} else {
			switch (trim($_POST['recordType'])) {
			    case "A Record":
				$type = "a";
				break;
			    case "Cname":
			        $type = "cname";
			        break;
			    case "MX Record":
			        $type = "mx";
			        break;
			    case "Txt Record":
			        $type = "txt";
			        break;
			}
			$q = "INSERT INTO records (`zoneid`,`name`,`type`,`data`) 
				VALUES ('".$row['id']."', '".$_POST['dnshost']."', '".$type."', '".$_POST['dnsvalue']."')
				ON DUPLICATE KEY UPDATE `data` = '".$_POST['dnshost']."'";
			$res = $db->query($q) or die($db->error()." error in query ".$q);
			echo "<tr><td width='80%' >".$d."</td><td align='center' ><span class='label bg-green'>Modified</span></td</tr>";
		}
	}
	echo "</table>";
}
?>
