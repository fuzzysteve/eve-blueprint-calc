<?php
$pricetype='redis';
#$pricetype='memcache';
#$pricetype='marketdata';

require_once($pricetype.'price.php');

require_once('db.inc.php');
$ignoreprice=0;
if (array_key_exists('clearprice',$_GET) && $_GET['clearprice'])
{
setcookie('prices',"",time() - 3600);
$ignoreprice=1;
}

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
$sql="select typename,typeid,portionSize from $database.invTypes where lower(typename)=lower(?)";
}
else
{
$bpid=$_GET['bpid'];
$sql="select typename,typeid,portionSize from $database.invTypes where typeid=?";
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
header('Location: index.php?error=1');
exit;
}


$sql="select productionTime,wasteFactor,productivityModifier,researchProductivityTime,researchMaterialTime,maxProductionLimit from $database.invBlueprintTypes where productTypeID=?";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
$row = $stmt->fetchObject();
$wasteFactor=$row->wasteFactor;
$productiontime=$row->productionTime;
$productionmodifier=$row->productivityModifier;
$researchProductivityTime=$row->researchProductivityTime;
$researchMaterialTime=$row->researchMaterialTime;
$maxruns=$row->maxProductionLimit/10;

if (array_key_exists('mpe',$_COOKIE) && is_numeric($_COOKIE['mpe']))
{
$mpe=$_COOKIE['mpe'];
}
if (array_key_exists('industry',$_COOKIE) && is_numeric($_COOKIE['industry']))
{
$ind=$_COOKIE['industry'];
}

$inventionchecksql="select metaGroupID,parentTypeID from $database.invMetaTypes where typeid=?";

$stmt = $dbh->prepare($inventionchecksql);
$stmt->execute(array($itemid));
if ($row = $stmt->fetchObject()){
$metaGroupID=$row->metaGroupID;
$baseid=$row->parentTypeID;
}
else
{
$metaGroupID=0;
$baseid=0;
}

if ($metaGroupID==2)
{
$me=-4;
$pe=-4;
}


if (array_key_exists('mpe',$_GET) && is_numeric($_GET['mpe']))
{
$mpe=$_GET['mpe'];
}
if (array_key_exists('me',$_GET) && is_numeric($_GET['me']))
{
$me=$_GET['me'];
}
if (array_key_exists('pe',$_GET) && is_numeric($_GET['pe']))
{
$pe=$_GET['pe'];
}
if (array_key_exists('ind',$_GET) && is_numeric($_GET['ind']))
{
$ind=$_GET['ind'];
}

if (array_key_exists('setcookie',$_GET) && is_numeric($_GET['setcookie']))
{
setcookie('industry',$ind,time()+31536000);
setcookie('mpe',$mpe,time()+31536000);
}


$pricepos='';
if (array_key_exists('pricepos',$_COOKIE))
{
    $coords=json_decode(stripslashes($_COOKIE['pricepos']),True);
    $top=$coords[0]['coordTop'];
    $left=$coords[0]['coordLeft'];
    $pwidth=substr($coords[0]['width'],0,-2);
    if (is_numeric($top)&& is_numeric($left)&& is_numeric($pwidth))
    {
        $pricepos="left:".$left."px;top:".$top."px;";
    }
}


?>
<html>
<head>
<title>BP Costs -<? echo $itemname ?></title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link href="/blueprints/main.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
  <link href="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" rel="stylesheet" type="text/css"/>
  <script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="/blueprints/dataTables.currencySort.js"></script>
  <script type="text/javascript" src="/blueprints/ColVis.min.js"></script>

  <script src="/blueprints/format.js"></script>

<script type="text/javascript">

