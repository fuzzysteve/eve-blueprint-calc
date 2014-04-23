<?php
if(is_numeric($_GET['typeid']) && is_numeric($_GET['me']) && is_numeric($_GET['pe'])) {
	$currentstructure=json_decode($_COOKIE["blueprints"],true);
	$newstructure[$_GET['typeid']]['me']=$_GET['me'];
	$newstructure[$_GET['typeid']]['pe']=$_GET['pe'];
	if(is_array($currentstructure)) {
		$blueprints=array_replace_recursive($currentstructure,$newstructure);
	} else {
		$blueprints=$newstructure;
	}
	setcookie('blueprints',json_encode($blueprints),time()+31536000);
	print "Blueprint Saved";
} else {
	print "error occured";
}
