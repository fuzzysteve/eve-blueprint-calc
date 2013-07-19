<?php
header('Content-Type: text/xml');

require_once('../db.inc.php');


$bpid=$_GET['bpid'];
$sql='select typename,typeid,portionSize from invTypes where typeid=?';
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
exit;
}


$sql='select productionTime,wasteFactor,productivityModifier,researchProductivityTime,researchMaterialTime from invBlueprintTypes where productTypeID=?';
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
$row = $stmt->fetchObject();
$wasteFactor=$row->wasteFactor;
$productiontime=$row->productionTime;
$productionmodifier=$row->productivityModifier;
$researchProductivityTime=$row->researchProductivityTime;
$researchMaterialTime=$row->researchMaterialTime;
$blueprintpe=0;
$industry=0;

if (array_key_exists('mpe',$_GET) && is_numeric($_GET['mpe']))
{
$pe=$_GET['mpe'];
}
if (array_key_exists('me',$_GET) && is_numeric($_GET['me']))
{
$me=$_GET['me'];
}
if (array_key_exists('bpe',$_GET) && is_numeric($_GET['bpe']))
{
$blueprintpe=$_GET['bpe'];
}
if (array_key_exists('industry',$_GET) && is_numeric($_GET['industry']))
{
$industry=$_GET['industry'];
}

echo "<?xml version='1.0' encoding='UTF-8'?>";

if ($blueprintpe<0)
{
$time=$productiontime*(1-(($productionmodifier/$productiontime)*($blueprintpe-1)));
}
else
{
$time=$productiontime*(1-(($productionmodifier/$productiontime)*($blueprintpe/(1+$blueprintpe))));
}

$time=$time * (1 - (.04 * $industry));

?>

<blueprint id="<? echo $itemid; ?>" name="<? echo $itemname; ?>" productiontime="<? echo $productiontime; ?>" productionmodifier="<? echo $productionmodifier; ?>" yourtime="<? echo $time; ?>" >
<?
if ($me<0)
{
    $wasteage=($wasteFactor/100)*(1-$me);
}
else
{
    $wasteage=($wasteFactor/($me+1))/100;
}

$typeid=array();
$typeamount=array();
$typeactual=array();
$typename=array();

$sql='select typeid,name,greatest(0,sum(quantity)) quantity from (select invTypes.typeid typeid,invTypes.typeName name,quantity  from invTypes,invTypeMaterials where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID=? union select invTypes.typeid typeid,invTypes.typeName name,invTypeMaterials.quantity*r.quantity*-1 quantity from invTypes,invTypeMaterials,ramTypeRequirements r,invBlueprintTypes bt where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID =r.requiredTypeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and r.recycle=1) t group by typeid,name';
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid,$itemid));
while ($row = $stmt->fetchObject()){
if ($row->quantity>0)
{
$name=$row->name;
$typeid[$row->typeid]=1;
$typeamount[$row->typeid]=$row->quantity;
$typeactual[$row->typeid]=round($row->quantity+($row->quantity*$wasteage)+($row->quantity*(0.25-(0.05*$pe))));
$typename[$row->typeid]=$name;
}
}
?>
<?
$typeide="";
$typeid2=$typeid;
$sql="SELECT t.typeName tn, r.quantity qn, r.damagePerJob dmg,t.typeID typeid,r.recycle recycle FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID != 16 and t.groupID = g.groupID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
while ($row = $stmt->fetchObject()){
$name=$row->tn;
if (array_key_exists($row->typeid,$typeid))
{
$actual=round($row->qn+($row->qn*(0.25-(0.05*$pe))));
$typeamount[$row->typeid]+=$row->qn;
$typeactual[$row->typeid]+=$actual;
}
else
{
$actual=$row->qn;
$typeamount[$row->typeid]=$row->qn;
$typeactual[$row->typeid]=$actual;
$typename[$row->typeid]=$name;
$typeid[$row->typeid]=1;
}

}


echo "<totalmaterials>\n";
foreach ($typeid as $key=>$vaule)
{
echo '<'.str_replace(" ","_",$typename[$key]).' id="'.$key.'">'.$typeactual[$key].'</'.str_replace(" ","_",$typename[$key]).">\n";
}
echo "</totalmaterials>\n";
echo "</blueprint>";
?>