itemid=<? if (isset($itemid)){ echo $itemid;} else {echo '0'; } ?>;
portionsize=<? if (isset($portionsize)){ echo $portionsize;} else {echo '0'; } ?>;
productiontime=<? if (isset($productiontime)){ echo $productiontime;} else {echo '0'; } ?>;
productionmodifier=<? if (isset($productionmodifier)){ echo $productionmodifier;} else {echo '0'; } ?>;
waste=<? if (isset($wasteFactor)){ echo $wasteFactor;} else {echo '0'; } ?>;
mpe=<? if (isset($mpe)){ echo $mpe;} else {echo '0'; } ?>;
me=<? if (isset($me)){ echo $me;} else {echo '0'; } ?>;
pe=<? if (isset($pe)){ echo $pe;} else {echo '0'; } ?>;
industry=<? if (isset($ind)){ echo $ind;} else {echo '0'; } ?>;
metallurgy=<? if (isset($metallurgy)){ echo $metallurgy;} else {echo '0'; } ?>;
research=<? if (isset($research)){ echo $research;} else {echo '0'; } ?>;



</script>
  <script type="text/javascript" src="/blueprints/items.js"></script>
  <script type="text/javascript" src="/blueprints/blueprint.js"></script>

<style>
.ui-menu .ui-menu-item a {
    display: block;
    line-height: 1;
    padding: 0.2em 0.4em;
    text-decoration: none;
}
div.jqDrag {cursor: move;}
.jitaprice {
 display: table-cell;
}
.priceedit {
 display: table-cell;
}
.hidden {
 display: none;
}

.marketlink {
color:blue;
text-decoration:underline;
}

.togglebuy { width:1em}

.dataTable { width:auto !important; clear:none !important; margin:0 !important;}

</style>

<?php include('/home/web/fuzzwork/htdocs/bootstrap/header.php'); ?>
</head>
<body>
<?php include('/home/web/fuzzwork/htdocs/menu/menubootstrap.php'); ?>
<div class="container">
<div class="main">
<h1 class="title"><? if (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) { echo "<a name='Main Item' onclick=\"CCPEVE.showMarketDetails(".$itemid.")\" class=\"marketlink\">$itemname <img src='//image.eveonline.com/InventoryType/".$itemid."_64.png' class='icon64'></a>";} else { echo $itemname." <img src='//image.eveonline.com/InventoryType/".$itemid."_64.png' class='icon64'>";}?></h1>
<p>Things should now be working right for extra materials and how waste is applied there. Thanks go to <a href="https://gate.eveonline.com/Profile/Lutz%20Major">Lutz Major</a>, and other people from the forum.</p>
<a href="" id='linkme'>Link to these details</a>&nbsp;|<a href="" id='xmlme'>XML</a>&nbsp;||<a href="" id='xml2me'>Alternate Format XML (with times as well)</a>&nbsp;|<a href="" id='staticme'>Bare Tables</a>&nbsp;|<a href="" id="cookieme">Set your Industry and Production Efficiency in a cookie</a>|
<a name='savebp' onclick="saveblueprint()" class="marketlink">save blueprint</a>
<div id="mecalcs">
<label for="me">Blueprint ME</label><input type=text value=0 id="me" size=3 style='width:3em;margin-right:1em;margin-left:1em'><div id="meslider" style='width:500px;display:inline-block;height:0.5em'></div><br>
<label for="pe">Manufacturer PE</label><input type=text value=1 id="pe" readonly=y size=1 style='width:1em;margin-right:1em;margin-left:1em'><div id="peslider" style='width:100px;display:inline-block;height:0.5em'></div><br>
<table border=1 id="basematerials">
<thead>
<tr><th>Material</th><th>Perfect</th><th>With ME waste</th><th>With your production waste</th><th>Perfect Cost</th><th>Cost</th><th>Difference</th><th>Waste Eliminated at</th></tr>
</thead>
<tbody>
<?
$max=0;
$sql="select typeid,name,greatest(0,sum(quantity)) quantity from (select invTypes.typeid typeid,invTypes.typeName name,quantity  from $database.invTypes,$database.invTypeMaterials where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID=? union select invTypes.typeid typeid,invTypes.typeName name,invTypeMaterials.quantity*r.quantity*-1 quantity from $database.invTypes,$database.invTypeMaterials,$database.ramTypeRequirements r,$database.invBlueprintTypes bt where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID =r.requiredTypeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and r.recycle=1) t group by typeid,name";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid,$itemid));
$typeid="";
while ($row = $stmt->fetchObject()){
if ($row->quantity>0)
{
$name="<img src='//image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->name;
if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='mat-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
echo "<tr id='basemat-".$row->typeid."'><td>".$name."</td><td id='".$row->typeid."-perfect'>".$row->quantity."<td id='".$row->typeid."-bp'>0</td><td id='".$row->typeid."-you'>0</td><td id='".$row->typeid."-perfectcost' align=right>0</td><td id='".$row->typeid."-cost' align=right>0</td><td id='".$row->typeid."-diff' align=right>0</td><td id='".$row->typeid."-me' onclick=\"setme(".floor($row->quantity*(($wasteFactor/100)/0.5)).");\" style='color:blue;text-decoration:underline' align=right>".floor($row->quantity*(($wasteFactor/100)/0.5))."</tr>";
$typeid.=$row->typeid.",";
$max=max($max,$row->quantity);
}
}
$typeid=trim($typeid,",");

