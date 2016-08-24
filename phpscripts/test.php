<?php

include dirname(__FILE__)."/CloudFlare/Api.php";
include dirname(__FILE__)."/CloudFlare/Zone/Dns.php";

use Cloudflare\Zone\Dns;

// Create a connection to the Cloudflare API which you can
// then pass into other services, e.g. DNS, later on
$client = new Cloudflare\Api('dimitry.lukin@gmail.com', 'e373091e2d730a6473d1b0014859defaafb39');

//echo $client;

// Create a new DNS record

$dns = new Cloudflare\Zone\Dns($client);
//print $dns->{'result'};

$res = $dns->create('428141799e1ba104c771b55a5c6516fe', 'A', 'name.com', '127.0.0.1', 120);
//print $res->{'result'};
?>
