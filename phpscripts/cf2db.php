<?php
/*
 Descr: Script gets data from cf and merge with db data. 
 Author: dimitry.lukin@gmail.com
 Revision: 201608241722
*/

$cfname = "dimitry.lukin@gmail.com";
$quotedCfname = "'".$cfname."'";

include dirname(__FILE__)."/Db.php";
include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";
include dirname(__FILE__)."/CloudFlare/Zone.php";
include dirname(__FILE__)."/Logger.php";
require_once dirname(__FILE__)."/cflib.php";

$log = new FileLogger("/var/log/cfapi.log");

use Cloudflare\Zone\Dns;

$db = new Db() or die($db->error()."\n");
$rows = $db->select("select cfkey, id from users where cfname = ".$quotedCfname) or die($db->error()."\n");

$key = $rows[0]['cfkey'];
$id = $rows[0]['id'];


// Get all zone records from cf
$zones = getAllZones($cfname, $key);

print_r($zones);

?>