?>
</tbody>
<tfoot>
<tr><td colspan=4>Total</td><td id=perfecttotal align=right>&nbsp;</td><td id=basictotal align=right>&nbsp;</td><td id='totaldifference' align=right>&nbsp;</td></tr>
<tr><td colspan=5>Total with Extra materials</td><td id=overalltotal align=right>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td colspan=5>Sell Price</td><td id="<? echo $itemid?>-cost" align=right>&nbsp;</td><td id=profit>&nbsp;</td></tr>
<? if ($metaGroupID==2)
{?>
<tr><td colspan=5>Invention Cost Per Unit</td><td colspan=2 id="inventioncost" align=right>&nbsp;</td></tr>
<?}?>
</tfoot>
</table>
<p>A no waste ME is: <? $nowaste=floor($max*(($wasteFactor/100)/0.5)); echo $nowaste; 
?></p>
<h2>Extra Materials</h2>
<table border=1 id="extramaterials"><thead>
<tr><th>Material</th><th>Extra materials</th><th>Extra Materials with PE</th><th>Damage/use per job</th><th>Cost</th></tr></thead><tbody>
<?
$typeide="";
$typeid2=$typeid;
$sql="SELECT t.typeName tn, r.quantity qn, r.damagePerJob dmg,t.typeID typeid FROM $database.ramTypeRequirements r,$database.invTypes t,$database.invBlueprintTypes bt,$database.invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID != 16 and t.groupID = g.groupID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
while ($row = $stmt->fetchObject()){
$name="<img src='//image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->tn;
if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='extramat-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
echo "<tr id='extramat-".$row->typeid."'><td>".$name."</td><td id='".$row->typeid."-extranumperfect'>".$row->qn."</td><td id='".$row->typeid."-extranum'></td><td id='".$row->typeid."-extradam' >".$row->dmg."</td><td id='".$row->typeid."-extracost' align=right>&nbsp;</td></tr>\n";
$typeid2.=",".$row->typeid;
$typeide.=",".$row->typeid;
}
$typeid2=trim($typeid2,",");
$typeide=trim($typeide,",");

?></tbody><tfoot>
<tr><td colspan=4>Total</td><td id="etotal"></td></tr></tfoot>
</table>
<p>Extra Materials have PE waste applied, if they also exist in the main list.</p>
<h2>Skills Required</h2>
<table border=1>
<tr><th>Skill</th><th>Level</th></tr>
<?
$sql="SELECT t.typeName tn, r.quantity qn FROM $database.ramTypeRequirements r,$database.invTypes t,$database.invBlueprintTypes bt,$database.invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID = 16 and t.groupID = g.groupID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
while ($row = $stmt->fetchObject()){
echo "<tr><td>".$row->tn."</td><td>".$row->qn."</td></tr>\n";
}

