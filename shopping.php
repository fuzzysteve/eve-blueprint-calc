<?php

$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

require_once('db.inc.php');
$ignoreprice=0;

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
header('Location: index.php');
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

if (array_key_exists('mpe',$_COOKIE) && is_numeric($_COOKIE['mpe']))
{
$mpe=$_COOKIE['mpe'];
}
if (array_key_exists('industry',$_COOKIE) && is_numeric($_COOKIE['industry']))
{
$ind=$_COOKIE['industry'];
}


if (array_key_exists('me',$_GET) && is_numeric($_GET['me']))
{
$me=$_GET['me'];
}
if (array_key_exists('pe',$_GET) && is_numeric($_GET['pe']))
{
$pe=$_GET['pe'];
}

if (array_key_exists('setcookie',$_GET) && is_numeric($_GET['setcookie']))
{
setcookie('industry',$ind,time()+31536000);
setcookie('mpe',$mpe,time()+31536000);
}


$pricepos='';
if (array_key_exists('pricepos',$_COOKIE))
{
    $coords=json_decode($_COOKIE['pricepos'],True);
    $top=$coords[0]['coordTop'];
    $left=$coords[0]['coordLeft'];
    $pwidth=substr($coords[0]['width'],0,-2);
    if (is_numeric($top)&& is_numeric($left)&& is_numeric($pwidth))
    {
        $pricepos="left:".$left."px;top:".$top."px;width:".$pwidth."px;";
    }
}


?>
<html>
<head>
<title>BP Costs -<? echo $itemname ?></title>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  <script src="/blueprints/format.js"></script>

<script type="text/javascript">
function runmenumbers()
{
    me=parseInt(document.getElementById("me").value);
    pe=parseInt(document.getElementById("pe").value);
    runs=parseInt(document.getElementById("runs").value);
    if (me<0)
    {
    wasteage=(waste/100)*(1-me);
    }
    else
    {
    wasteage=(waste/(me+1))/100;
    }
    total=0;
    etotal=0;
    perfecttotal=0;
    runtotal=0;
    for (type in typeid)
    {
        perfect=parseInt(document.getElementById(typeid[type] + "-perfect").innerHTML);
        document.getElementById(typeid[type] + "-bp").innerHTML=addCommas(Math.round(perfect+(perfect*wasteage)));
        document.getElementById(typeid[type] + "-you").innerHTML=addCommas(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe)))));
        document.getElementById(typeid[type] + "-cost").innerHTML=addIskCommas(Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100);
        document.getElementById(typeid[type] + "-perfectcost").innerHTML=addIskCommas(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100);
        document.getElementById(typeid[type] + "-number").innerHTML=addCommas(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*runs);
        document.getElementById(typeid[type] + "-runcost").innerHTML=addIskCommas(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*runs*100)/100);
        total=total+Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100;
        perfecttotal=perfecttotal+Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100;
        runtotal=runtotal+Math.round(perfect*runs*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100;
    }

    for (type in typeide)
    {
        number=parseInt(document.getElementById(typeide[type] + "-extranumperfect").innerHTML);
        if (document.getElementById(typeide[type] + "-perfect"))
        {
            number=(number+(number*(0.25-(0.05*pe))));
        }
        document.getElementById(typeide[type] + "-extranum").innerHTML=addCommas(number);
        document.getElementById(typeide[type] + "-extracost").innerHTML=addIskCommas(Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100);
        etotal=etotal+Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100;
    }

    document.getElementById("<? echo $itemid;?>-cost").innerHTML=addIskCommas(Math.round(parseFloat(document.getElementById("<? echo $itemid;?>-jitaprice").innerHTML)*<? echo $portionsize;?>*100)/100);;
    document.getElementById("etotal").innerHTML=addIskCommas(Math.round(etotal*100)/100);
    document.getElementById("basictotal").innerHTML=addIskCommas(Math.round(total*100)/100);
    document.getElementById("perfecttotal").innerHTML=addIskCommas(Math.round(perfecttotal*100)/100);
    document.getElementById("runtotal").innerHTML=addIskCommas(Math.round(runtotal*100)/100);
    document.getElementById("overalltotal").innerHTML=addIskCommas(Math.round((total+etotal)*100)/100);
    document.getElementById("sellruntotal").innerHTML=addIskCommas(Math.round(((parseFloat(document.getElementById("<? echo $itemid;?>-jitaprice").innerHTML)*<? echo $portionsize;?>*runs))*100)/100);
}



function toggleprice() 
{
    $('td.priceedit').toggleClass('hidden')
    $('a.priceedit').toggleClass('hidden')
    $('td.jitaprice').toggleClass('hidden')
}

function updateprice(type)
{
    document.getElementById(type+"-jitaprice").innerHTML=parseFloat(document.getElementById(type+"-priceedit").value);
    runmenumbers();
}

