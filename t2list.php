<?php
require_once('db.inc.php');


require_once(__DIR__.'/Price/Price.php');


$mpe=0;
$industry=1;

if (array_key_exists('mpe',$_COOKIE) && is_numeric($_COOKIE['mpe']))
{
$mpe=$_COOKIE['mpe'];
}
if (array_key_exists('industry',$_COOKIE) && is_numeric($_COOKIE['industry']))
{
$industry=$_COOKIE['industry'];
}


if (array_key_exists('entries',$_POST))
{
$entries=explode("\n",$_POST['entries']);
}
else
{
echo "No Entries provided";
exit;
}

$sql='select typename,invTypes.typeid,producttypeid from invTypes,invBlueprintTypes where invTypes.published=1 and invTypes.typeid=invBlueprintTypes.blueprinttypeid';

$stmt = $dbh->prepare($sql);

$stmt->execute();
$typeidlookup=array();
$typenamelookup=array();
while ($row = $stmt->fetchObject()){
$typeidlookup[$row->typename]=$row->typeid;
$typenamelookup[$row->typeid]=$row->typename;
$productlookup[$row->typeid]=$row->producttypeid;
}


$inventory=array();

foreach ($entries as $entry)
{
   $entry=str_replace(",","",$entry);
   if (preg_match("/^(.+?)\t(\d+?)?\t(.+?)\tBlueprint\t(Yes|No)\t(\-?\d+?)\t(\-?\d+?)\t(\d+?)$/",trim($entry),$matches))
   {
       if(isset($typeidlookup[$matches[1]]))
       {
           $quantity=1;
           if (is_numeric($matches[2]))
           {
               $quantity=$matches[2];
           }

           if(isset($inventory[$typeidlookup[$matches[1]]."/".$matches[4].":".$matches[7]."/".$matches[5]."/".$matches[6]]))
           {
               $inventory[$typeidlookup[$matches[1]]."/".$matches[4].":".$matches[7]."/".$matches[5]."/".$matches[6]]+=$quantity;
           }
           else
           {
               $inventory[$typeidlookup[$matches[1]]."/".$matches[4].":".$matches[7]."/".$matches[5]."/".$matches[6]]=$quantity;
           }
       }
    } else
   if (preg_match("/^(.+?)\t(\d+?)?\t(.+?)\tBlueprint\t(Yes|No)\t(\-?\d+?)\t(\-?\d+?)$/",trim($entry),$matches))
   {
       if(isset($typeidlookup[$matches[1]]))
       {
           $quantity=1;
           if (is_numeric($matches[2]))
           {
               $quantity=$matches[2];
           }

           if(isset($inventory[$typeidlookup[$matches[1]]."/".$matches[4].":/".$matches[5]."/".$matches[6]]))
           {
               $inventory[$typeidlookup[$matches[1]]."/".$matches[4].":/".$matches[5]."/".$matches[6]]+=$quantity;
           }
           else
           {
               $inventory[$typeidlookup[$matches[1]]."/".$matches[4].":/".$matches[5]."/".$matches[6]]=$quantity;
           }
       }
    }


}











?>
<html>
<head>
<title>Blueprint List</title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
  <link href="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" rel="stylesheet" type="text/css"/>
  <script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function()
    {
        var oTable = $("#blueprints").dataTable({
            "bAutoWidth": false,
});
    }
);
</script>


<?php include('/home/web/fuzzwork/htdocs/bootstrap/header.php'); ?>
</head>
<body>
<?php include('/home/web/fuzzwork/htdocs/menu/menubootstrap.php'); ?>
<div class="container">


<p>Prices are purely indicative, updating once per hour. Costs for invention currently assume skills at 3, with no meta item. Prices of Tech 2 items will be a little off due to R.A.M. modules not being properly consumed (fractions are being rounded down to nothing)</p>
<table border=1 id="blueprints" class="tablesorter">
<thead>
<tr><th>id</th><th>Name</th><th>Quantity</th><th>ME</th><th>PE</th><th>Margin(ish)</th><th>Isk/hr ish</th><th>POS isk/hr</th><th>POS isk/hr - DCs</th></tr>
</thead>
<tbody>
<?