?>
</table>
</div>
<div id="timecalcs">
<h2>Time Calculations:</h2>
<label for="prode">Blueprint PE</label><input type=text value=0 id="prode" size=3 style='width:3em;margin-right:1em;margin-left:1em'><div id="prodeslider" style='width:500px;display:inline-block;height:0.5em'></div><br>
<label for="ind">Manufacturer Industry</label><input type=text value=1 id="ind" readonly=y size=1 style='width:1em;margin-right:1em;margin-left:1em'><div id="indslider" style='width:100px;display:inline-block;height:0.5em'></div><br>
<table border=1>
<tr><th>Base time</th><th>Time with PE</th><th>Your time</th><th title="POS assembly arrays get a time multiplier of 0.75.">Your POS time</th><tr>
<tr><td id=basetime align=right><? echo $productiontime ?></td><td id=petime align=right>&nbsp;</td><td id=youtime align=right>&nbsp;</td><td id=youpostime align=right>&nbsp;</td></tr>
<tr><th>iskh</th><td id="peiskh" align=right>&nbsp;</td><td id=youriskh align=right>&nbsp;</td><td id=posiskh align=right>&nbsp;</td></tr>
<?php if ($metaGroupID==2){
echo '<tr title="The ISK/hr assuming you only put jobs in once a day. 3 hours =24 hours. 10 hours =24 hours. 25 hours=48 hours"><th>iskh 24H rounding</th><td id="peisk24h" align=right>&nbsp;</td><td id=yourisk24h align=right>&nbsp;</td><td id=posisk24h align=right>&nbsp;</td></tr>';
}?>
</table>
<h2>Material Efficiency Research Time</h2>
<label for="met">Metallurgy</label><input type=text value=0 id="met" size=3 style='width:3em;margin-right:1em;margin-left:1em'><div id="metslider" style='width:500px;display:inline-block;height:0.5em'></div><br>
<table border=1>
<tr><th>Base Research time</th><th>Your Time</th><th>POS Time</th></tr>
<tr><td id="basemetime"><?echo $researchMaterialTime?></td><td id=yourmetime>&nbsp</td><td id=yourmepostime>&nbsp</td></tr>
</table>
<h2>Production Efficiency Research Time</h2>
<label for="research">Research</label><input type=text value=0 id="research" size=3 style='width:3em;margin-right:1em;margin-left:1em'><div id="researchslider" style='width:500px;display:inline-block;height:0.5em'></div><br>
<table border=1>
<tr><th>Base Research time</th><th>Your Time</th><th>POS Time</th></tr>
<tr><td id="basepetime"><?echo $researchProductivityTime?></td><td id=yourpetime>&nbsp</td><td id=yourpepostime>&nbsp</td></tr>
</table>
</div>
<?
$dctype='';
if ($metaGroupID == 2)
{

$inventionsql="select invTypes.typeid,invTypes.typename,ramTypeRequirements.quantity,chance from $database.ramTypeRequirements,$database.invBlueprintTypes,$database.invTypes,evesupport.inventionChance where producttypeid=? and ramTypeRequirements.typeid=invBlueprintTypes.blueprintTypeID and activityid=8 and invTypes.typeid=requiredTypeID and groupid !=716 and inventionChance.typeid=producttypeid";
$stmt = $dbh->prepare($inventionsql);
?>
<div id="invention">
<h1><span onclick='$("#inventiondialog").dialog( "open" );' style="color: blue;text-decoration: underline;">Invention Calculator</span></h1><p><a href="//www.fuzzwork.co.uk/blueprints/inventionxml/<? echo $databasenumber ?>/<? echo $itemid ?>">xml for materials</a></p>
<label for="inventionchance">Invention chance</label><input type=text id="inventionchance" value="40" onchange='runinventionnumbers()'>%<br>
<label for="inventprofit">Remove from isk/hr</label><input type=checkbox id="inventprofit" onchange='runinventionnumbers()'><br>
<label for="inventruns">Runs per invention</label><input type=text id="inventruns" value=<? echo $maxruns; ?> disabled><input type=hidden id="baseruns" value=<? echo $maxruns; ?>>
<table border=1>
<tr><th>Invention Material Name</th><th>Invention Material Quantity</th><th>Datacore Cost</th></tr>
<?
$stmt->execute(array($baseid));
$chance=0.4;
while ($row = $stmt->fetchObject()){
    echo "<tr><td>".$row->typename."</td><td id='inventquantity-".$row->typeid."' align=;right'>".$row->quantity."</td><td id='inventcost-".$row->typeid."' align='right'>&nbsp</td></tr>\n";
    $typeid2.=",".$row->typeid;
    $dctype.=",".$row->typeid;
    $chance=$row->chance;

    if (!isset($chance))
    {
        $chance=0;
    }

}

$decryptorsql="select it2.typeid,it2.typename,coalesce(dta2.valueint,dta2.valueFloat) modifier  from invBlueprintTypes ibt join ramTypeRequirements rtr on (ibt.blueprinttypeid=rtr.typeid)join invTypes it1 on (rtr.requiredTypeID=it1.typeid and it1.groupid=716  and activityid=8) join dgmTypeAttributes dta on ( it1.typeid=dta.typeid and dta.attributeid=1115) join invTypes it2 on (it2.groupid=coalesce(dta.valueint,dta.valueFloat)) join dgmTypeAttributes dta2 on (dta2.typeid=it2.typeid and dta2.attributeid=1112) where ibt.producttypeid=?";
$stmt = $dbh->prepare($decryptorsql);
$stmt->execute(array($baseid));
while ($row = $stmt->fetchObject()){
   echo "<tr class='hidden' id='decryptorrow-".$row->modifier."'><td id='decryptorname-".$row->modifier."'>".$row->typename."</td><td>1</td><td id='decryptorcost-".$row->modifier."' align='right'>&nbsp</td><td class='hidden' id='decryptorid-".$row->modifier."'>$row->typeid</td></tr>\n";
    $typeid2.=",".$row->typeid;
}



$typeid2=trim($typeid2,",");
$dctype=trim($dctype,",");
?>
<tr><td id="displaydecryptor">No Decryptor</td><td id="displaydecryptorq">0</td><td id=displaydecryptorc align='right'></td></tr>
<tr><th colspan=2>Material cost per Successful invention</th><td id='inventtotalcost' align='right'>&nbsp</td></tr>

</table>
</div>
<script>noinvent=0;</script>
<?
}
else
{
echo "<script>noinvent=1;</script>";
}
?>