function saveprice()
{
    var price = {};
    for (type in typetotal)
    {
       temp1=typetotal[type];
       temp2=parseFloat(document.getElementById(typetotal[type]+"-jitaprice").innerHTML);
       temp3=temp1+":"+temp2
       price[temp1] = temp2;
    }
    var stringPrice=JSON.stringify(price);
    var ajaxRequest;  // The variable that makes Ajax possible!

        try{
                // Opera 8.0+, Firefox, Safari
                ajaxRequest = new XMLHttpRequest();
        } catch (e){
                // Internet Explorer Browsers
                try{
                        ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                        try{
                                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                        } catch (e){
                                // Something went wrong
                                alert("Your browser broke!");
                                return false;
                        }
                }
        }
   var priceurl="http://www.fuzzwork.co.uk/blueprints/prices.php";
   var params="prices="+stringPrice;
   ajaxRequest.open("POST",priceurl,true);
   ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   ajaxRequest.setRequestHeader("Content-length", params.length);
   ajaxRequest.setRequestHeader("Connection", "close");
   ajaxRequest.onreadystatechange = function() {//Call a function when the state changes.
        if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                alert(ajaxRequest.responseText);
        }
   }
   ajaxRequest.send(params);


}


function setme(menumber)
{
  document.getElementById("me").value=menumber;
  runmenumbers();
}

</script>
<script type="text/javascript">
waste=<? echo $wasteFactor; ?>;

	$(function() {
		$( "#prices" ).draggable().mouseup(function(){  
                    var coords=[];  
                    var coord = $(this).position();  
                    var item={ coordTop:  Math.floor(coord.top), coordLeft: Math.floor(coord.left),width: $("#prices").css('width') };  
                    coords.push(item);
                    createCookie("pricepos",JSON.stringify(coords),700);
                 }).resizable({stop: function(){
                    var coords=[];
                    var coord = $(this).position();
                    var item={ coordTop:  Math.floor(coord.top), coordLeft: Math.floor(coord.left),width: $("#prices").css('width') };
                    coords.push(item);
                    createCookie("pricepos",JSON.stringify(coords),700);
                 }});
	});

<?

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

$(document).ready(function() {
    $("input#blueprintname").autocomplete({ source: source });
    runmenumbers();
    runpenumbers();
});
</script>

<style>
.ui-menu .ui-menu-item a {
    display: block;
    line-height: 1;
    padding: 0.2em 0.4em;
    text-decoration: none;
}

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

</style>

</head>
<body>
<div class="main">
<h1><? if (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) { echo "<a name='Main Item' onclick=\"CCPEVE.showMarketDetails(".$itemid.")\" class=\"marketlink\">$itemname</a>";} else { echo $itemname;}?></h1>
<p>Things should now be working right for extra materials and how waste is applied there. Thanks go to <a href="https://gate.eveonline.com/Profile/Lutz%20Major">Lutz Major</a>, and other people from the forum.</p>
<div id="mecalcs" class="ui-widget-content">
<label for="runs">Runs</label><input type=text value=1 id="runs" size=3 style='width:3em;margin-right:1em;margin-left:1em' onchange='runmenumbers();'><br>
<label for="me">Blueprint ME</label><input type=text value=<? echo $me; ?> id="me" size=3 style='width:3em;margin-right:1em;margin-left:1em' disabled="disabled"><br>
<label for="pe">Manufacturer PE</label><input type=text value=5<? echo $mpe; ?> id="pe" readonly=y size=1 style='width:1em;margin-right:1em;margin-left:1em' disabled="disabled"><br>
<table border=1>
<tr><th>Material</th><th class="hidden">Perfect</th><th class="hidden">With ME waste</th><th>With your production waste</th><th>Perfect Cost</th><th>Cost</th><th>Number Needed</th><th>Run cost</th></tr>
<?
$max=0;
#$sql='select invTypes.typeid typeid,invTypes.typeName name,quantity  from invTypes,invTypeMaterials where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID=?';
$sql='select typeid,name,greatest(0,sum(quantity)) quantity from (select invTypes.typeid typeid,invTypes.typeName name,quantity  from invTypes,invTypeMaterials where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID=? union select invTypes.typeid typeid,invTypes.typeName name,invTypeMaterials.quantity*r.quantity*-1 quantity from invTypes,invTypeMaterials,ramTypeRequirements r,invBlueprintTypes bt where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID =r.requiredTypeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and r.recycle=1) t group by typeid,name';
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid,$itemid));
$typeid="";
while ($row = $stmt->fetchObject()){
if ($row->quantity>0)
{
$name=$row->name;
if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='mat-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
echo "<tr><td>".$name."</td><td id='".$row->typeid."-perfect' class=\"hidden\">".$row->quantity."<td id='".$row->typeid."-bp' class=\"hidden\">&nbsp;</td><td id='".$row->typeid."-you'>&nbsp;</td><td id='".$row->typeid."-perfectcost' align=right>&nbsp;</td><td id='".$row->typeid."-cost' align=right>&nbsp;</td><td id='".$row->typeid."-number' align=right>&nbsp;</td><td id='".$row->typeid."-runcost' align=right>&nbsp;</td></tr>";
$typeid.=$row->typeid.",";
$max=max($max,$row->quantity);
}
}
$typeid=trim($typeid,",");

