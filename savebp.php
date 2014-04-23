<?php
if(is_numeric($_REQUEST['typeid']) && is_numeric($_REQUEST['me']) && is_numeric($_REQUEST['pe'])) {
	$currentstructure=json_decode($_COOKIE["blueprints"],true);
	$newstructure[$_REQUEST['typeid']]['me']=$_REQUEST['me'];
	$newstructure[$_REQUEST['typeid']]['pe']=$_REQUEST['pe'];
	if(is_array($currentstructure)) {
		$blueprints=array_replace_recursive($currentstructure,$newstructure);
	} else {
		$blueprints=$newstructure;
	}
	setcookie('blueprints',json_encode($blueprints),time()+31536000);
	echo 'Blueprint Saved';
} else {
	echo 'error occured';
}
