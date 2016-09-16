<?php
require_once dirname(__FILE__)."/phpscripts/cflib.php";
if(isset($_POST['domains'])){
	$res = "";
	$domArray = preg_split ('/$\R?^/m', $_POST['domains']);
	$db = new Db();
	echo "<table width='100%'>";
	foreach($domArray as $i=>$d){
		$q = "select * from zones where `zonename` = '".$d."'";
		$res = $db->query($q) or die($db->error()." error in query ".$q);
		if($res->num_rows > 0 ) {
			$q = "delete from zones where `zonename` = '".$d."'";
			$res = $db->query($q) or die($db->error()." error in query ".$q);
			echo "<tr><td width='80%' >".$d."</td><td align='center' ><span class='label bg-green'>Deleted</span></td</tr>";
		} else {
			echo "<tr><td width='80%' >".$d."</td><td align='center' ><span class='label bg-red'>Not found</span></td</tr>";
		}
	}
	echo "</table>";
}
?>