$pricesql="select sum(quantity*price) `totalprice` from (select typeid,name,round(sum(quantity)+(sum(perfect)*(0.25-(0.05*:pe))*max(base))) quantity from( select typeid,name,round(if(:me>=0,greatest(0,sum(quantity))+(greatest(0,sum(quantity))*((wastefactor/(:me+1))/100)),greatest(0,sum(quantity))+(greatest(0,sum(quantity))*(wastefactor/100)*(1-:me)))) quantity,1 base,greatest(0,sum(quantity)) perfect from (   select invTypes.typeid typeid,invTypes.typeName name,quantity   from invTypes,invTypeMaterials   where invTypeMaterials.materialTypeID=invTypes.typeID    and invTypeMaterials.TypeID=:typeid   union   select invTypes.typeid typeid,invTypes.typeName name,          invTypeMaterials.quantity*r.quantity*-1 quantity   from invTypes,invTypeMaterials,ramTypeRequirements r,invBlueprintTypes bt   where invTypeMaterials.materialTypeID=invTypes.typeID    and invTypeMaterials.TypeID =r.requiredTypeID    and r.typeID = bt.blueprintTypeID    and r.activityID = 1 and bt.productTypeID=:typeid and r.recycle=1 ) t join invBlueprintTypes on (invBlueprintTypes.productTypeID=:typeid) group by typeid,name union SELECT t.typeID typeid,t.typeName tn, r.quantity * r.damagePerJob quantity,0 base,r.quantity * r.damagePerJob perfect FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID and r.activityID = 1 and bt.productTypeID=:typeid and g.categoryID != 16 and t.groupID = g.groupID) outside group by typeid,name) bom join evesupport.sellprices on (bom.typeid=sellprices.typeid) where region=10000002";
$pricestmt = $dbh->prepare($pricesql);
$detailsql="select portionSize,productionTime,productivityModifier,chance,maxProductionLimit from invTypes join invBlueprintTypes on (invTypes.typeid=invBlueprintTypes.producttypeid) left join invMetaTypes on (invTypes.typeid=invMetaTypes.typeid) left join evesupport.inventionChance on (evesupport.inventionChance.typeid=invMetaTypes.parenttypeid) where invTypes.typeid=:typeid";
$detailstmt = $dbh->prepare($detailsql);

$datacoresql="select quantity,level,price from invTypes join invMetaTypes on invTypes.typeid=invMetaTypes.typeid join invBlueprintTypes on invBlueprintTypes.producttypeid=invMetaTypes.parentTypeID join ramTypeRequirements on (blueprintTypeID=ramTypeRequirements.typeID and activityid=8) join invTypes it2 on (requiredtypeid=it2.typeid and it2.groupid!=716) join dgmTypeAttributes on (it2.typeid=dgmTypeAttributes.typeid and dgmTypeAttributes.attributeid=182) join evesupport.inventionSkills on (coalesce(valueInt,valueFloat)=skill) join evesupport.sellprices on ( requiredtypeid=evesupport.sellprices.typeid and region=10000002) where invTypes.typeid=:typeid";

$datacorestmt=$dbh->prepare($datacoresql);

$encryption=3;

$inventionsql="select blueprinttypeid from invMetaTypes join invBlueprintTypes on (invMetaTypes.typeid=invBlueprintTypes.producttypeid)  where parenttypeid=:parenttypeid and metagroupid=2";
$inventionstmt=$dbh->prepare($inventionsql);


foreach (array_keys($inventory) as $blueprint ){
list($parenttypeid,$copy,$me,$pe)=explode("/",$blueprint);
$me=-4;
$pe=-4;
$inventionstmt->execute(array(":parenttypeid"=>$productlookup[$parenttypeid]));
while ($inventionrow=$inventionstmt->fetchObject())
{
$typeid=$inventionrow->blueprinttypeid;
$pricestmt->execute(array(":typeid"=>$productlookup[$typeid],":me"=>$me,":pe"=>$mpe));
$detailstmt->execute(array(":typeid"=>$productlookup[$typeid]));
$pricerow = $pricestmt->fetchObject();
$detailrow = $detailstmt->fetchObject();
$inventionprice=0;


list($itemprice,$itempricebuy)=returnprice($productlookup[$typeid]);
if ($pe<0)
{
$productiontime=($detailrow->productionTime*(1-(($detailrow->productivityModifier/$detailrow->productionTime)*($pe-1))));
}
else
{
$productiontime=($detailrow->productionTime*(1-(($detailrow->productivityModifier/$detailrow->productionTime)*($pe/$pe+1))));
}


$productiontime=($productiontime*(1-(0.04*$industry)))/3600;


echo "<tr><td>".$typeid."</td><td><a href=\"//www.fuzzwork.co.uk/blueprints/".$productlookup[$typeid]."/$me/$pe\" target='_blank'>".$typenamelookup[$typeid]."</a></td><td>".$inventory[$blueprint]."</td><td>$me</td><td>$pe</td><td>\n";


echo round(($detailrow->portionSize*$itemprice)-($pricerow->totalprice),2);
echo "</td><td>";
echo round((($detailrow->portionSize*$itemprice)-($pricerow->totalprice))/$productiontime,2);
echo "</td><td>";
echo round((($detailrow->portionSize*$itemprice)-($pricerow->totalprice))/($productiontime*.75),2);
echo "</td><td>";
if ($me==-4)
{
    $datacorestmt->execute(array(":typeid"=>$productlookup[$typeid]));

    $datacorerow=$datacorestmt->fetchObject();
    $price=$datacorerow->price*$datacorerow->quantity;
    $level1=$datacorerow->level;
    $datacorerow=$datacorestmt->fetchObject();
    $price+=$datacorerow->price*$datacorerow->quantity;
    $level2=$datacorerow->level;
    $chance=min(($detailrow->chance*(1+(0.01*$encryption)) * (1 + (($level1+$level2)*0.02))),1);
    $inventionprice=$price/$chance;
    echo round(((($detailrow->portionSize*$itemprice)-($pricerow->totalprice))-($inventionprice/($detailrow->maxProductionLimit/10)))/($productiontime*.75),2);
}
else
{
echo "N/A";
}


echo "</td></tr>\n";
}
}


?>
</tbody>
<tfoot>
</tfoot>
</table>


</div>
<?php include('/home/web/fuzzwork/htdocs/bootstrap/footer.php'); ?>



</body>
</html>