<div id="prices" style="position:absolute;<? echo $pricepos;?>">
    <div id="priceheader" class="ui-widget-header"><span id="togglepricedetail" style="float:right"><img src="/blueprints/collapse.png"></span>Prices</div>
    <div id="pricedetail">
        <div>
            <select id="priceregion" onchange='$.post("/blueprints/loadregion.php",{"region":$("#priceregion").val(),"items":allitems.join(":")},function(data) {updateprices(data);});'>
<?
$regionsql='select regionid,regionname from eve.mapRegions  where regionname not like "%-%" order by regionname';

$regionstmt = $dbh->prepare($regionsql);

$regionstmt->execute();

while ($row = $regionstmt->fetchObject()){
echo "<option value=".$row->regionid;
if ($row->regionid==10000002)
{
echo " selected";
}
echo ">".$row->regionname.'</option>';
}
?>


</select>

       </div>
       <div id="pricetable">
<table border=1>
<?
if (array_key_exists("prices",$_COOKIE))
{
    $cookieprice=json_decode($_COOKIE["prices"],true);
}
else
{
    $cookieprice=array(0);
}

$sql="select invTypes.typename,invTypes.typeid,if(blueprintTypeID,1,0) canmake from $database.invTypes left join $database.invBlueprintTypes on (invTypes.typeid= invBlueprintTypes.producttypeid) where invTypes.typeid in ($typeid2,$itemid)";
$stmt = $dbh->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetchObject())
{
    $name="<img src='//image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->typename;
    if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='price-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
    echo "<tr><td id='toggle-".$row->typeid."' class='togglebuy' title='Toggle buy/sell'>S</td><td>".$name."</td>";
    if ($row->canmake)
    {
        echo "<td><a href='/blueprints/".$row->typeid."/0/0' target='_blank'>make</a></td>";
    }
    else
    {
       echo "<td></td>";
    }

    echo "<td id=\"".$row->typeid.'-jitaprice" align=right class=jitaprice>';
    if (isset($cookieprice) && array_key_exists($row->typeid,$cookieprice) && is_numeric($cookieprice[$row->typeid])&&!$ignoreprice)
    {  
       $price=$cookieprice[$row->typeid];
       echo $cookieprice[$row->typeid];
    }
    else
    { 
        list($price,$pricebuy)=returnprice($row->typeid);
        echo $price;

    }

echo "</td><td class=\"priceedit hidden\"><input style=\"text-align: right;\" type=text id=\"".$row->typeid."-priceedit\" align=right value=\"$price\" onchange=\"updateprice(".$row->typeid.")\" maxlength=10></td><td id=\"".$row->typeid."-jitasell\" class='hidden jitasell'>$price</td><td id=\"".$row->typeid."-jitabuy\" class='hidden jitabuy'>$pricebuy</td></tr>\n";
}


