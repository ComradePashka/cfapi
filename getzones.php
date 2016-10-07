<?php
	$cfname = "dimitry.lukin@gmail.com";
	require_once dirname(__FILE__)."/phpscripts/cflib.php";
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

//	get system db config instead of copying settings
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
	        'formatter' => function( $d, $row ) use ($db) {
		    return "
		<button class='btn btn-default btn-sm' data-toggle='modal' data-target='#".$d."'>".$row['zonename']."</button>
		<div id='".$d."' class='modal fade' role='dialog' aria-hidden='true'>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'><br>" . printZone($db, $d, $row['zonename']) . "</div></div>
		</div>";
	        }
	    ),
	    array( 'db' => 'zonename',  'dt' => 1,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '@' and type = 'ns' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return isset($a[0]['data']) ? $a[0]['data'] : "NONE";
	        }
	    ),
	    array( 'db' => 'sync',  'dt' => 2,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '@' and type = 'a' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return isset($a[0]['data']) ? $a[0]['data'] : "NONE";
	        }
	    ),
	    array( 'db' => 'id',  'dt' => 3,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = 'www' and type = 'a' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return isset($a[0]['data']) ? $a[0]['data'] : "NONE";
	        }
	    ),
	    array( 'db' => 'id',  'dt' => 4,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '*' and type = 'a' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return isset($a[0]['data']) ? $a[0]['data'] : "NONE";
	        }
	    ),
	    array( 'db' => 'id',  'dt' => 5,
	        'formatter' => function( $d, $row ) use ($db) {
		  $q = "select data from records where name = '@' and type = 'cname' and zoneid = '".$row['id']."'";
		  $a = $db->select($q);
		  return isset($a[0]['data']) ? $a[0]['data'] : "NONE";
	        }
	    ),
	    array( 'db' => 'status',  'dt' => 6,
	        'formatter' => function( $d, $row ) {
		  return (($row['sync'] == 1) ? "<span class='label bg-green'>Synced</span>" : "<span class='label bg-red'>NotSynced</span>") .
			"<br><span class='label bg-blue'>".$row['status']."</span>";
	        }
	    )		
	);

//	datatables Helper
//	use compex() instead of simple() due to user filtering purpose
	$whereAll = 'userid = ' . $id;
	require( 'phpscripts/ssp.class.php' ); 
	echo json_encode(
	    SSP::complex ( $_GET, $sql_details, $table, $primaryKey, $columns, null, $whereAll )
	);
	
?>
