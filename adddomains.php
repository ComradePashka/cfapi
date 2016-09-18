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
		$q = "select * from zones where `zonename` = '".$d."'";
		$res = $db->query($q) or die($db->error()." error in query ".$q);
		if($res->num_rows > 0 ){
			echo "<tr><td width='80%' >".$d."</td><td align='center' ><span class='label bg-red'>Duplicate</span></td</tr>";
		} else {
			writeDefaults2zone($db, $d, $dus['id'], 
				array('A'=>$dus['defaultA'], 'WWW'=>$dus['defaultWWW'],  'MX'=>$dus['defaultMX'],
				 'WCARD'=>$dus['defaultWCARD'], 'CNAME'=>$dus['defaultCNAME'], 'NS'=>$dus['defaultNS']));	
			echo "<tr><td width='80%' >".$d."</td><td align='center' ><span class='label bg-green'>Added</span></td</tr>";
		}
	}
	echo "</table>";
}
?>