?>
</table>
<a name='editprice' onclick="toggleprice()">Edit prices</a>
<a name='saveprice' onclick="saveprice()" class="priceedit hidden">|save prices</a>
<a href="" id="clearprice" class="priceedit hidden">|reset prices</a>
        </div>
</div>
</div>

<script type="text/javascript">
typeid=[<? echo $typeid?>];
dctypes=[<? echo $dctype?>];
typeide=[<? echo $typeide?>];
typetotal=[<? echo trim(trim($typeide.",".$typeid,",").",".$itemid,",")?>];
allitems=[<? echo trim(trim($typeide.",".$typeid,",").",".$itemid.",".$dctype.",".$typeid2,",")?>];
itemid=<? echo $itemid ?>;
url="//www.fuzzwork.co.uk/blueprints/calc.php?bpid=<? echo $itemid ?>";
linkurl="//www.fuzzwork.co.uk/blueprints/<? echo $databasenumber."/".$itemid ?>/";
xmlurl="//www.fuzzwork.co.uk/blueprints/xml/<? echo $itemid ?>/";
xml2url="//www.fuzzwork.co.uk/blueprints/xml2/<? echo $itemid ?>/";
staticurl="//www.fuzzwork.co.uk/blueprints/static/<? echo $itemid ?>/";
</script>
<br><br>
<div id="search" >
<form method=post action='/blueprints/calc.php' id="nextsearch">
<input type=text width=30 id="blueprintname" name='blueprintname' />
<input type=hidden name="database" value="<? echo $databasenumber ?>">
<label for="newwindow">New Window?</label>
<input type=checkbox id=newwindow value="1" onchange="if ($('#newwindow').is(':checked')){ document.getElementById('nextsearch').target='_blank';} else {  document.getElementById('nextsearch').target='_self';}">
<input type=submit value="Do calculations" />
</form>
</div>
<div id="inventiondialog" title="Invention Calculator">
<form id='calculator' name='calculator'>
<table>
<tr><th>Base</th><th>Encryption</th><th>DC 1</th><th>DC 2</th><th>MetaItem</th><th>Decryptor</th><th>Chance</th></tr>
<tr>
<td class="slidercell">
<?
if (!isset($chance))
{
$chance=0;
}
?>
<input type=radio name="basechance" value=20 id="bc20" onchange="calculateresult();" <? if ($chance=="0.2") { echo "checked"; }?>/><label for="bc20">20%</label><br/>
<input type=radio name="basechance" value=25 id="bc25" onchange="calculateresult();" <? if ($chance=="0.25") { echo "checked"; }?>/><label for="bc25">25%</label><br/>
<input type=radio name="basechance" value=30 id="bc30" onchange="calculateresult();" <? if ($chance=="0.3") { echo "checked"; }?>/><label for="bc30">30%</label><br/>
<input type=radio name="basechance" value=40 id="bc40" onchange="calculateresult();" <? if ($chance=="0.4") { echo "checked"; }?>/><label for="bc40">40%</label>
<td class="slidercell">
<input type=text disabled=true  id='encryption' class="dontcover" size=1 value=1>
<div id="slider-encryption" style="height:200px;"></div>
</td>
<td class="slidercell">
<input type=text disabled=true  id='datacore1'  class="dontcover" size=1 value=1>
<div id="slider-datacore1" style="height:200px;"></div>
</td>
<td class="slidercell">
<input type=text disabled=true  id='datacore2'  class="dontcover" size=1 value=1>
<div id="slider-datacore2" style="height:200px;"></div>
</td>
<td class="slidercell">
<input type=text disabled=true  id='metaitem'  class="dontcover" size=1 value=0>
<div id="slider-metaitem" style="height:200px;"></div>
</td>
<td class="slidercell">
<input type=radio name="decryptor" value="none" id="dec1" checked="checked" onchange="calculateresult();"/><label for="dec1">None</label><br/>
<input type=radio name="decryptor" value=0.6 id="dec2" onchange="calculateresult();"/><label for="dec2">Augmentation 0.6</label><br/>
<input type=radio name="decryptor" value=0.9 id="dec9" onchange="calculateresult();"/><label for="dec9">Optimized Augmentation 0.9</label><br/>
<input type=radio name="decryptor" value=1 id="dec3" onchange="calculateresult();"/><label for="dec3">Symmetry 1.0</label><br/>
<input type=radio name="decryptor" value=1.1 id="dec4"  onchange="calculateresult();"/><label for="dec4">Process 1.1</label><br/>
<input type=radio name="decryptor" value=1.2 id="dec5"  onchange="calculateresult();"/><label for="dec5">Accelerant 1.2</label><br/>
<input type=radio name="decryptor" value=1.5 id="dec7"  onchange="calculateresult();"/><label for="dec7">Parity 1.5</label><br/>
<input type=radio name="decryptor" value=1.8 id="dec6"  onchange="calculateresult();"/><label for="dec6">Attainment 1.8</label><br/>
<input type=radio name="decryptor" value=1.9 id="dec8"  onchange="calculateresult();"/><label for="dec8">Optimized Attainment 1.9</label><br/>
</td>
<td id="results"></td>
</tr>
</table>
</form>
        <div>
                <span class="note">Base chance is 20% for battlecruisers, battleships, Hulk</span><br />
                <span class="note">Base chance is 25% for cruisers, industrials, Mackinaw</span><br />
                <span class="note">Base chance is 30% for frigates, destroyers, Skiff, freighters</span><br />
                <span class="note">Base chance is 40% for all other inventables</span><br />
        </div>
