<?php
$expires = 14400;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
header('Content-Type: application/javascript');

require_once('db.inc.php');

$sql='select typename from invBlueprintTypes,invTypes where typeid=productTypeID and invTypes.published=1 order by typename';

$stmt = $dbh->prepare($sql);

$stmt->execute();

echo "source=[";
$row = $stmt->fetchObject();
echo  '"'.$row->typename.'"';
while ($row = $stmt->fetchObject()){
echo ',"'.$row->typename.'"';
}
echo "];\n";
?>

