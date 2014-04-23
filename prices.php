<?php
$newstructure = json_decode($_REQUEST["prices"], true);
$currentstructure = json_decode($_COOKIE["prices"],true);

if(is_array($currentstructure)) {
	$prices	= array_replace_recursive($currentstructure, $newstructure);
} else {
	$prices = $newstructure;
}

setcookie('prices', json_encode($prices), time()+31536000);

echo 'Personal prices updated';