<table>
<tr><th>Decryptor</th><th>Modifier</th><th>Runs</th><th>Final ME</th><th>Final PE</th></tr>
<tr><td>None</td><td>1</td><td>+0</td><td>-4</td><td>-4</td></tr>
<tr><td>Augmentation</td><td>0.6</td><td>+9</td><td>-6</td><td>-3</td></tr>
<tr><td>Optimized Augmentation</td><td>0.9</td><td>+7<td>-2</td><td>-4</td></tr>
<tr><td>Symmetry</td><td>1.0</td><td>+2</td><td>-3</td><td>0</td></tr>
<tr><td>Process</td><td>1.1</td><td>+0</td><td>-1</td><td>-1</td></tr>
<tr><td>Accelerant</td><td>1.2</td><td>+1</td><td>-2</td><td>1.</td></tr>
<tr><td>Parity</td><td>1.5</td><td>+3</td><td>-3</td><td>-5</td></tr>
<tr><td>Attainment</td><td>1.8</td><td>4</td><td>-5</td><td>-2</td></tr>
<tr><td>Optimized Attainment</td><td>1.9</td><td>+2</td><td>-3</td><td>-5</td></tr>
</table>
</div>
</div>
</div>

<?php include('/home/web/fuzzwork/htdocs/bootstrap/footer.php'); ?>
</body>
</html>
