<?php
	$cfname = "dimitry.lukin@gmail.com";
	include dirname(__FILE__)."/phpscripts/Db.php";	
	$db = new Db() or die($db->error()."\n");
	$rows = $db->select("select cfkey, id from users where cfname = '".$cfname."'") or die($db->error()."\n");
	$key = $rows[0]['cfkey'];
	$id = $rows[0]['id'];

/*
	Server-side processing
	https://datatables.net/manual/server-side

	handling as in simple example
	https://datatables.net/examples/data_sources/server_side.html
*/

//	get system db config instead if copy settings
	$config = parse_ini_file(dirname(__FILE__)."/phpscripts/config.ini"); 
	$sql_details = array(
	    'user' => $config['dbuser'],
	    'pass' => $config['dbpass'],
	    'db'   => $config['dbname'],
	    'host' => 'localhost'
	);
	$table = 'zones';
	$primaryKey = 'id';
	$columns = array(
	    array(
		'db' => 'id',
		'dt' => 0,
	        'formatter' => function( $d, $row ) {
		    return "
		<button class='btn btn-default btn-sm' data-toggle='modal' data-target='#".$d."'>Zone:".$row['zonename']."</button>
		<div id='".$d."' class='modal fade' role='dialog' aria-hidden='true'>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'><br>printZone(db, r['id'], r['zonename'])</div></div>
		</div>";
	        }
	    ),
	    array( 'db' => 'zonename',  'dt' => 1,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '@' and type = 'ns' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
	        }
	    ),
	    array( 'db' => 'sync',  'dt' => 2,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '@' and type = 'a' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
	        }
	    ),
	    array( 'db' => 'status',  'dt' => 3,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = 'www' and type = 'a' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
	        }
	    ),
	    array( 'db' => 'id',  'dt' => 4,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '*' and type = 'a' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
	        }
	    ),
	    array( 'db' => 'id',  'dt' => 5,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '@' and type = 'cname' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
	        }
	    ),
	    array( 'db' => 'status',  'dt' => 6,
	        'formatter' => function( $d, $row ) use ($db) {
		  return (($row['sync'] == 1) ? "<span class='label bg-green'>Synced</span>" : "<span class='label bg-red'>NotSynced</span>") .
			"<br><span class='label bg-blue'>".$row['status']."</span>";
	        }
	    )		
//	    array( 'db' => 'status', 'dt' => 6 ),
	);
	$whereAll = 'userid = ' . $id;
//	datatables Helper
	require( 'phpscripts/ssp.class.php' ); 
//	use compex() instead of simple() due to user filtering purpose
	echo json_encode(
	    SSP::complex ( $_GET, $sql_details, $table, $primaryKey, $columns, null, $whereAll )
	);

/* 
//	old code

	$rows = $db->select("select id, zonename, sync, status from zones where userid = ".$id) or die($db->error()."\n");
	foreach($rows as $i => $r) {

		echo "<tr>";
		echo "<td>
		<button class='btn btn-default btn-sm' 
			data-toggle='modal' 
			data-target='#".$r['id']."'>".$r['zonename']."
		</button><div id='".$r['id']."' 
			class='modal fade'  
			role='dialog' 
			aria-hidden='true'>  
		<div class='modal-dialog modal-lg'> 
			<div class='modal-content'><br>".printZone($db, $r['id'], $r['zonename'])."</div></div>
		</div></td>";
                $q = "select data from records where name = '@' and type = 'ns' and zoneid = '".$r['id']."'";
                $a = $db->select($q);
                echo "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
		$q = "select data from records where name = '@' and type = 'a' and zoneid = '".$r['id']."'";
		$a = $db->select($q);
		echo "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
		$q = "select data from records where name = 'www' and type = 'a' and zoneid = '".$r['id']."'";
		$a = $db->select($q);
		echo "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
		$q = "select data from records where name = '*' and type = 'a' and zoneid = '".$r['id']."'";
		$a = $db->select($q);
		echo "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
		$q = "select data from records where name = '@' and type = 'cname' and zoneid = '".$r['id']."'";
		$a = $db->select($q);
		echo "<td>".(isset($a[0]['data']) ? $a[0]['data'] : "NONE" )."</td>";
		echo "<td>";
		if($r['sync'] == 1 ){echo "<span class='label bg-green'>Synced</span>";
		} else {echo "<span class='label bg-red'>NotSynced</span>";}
		echo "<br><span class='label bg-blue'>".$r['status']."</span>";
		echo "</td>";
		echo "</tr>";
	}
*/
	
?>
