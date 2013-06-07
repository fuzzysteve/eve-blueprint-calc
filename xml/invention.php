<?php
header('Content-Type: text/xml');

require_once('../db.inc.php');

$database='eve';
$databasenumber=0;
if ((array_key_exists('database',$_POST) &&  is_numeric($_POST['database']))|| (array_key_exists('database',$_GET) &&  is_numeric($_GET['database'])))
{

    if (array_key_exists('database',$_POST))
    {
        $dbnum=$_POST['database'];
    }
    else
    {
        $dbnum=$_GET['database'];
    }

    $sql='select id,version from evesupport.dbversions where id=?';

    $stmt = $dbh->prepare($sql);

    $stmt->execute(array($dbnum));

    while ($row = $stmt->fetchObject()){
        $databasenumber=$row->id;
        $database=$row->version;
    }

}


if (array_key_exists('blueprintname',$_POST))
{
$bpid=strtolower($_POST['blueprintname']);
$bpid=str_replace(' blueprint','',$bpid);
$sql="select typename,coalesce(invMetaTypes.parenttypeid,invTypes.typeid) typeid,portionSize from $database.invTypes left join $database.invMetaTypes on (invMetaTypes.typeid=invTypes.typeid) where lower(typename)=lower(?)";
}
else
{
$bpid=$_GET['bpid'];
$sql="select typename,coalesce(invMetaTypes.parenttypeid,invTypes.typeid) typeid,portionSize from $database.invTypes left join $database.invMetaTypes on (invMetaTypes.typeid=invTypes.typeid) where invTypes.typeid=?";
}
$stmt = $dbh->prepare($sql);
$stmt->execute(array($bpid));

if ($row = $stmt->fetchObject())
{
$itemname=$row->typename;
$itemid=$row->typeid;
$portionsize=$row->portionSize;
}
else
{
header('Location: ../index.php?error=1');
exit;
}


$inventionsql="select invTypes.typeid,invTypes.typename,ramTypeRequirements.quantity,chance,damageperjob,groupid from $database.ramTypeRequirements,$database.invBlueprintTypes,$database.invTypes,evesupport.inventionChance where producttypeid=? and ramTypeRequirements.typeid=invBlueprintTypes.blueprintTypeID and activityid=8 and invTypes.typeid=requiredTypeID and inventionChance.typeid=producttypeid";
$stmt = $dbh->prepare($inventionsql);

echo "<?xml version='1.0' encoding='UTF-8'?>";


$xml="<materials>";

$stmt->execute(array($itemid));
$chance=0.4;
while ($row = $stmt->fetchObject()){
    $xml.="<material>";
    $xml.="<typeid>$row->typeid</typeid>\n";
    $xml.="<name>$row->typename</name>\n";
    $xml.="<quantity>$row->quantity</quantity>\n";
    if ($row->groupid==716)
    {
        $xml.="<damage>0</damage>\n";
    }
    else
    {
        $xml.="<damage>$row->damageperjob</damage>\n";
    }
    $xml.="</material>";
    $chance=$row->chance;

    if (!isset($chance))
    {
        $chance=0;
    }

}
$xml.="</materials>";
echo "<blueprint id=\"$itemid\" name=\"$itemname\" basechance=\"$chance\">\n";
echo $xml;
echo "</blueprint>";
?>
