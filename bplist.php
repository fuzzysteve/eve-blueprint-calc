<?php
require_once('db.inc.php');


$pricetype='redis';
require_once($pricetype.'price.php');

if (array_key_exists('mpe',$_COOKIE) && is_numeric($_COOKIE['mpe']))
{
$mpe=$_COOKIE['mpe'];
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
   if (preg_match("/^(.+?)\t(\d+?)?\t(.+?)\tBlueprint\t(Yes|No)\t(\-?\d+?)\t(\-?\d+?)\t?(\d+?)?$/",trim($entry),$matches))
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
    }

}











?>
<html>
<head>
<title></title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link href="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  <script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function()
    {
        var oTable = $("#blueprints").dataTable({
           "bPaginate": false,
            "bFilter": false,
            "bInfo": false,
            "bAutoWidth": false,
});
    }
);
</script>
</head>
<body>
<table border=1 id="blueprints" class="tablesorter">
<thead>
<tr><th>id</th><th>Name</th><th>Quantity</th><th>ME</th><th>PE</th><th>Copy?</th><th>Margin(ish)</th></tr>
</thead>
<tbody>
<?

$pricesql="select sum(quantity*price) `totalprice` from (select typeid,name,round(sum(quantity)+(sum(perfect)*(0.25-(0.05*:pe))*max(base))) quantity from( select typeid,name,round(if(:me>=0,greatest(0,sum(quantity))+(greatest(0,sum(quantity))*((wastefactor/(:me+1))/100)),greatest(0,sum(quantity))+(greatest(0,sum(quantity))*(wastefactor/100)*(1-:me)))) quantity,1 base,greatest(0,sum(quantity)) perfect from (   select invTypes.typeid typeid,invTypes.typeName name,quantity   from invTypes,invTypeMaterials   where invTypeMaterials.materialTypeID=invTypes.typeID    and invTypeMaterials.TypeID=:typeid   union   select invTypes.typeid typeid,invTypes.typeName name,          invTypeMaterials.quantity*r.quantity*-1 quantity   from invTypes,invTypeMaterials,ramTypeRequirements r,invBlueprintTypes bt   where invTypeMaterials.materialTypeID=invTypes.typeID    and invTypeMaterials.TypeID =r.requiredTypeID    and r.typeID = bt.blueprintTypeID    and r.activityID = 1 and bt.productTypeID=:typeid and r.recycle=1 ) t join invBlueprintTypes on (invBlueprintTypes.productTypeID=:typeid) group by typeid,name union SELECT t.typeID typeid,t.typeName tn, r.quantity * r.damagePerJob quantity,0 base,r.quantity * r.damagePerJob perfect FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID and r.activityID = 1 and bt.productTypeID=:typeid and g.categoryID != 16 and t.groupID = g.groupID) outside group by typeid,name) bom join evesupport.sellprices on (bom.typeid=sellprices.typeid) where region=10000002";
$pricestmt = $dbh->prepare($pricesql);
$detailsql="select portionSize from invTypes where typeid=:typeid";
$detailstmt = $dbh->prepare($detailsql);

foreach (array_keys($inventory) as $blueprint ){
list($typeid,$copy,$me,$pe)=explode("/",$blueprint);


$pricestmt->execute(array(":typeid"=>$productlookup[$typeid],":me"=>$me,":pe"=>$mpe));
$detailstmt->execute(array(":typeid"=>$productlookup[$typeid]));
$pricerow = $pricestmt->fetchObject();
$detailrow = $detailstmt->fetchObject();
list($itemprice,$itempricebuy)=returnprice($productlookup[$typeid]);


echo "<tr><td>".$typeid."</td><td><a href=\"//www.fuzzwork.co.uk/blueprints/".$productlookup[$typeid]."/$me/$pe\" target='_blank'>".$typenamelookup[$typeid]."</a></td><td>".$inventory[$blueprint]."</td><td>$me</td><td>$pe</td><td>$copy</td><td>\n";


echo ($detailrow->portionSize*$itemprice)-($pricerow->totalprice);

echo "</td></tr>\n";
}



?>

</tbody>
<tfoot>
</tfoot>
</table>

<?php include('/home/web/fuzzwork/analytics.php'); ?>

</body>
</html>

