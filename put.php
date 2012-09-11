<?php

$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

#$memcache->set('key', $tmp_object, false, 10) or die ("Failed to save data at the server");
#echo "Store data in the cache (data will expire in 10 seconds)<br/>\n";

$memcache->set('price-trit',3.35) or die ("Failed to save data at the server");
$memcache->set('price-pyro',4.17) or die ("Failed to save data at the server");
$memcache->set('price-iso',58.16) or die ("Failed to save data at the server");
$memcache->set('price-nocxium',439.50) or die ("Failed to save data at the server");
$memcache->set('price-mexallon',29.78) or die ("Failed to save data at the server");
$memcache->set('price-megacyte',2805) or die ("Failed to save data at the server");
$memcache->set('price-zydrine',777) or die ("Failed to save data at the server");


?>