?>
<tr><td colspan=2>Total</td><td id=perfecttotal align=right>&nbsp;</td><td id=basictotal align=right>&nbsp;</td><td></td><td id=runtotal align=right>&nbsp;</td></tr>
<tr><td colspan=3>Total with Extra materials</td><td id=overalltotal align=right>&nbsp;</td></tr>
<tr><td colspan=3>Sell Price</td><td id="<? echo $itemid?>-cost" align=right>&nbsp;</td><td></td><td id=sellruntotal align=right>&nbsp;</td></tr>
</table>
<p>A no waste ME is: <? $nowaste=floor($max*(($wasteFactor/100)/0.5)); echo $nowaste; 
?></p>
<h2>Extra Materials</h2>
<table border=1>
<tr><th>Material</th><th>Extra materials</th><th>Extra Materials with PE</th><th>Damage/use per job</th><th>Cost</th><th>Number Needed</th><th>Run cost</th></tr>
<?
$typeide="";
$typeid2=$typeid;
$sql="SELECT t.typeName tn, r.quantity qn, r.damagePerJob dmg,t.typeID typeid FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID != 16 and t.groupID = g.groupID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
while ($row = $stmt->fetchObject()){
$name=$row->tn;
if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='extramat-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
echo "<tr><td>".$name."</td><td id='".$row->typeid."-extranumperfect'>".$row->qn."</td><td id='".$row->typeid."-extranum'></td><td id='".$row->typeid."-extradam' >".$row->dmg."</td><td id='".$row->typeid."-extracost' align=right>&nbsp;</td><td id='".$row->typeid."-extranumer' align=right>&nbsp;</td><td id='".$row->typeid."-extraruncost' align=right>&nbsp;</td></tr>\n";
$typeid2.=",".$row->typeid;
$typeide.=",".$row->typeid;
}
$typeid2=trim($typeid2,",");
$typeide=trim($typeide,",");

?>
<tr><td colspan=4>Total</td><td id="etotal"></td></tr>
</table>
<p>Extra Materials have PE waste applied, if they also exist in the main list.</p>
<h2>Skills Required</h2>
<table border=1>
<tr><th>Skill</th><th>Level</th></tr>
<?
$sql="SELECT t.typeName tn, r.quantity qn FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID = 16 and t.groupID = g.groupID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
while ($row = $stmt->fetchObject()){
echo "<tr><td>".$row->tn."</td><td>".$row->qn."</td></tr>\n";
}

?>
</table>
</div>
<div id="prices" class="ui-widget-content" style="width:50%;position:absolute;<? echo $pricepos;?>">
<h2>Prices</h2>
<p>You can drag this bit up, if you have the screen width for it</p>
<table border=1>
<?
if (array_key_exists("prices",$_COOKIE))
{
    $cookieprice=json_decode($_COOKIE["prices"],true);
}
else
{
    $cookieprice=array();
}

$sql="select typename,typeid from invTypes where typeid in ($typeid2,$itemid)";
$stmt = $dbh->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetchObject())
{
    $name=$row->typename;
    if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='price-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
    echo "<tr><td>".$name."</td><td id=\"".$row->typeid.'-jitaprice" align=right class=jitaprice>';
    if (array_key_exists($row->typeid,$cookieprice) && is_numeric($cookieprice[$row->typeid])&&!$ignoreprice)
    {  
       $price=$cookieprice[$row->typeid];
       echo $cookieprice[$row->typeid];
    }
    else
    {
        $price = $memcache->get('price-type-'.$row->typeid);

        if ($price)
        {
            echo $price;
        }
        else
        {
            $url="http://api.eve-marketdata.com/api/item_prices2.xml?char_name=steveronuken&buysell=s&type_ids=".$row->typeid;
            $pricexml=file_get_contents($url);
            $xml=new SimpleXMLElement($pricexml);
            $price= (float) $xml->result->rowset->row['price'][0];
            $price=round($price,2);
            $memcache->set('price-type-'.$row->typeid,$price,false,86400);
            echo $price;
        }
    }

    echo "</td><td class=\"priceedit hidden\"><input style=\"text-align: right;\" type=text id=\"".$row->typeid."-priceedit\" align=right value=\"$price\" onchange=\"updateprice(".$row->typeid.")\" maxlength=10></td></tr>\n";
}


?>
</table>
<a name='editprice' onclick="toggleprice()">Edit prices</a>
<a name='saveprice' onclick="saveprice()" class="priceedit hidden">|save prices</a>
<a href="" id="clearprice" class="priceedit hidden">|reset prices</a>
</div>
<script type="text/javascript">
typeid=[<? echo $typeid?>];
typeide=[<? echo $typeide?>];
typetotal=[<? echo trim(trim($typeide.",".$typeid,",").",".$itemid,",")?>];
itemid=<? echo $itemid ?>;
url="http://www.fuzzwork.co.uk/blueprints/calc.php?bpid=<? echo $itemid ?>";
linkurl="http://www.fuzzwork.co.uk/blueprints/<? echo $itemid ?>/";
</script>
<br><br>
</div>
</body>
</html>
