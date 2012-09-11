<?

$newstructure=json_decode($_POST["prices"],true);
$currentstructure=json_decode($_COOKIE["prices"],true);
if (is_array($currentstructure))
{
$prices=array_replace_recursive($currentstructure,$newstructure);
}
else
{
$prices=$newstructure;
}
setcookie('prices',json_encode($prices),time()+31536000);

#var_dump($prices);
#var_dump($newstructure);
#var_dump($currentstructure);
#var_dump(json_encode($prices,JSON_NUMERIC_CHECK));
?>
Personal prices updated
