<?php

$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

require_once('db.inc.php');
$ignoreprice=0;
if (array_key_exists('clearprice',$_GET) && $_GET['clearprice'])
{
setcookie('prices',"",time() - 3600);
$ignoreprice=1;
}

if (array_key_exists('blueprintname',$_POST))
{
$bpid=$_POST['blueprintname'];
$sql='select typename,typeid,portionSize from invTypes where lower(typename)=lower(?)';
}
else
{
$bpid=$_GET['bpid'];
$sql='select typename,typeid,portionSize from invTypes where typeid=?';
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
        $pricepos="left:".$left."px;top:".$top."px;width:".$pwidth."px;";
    }
}


?>
<html>
<head>
<title>BP Costs -<? echo $itemname ?></title>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link href="/blueprints/main.css" rel="stylesheet" type="text/css"/>
  <link href="/blueprints/jqModal.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  <script src="/blueprints/format.js"></script>
  <script src="/blueprints/jqModal.js"></script>

<script type="text/javascript">
pmargin=0;
inventioncost=0;
function runmenumbers()
{
    me=parseInt(document.getElementById("me").value);
    pe=parseInt(document.getElementById("pe").value);
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
    for (type in typeid)
    {
        perfect=parseInt(document.getElementById(typeid[type] + "-perfect").innerHTML);
        document.getElementById(typeid[type] + "-bp").innerHTML=addCommas(Math.round(perfect+(perfect*wasteage)));
        document.getElementById(typeid[type] + "-you").innerHTML=addCommas(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe)))));
        document.getElementById(typeid[type] + "-cost").innerHTML=addIskCommas(Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100);
        document.getElementById(typeid[type] + "-perfectcost").innerHTML=addIskCommas(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100);
        document.getElementById(typeid[type] + "-diff").innerHTML=addCommas(Math.round(((Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100)-(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100))*100)/100); 
        total=total+Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100;
        perfecttotal=perfecttotal+Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100;
        if (me>=Math.floor(perfect*((waste/100)/0.5)))
        {
           $("#"+typeid[type]+"-me").toggleClass('perfect',true);
        }
        else
        {
           $("#"+typeid[type]+"-me").toggleClass('perfect',false);
        }
    }

    for (type in typeide)
    {
        number=parseInt(document.getElementById(typeide[type] + "-extranumperfect").innerHTML);
        if (document.getElementById(typeide[type] + "-perfect"))
        {
            number=(number+(number*(0.25-(0.05*pe))));
        }
        document.getElementById(typeide[type] + "-extranum").innerHTML=addCommas(Math.round(number));
        document.getElementById(typeide[type] + "-extracost").innerHTML=addIskCommas(Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100);
        etotal=etotal+Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100;
    }

    document.getElementById("<? echo $itemid;?>-cost").innerHTML=addIskCommas(Math.round(parseFloat(document.getElementById("<? echo $itemid;?>-jitaprice").innerHTML)*<? echo $portionsize;?>*100)/100);;
    document.getElementById("etotal").innerHTML=addIskCommas(Math.round(etotal*100)/100);
    document.getElementById("basictotal").innerHTML=addIskCommas(Math.round(total*100)/100);
    document.getElementById("perfecttotal").innerHTML=addIskCommas(Math.round(perfecttotal*100)/100);
    document.getElementById("overalltotal").innerHTML=addIskCommas(Math.round((total+etotal)*100)/100);
    document.getElementById("totaldifference").innerHTML=addIskCommas(Math.round((total-perfecttotal)*100)/100);
    document.getElementById("profit").innerHTML=addIskCommas(Math.round(((parseFloat(document.getElementById("<? echo $itemid;?>-jitaprice").innerHTML)*<? echo $portionsize;?>)-(total+etotal))*100)/100);
    pmargin=Math.round(((parseFloat(document.getElementById("<? echo $itemid;?>-jitaprice").innerHTML)*<? echo $portionsize;?>)-(total+etotal))*100)/100;
    basetime=parseInt(document.getElementById("basemetime").innerHTML);
    metallurgy=parseInt(document.getElementById("met").value);
    document.getElementById("yourmetime").innerHTML=rectime(me*basetime*(1-(metallurgy*0.05)))
    document.getElementById("yourmepostime").innerHTML=rectime(me*basetime*(1-(metallurgy*0.05))*0.75)
    updatelink();
    runpenumbers();
}

function runpenumbers()
{ 
    prode=parseInt(document.getElementById("prode").value);
    productiontime=<? echo $productiontime; ?>;
    productionmodifier=<? echo $productionmodifier; ?>;
    if (prode<0)
    {
        timewaste=productiontime*(1-(productionmodifier/productiontime)*(prode-1));
    }
    else
    {
        timewaste=productiontime*(1-(productionmodifier/productiontime)*(prode/(1+prode)));
    }
    if (!noinvent)
    {
        if (document.getElementById("inventprofit").checked)
        {
            pmargin2=pmargin-(inventioncost/parseInt(document.getElementById("inventruns").value));
        }
        else
        {
            pmargin2=pmargin;
        }
    }
    else
    {
        pmargin2=pmargin;
    }

    document.getElementById("petime").innerHTML=rectime(Math.floor(timewaste));
    document.getElementById("youtime").innerHTML=rectime(Math.floor(timewaste* (1 - .04 *parseInt(document.getElementById("ind").value))));
    document.getElementById("youpostime").innerHTML=rectime(Math.floor((timewaste* (1 - .04 *parseInt(document.getElementById("ind").value)))*0.75));
    basetime=parseInt(document.getElementById("basepetime").innerHTML);
    research=parseInt(document.getElementById("research").value);
    document.getElementById("yourpetime").innerHTML=rectime(prode*basetime*(1-(research*0.05)))
    document.getElementById("yourpepostime").innerHTML=rectime(prode*basetime*(1-(research*0.05))*0.75)
    document.getElementById("peiskh").innerHTML=addIskCommas(Math.round(((3600/timewaste)*pmargin2)*100)/100);
    document.getElementById("youriskh").innerHTML=addIskCommas(Math.round(((3600/(timewaste* (1 - .04 *parseInt(document.getElementById("ind").value))))*pmargin2)*100)/100);
    document.getElementById("posiskh").innerHTML=addIskCommas(Math.round(((3600/((timewaste* (1 - .04 *parseInt(document.getElementById("ind").value)))*0.75))*pmargin2)*100)/100);
    updatelink();
}

function runinventionnumbers()
{
    if (dctypes.length>0)
    {
        inventionchance=parseFloat(document.getElementById("inventionchance").value);
        totalcost=0;
        for (dctype in dctypes) 
        {
            document.getElementById("inventcost-"+dctypes[dctype]).innerHTML=addIskCommas(Math.round((parseFloat(document.getElementById("inventquantity-"+dctypes[dctype]).innerHTML)*document.getElementById(dctypes[dctype] + "-jitaprice").innerHTML)*100)/100);
            totalcost=totalcost+Math.round((parseFloat(document.getElementById("inventquantity-"+dctypes[dctype]).innerHTML)*document.getElementById(dctypes[dctype] + "-jitaprice").innerHTML)*100)/100;
        }
       document.getElementById("inventtotalcost").innerHTML=addIskCommas(((Math.round(totalcost/(inventionchance/100))/100))*100);
       inventioncost=totalcost/(inventionchance/100);
    }
    runpenumbers();
}

function updatelink()
{
    me=parseInt(document.getElementById("me").value);
    pe=parseInt(document.getElementById("pe").value);
    prode=parseInt(document.getElementById("prode").value);
    ind=parseInt(document.getElementById("ind").value);
    urltoshow=linkurl+me+"/"+pe+"/"+prode+"/"+ind;
    urltoxml=xmlurl+me+"/"+pe;
    urltostatic=staticurl+me+"/"+pe;
    urltocookie=url+"&mpe="+pe+"&ind="+ind+"&setcookie=1";
    document.getElementById("cookieme").href=urltocookie;
    document.getElementById("linkme").href=urltoshow;
    document.getElementById("xmlme").href=urltoxml;
    document.getElementById("staticme").href=urltostatic;
    document.getElementById("clearprice").href=urltoshow+"&clearprice=1";
    document.getElementById("nextsearch").action="/blueprints/calc.php?mpe="+pe+"&ind="+ind;
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

function saveblueprint()
{
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
   var saveurl="http://www.fuzzwork.co.uk/blueprints/savebp.php";
   var params="typeid="+itemid+"&me="+parseInt(document.getElementById("me").value)+"&pe="+parseInt(document.getElementById("prode").value);
   ajaxRequest.open("GET",saveurl+"?"+params,true);
   ajaxRequest.onreadystatechange = function() {//Call a function when the state changes.
        if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                alert(ajaxRequest.responseText);
        }
   }
   ajaxRequest.send(null);


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
		$( "#peslider" ).slider({
			value:<? if (isset($mpe)){ echo $mpe;} else {echo '1'; } ?>,
			min: 0,
			max: 5,
			step: 1,
			slide: function( event, ui ) {
				$( "#pe" ).val( ui.value );
				runmenumbers()
			}
		});
		$( "#pe" ).val( $( "#peslider" ).slider( "value" ) );
	});

$(function() {
                $( "#meslider" ).slider({
			value:<? if (isset($me)){ echo $me;} else {echo '0'; } ?>,
                        min: -6,
                        max: 500,
                        step: 1,
                        slide: function( event, ui ) {
                                $( "#me" ).val( ui.value );
                                runmenumbers()
                        }
                });
                $( "#me" ).val( $( "#meslider" ).slider( "value" ) );
        });

$(function() {
                $( "#prodeslider" ).slider({
			value:<? if (isset($pe)){ echo $pe;} else {echo '0'; } ?>,
                        min: -6,
                        max: 500,
                        step: 1,
                        slide: function( event, ui ) {
                                $( "#prode" ).val( ui.value );
                                runpenumbers()
                        }
                });
                $( "#prode" ).val( $( "#prodeslider" ).slider( "value" ) );
        });

$(function() {
                $( "#indslider" ).slider({
			value:<? if (isset($ind)){ echo $ind;} else {echo '1'; } ?>,
                        min: 1,
                        max: 5,
                        step: 1,
                        slide: function( event, ui ) {
                                $( "#ind" ).val( ui.value );
                                runpenumbers()
                        }
                });
                $( "#ind" ).val( $( "#indslider" ).slider( "value" ) );
        });

$(function() {
                $( "#metslider" ).slider({
			value:<? if (isset($met)){ echo $met;} else {echo '0'; } ?>,
                        min: 0,
                        max: 5,
                        step: 1,
                        slide: function( event, ui ) {
                                $( "#met" ).val( ui.value );
                                runmenumbers()
                        }
                });
                $( "#met" ).val( $( "#metslider" ).slider( "value" ) );
        });

$(function() {
                $( "#researchslider" ).slider({
                        value:<? if (isset($research)){ echo $research;} else {echo '0'; } ?>,
                        min: 0,
                        max: 5,
                        step: 1,
                        slide: function( event, ui ) {
                                $( "#research" ).val( ui.value );
                                runpenumbers()
                        }
                });
                $( "#research" ).val( $( "#researchslider" ).slider( "value" ) );
        });


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
$(function() {
                $( "#slider-encryption" ).slider({
                        orientation: "vertical",
                        range: "min",
                        min: 1,
                        max: 5,
                        value: 1,
                        slide: function( event, ui ) {
                                $( "#encryption" ).val( ui.value );
                                calculateresult();
                        }
                });
                $( "#encryption" ).val( $( "#slider-encryption" ).slider( "value" ) );
        });
$(function() {
                $( "#slider-datacore1" ).slider({
                        orientation: "vertical",
                        range: "min",
                        min: 1,
                        max: 5,
                        value: 1,
                        slide: function( event, ui ) {
                                $( "#datacore1" ).val( ui.value );
                                calculateresult();
                        }
                });
                $( "#datacore1" ).val( $( "#slider-datacore1" ).slider( "value" ) );
        });
$(function() {
                $( "#slider-datacore2" ).slider({
                        orientation: "vertical",
                        range: "min",
                        min: 1,
                        max: 5,
                        value: 1,
                        slide: function( event, ui ) {
                                $( "#datacore2" ).val( ui.value );
                                calculateresult();
                        }
                });
                $( "#datacore2" ).val( $( "#slider-datacore2" ).slider( "value" ) );
        });
$(function() {
                $( "#slider-metaitem" ).slider({
                        orientation: "vertical",
                        range: "min",
                        min: 0,
                        max: 4,
                        value: 0,
                        slide: function( event, ui ) {
                                $( "#metaitem" ).val( ui.value );
                                calculateresult();
                        }
                });
                $( "#metaitem" ).val( $( "#slider-metaitem" ).slider( "value" ) );
        });

function calculateresult()
{
if (noinvent)
{
return;
}

basechance=$('input:radio[name=basechance]:checked').val();
encryption=parseInt(document.getElementById("encryption").value);;
datacore1=parseInt(document.getElementById("datacore1").value);;
datacore2=parseInt(document.getElementById("datacore2").value);
metaitem=parseInt(document.getElementById("metaitem").value);
decryptor=$('input:radio[name=decryptor]:checked').val();
InventionChance = Math.min((basechance * (1 + (0.01 * encryption)) * (1 + ((datacore1+datacore2) * (0.1 / (5 -metaitem))))*decryptor),100);
document.getElementById("results").innerHTML=Math.floor(InventionChance*100)/100+"%";
document.getElementById("inventionchance").value=Math.floor(InventionChance*100)/100;
runinventionnumbers();
}


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
    runinventionnumbers();
    $('#inventiondialog').jqm();
    calculateresult();
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
<h1 class="title"><? if (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) { echo "<a name='Main Item' onclick=\"CCPEVE.showMarketDetails(".$itemid.")\" class=\"marketlink\">$itemname <img src='http://image.eveonline.com/InventoryType/".$itemid."_64.png' class='icon64'></a>";} else { echo $itemname." <img src='http://image.eveonline.com/InventoryType/".$itemid."_64.png' class='icon64'>";}?></h1>
<p>Things should now be working right for extra materials and how waste is applied there. Thanks go to <a href="https://gate.eveonline.com/Profile/Lutz%20Major">Lutz Major</a>, and other people from the forum.</p>
<a href="" id='linkme'>Link to these details</a>&nbsp;|<a href="" id='xmlme'>XML</a>&nbsp;|<a href="" id='staticme'>Bare Tables</a>&nbsp;|<a href="" id="cookieme">Set your Industry and Production Efficiency in a cookie</a>|
<a name='savebp' onclick="saveblueprint()" class="marketlink">save blueprint</a>
<div id="mecalcs">
<label for="me">Blueprint ME</label><input type=text value=0 id="me" size=3 style='width:3em;margin-right:1em;margin-left:1em'><div id="meslider" style='width:500px;display:inline-block;height:0.5em'></div><br>
<label for="pe">Manufacturer PE</label><input type=text value=1 id="pe" readonly=y size=1 style='width:1em;margin-right:1em;margin-left:1em'><div id="peslider" style='width:100px;display:inline-block;height:0.5em'></div><br>
<table border=1>
<tr><th>Material</th><th>Perfect</th><th>With ME waste</th><th>With your production waste</th><th>Perfect Cost</th><th>Cost</th><th>Difference</th><th>Waste Eliminated at</th></tr>
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
$name="<img src='http://image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->name;
if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='mat-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
echo "<tr><td>".$name."</td><td id='".$row->typeid."-perfect'>".$row->quantity."<td id='".$row->typeid."-bp'>&nbsp;</td><td id='".$row->typeid."-you'>&nbsp;</td><td id='".$row->typeid."-perfectcost' align=right>&nbsp;</td><td id='".$row->typeid."-cost' align=right>&nbsp;</td><td id='".$row->typeid."-diff' align=right>&nbsp;</td><td id='".$row->typeid."-me' onclick=\"setme(".floor($row->quantity*(($wasteFactor/100)/0.5)).");\" style='color:blue;text-decoration:underline' align=right>".floor($row->quantity*(($wasteFactor/100)/0.5))."</tr>";
$typeid.=$row->typeid.",";
$max=max($max,$row->quantity);
}
}
$typeid=trim($typeid,",");

?>
<tr><td colspan=4>Total</td><td id=perfecttotal align=right>&nbsp;</td><td id=basictotal align=right>&nbsp;</td><td id='totaldifference' align=right>&nbsp;</td></tr>
<tr><td colspan=5>Total with Extra materials</td><td id=overalltotal align=right>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td colspan=5>Sell Price</td><td id="<? echo $itemid?>-cost" align=right>&nbsp;</td><td id=profit>&nbsp;</td></tr>
</table>
<p>A no waste ME is: <? $nowaste=floor($max*(($wasteFactor/100)/0.5)); echo $nowaste; 
?></p>
<h2>Extra Materials</h2>
<table border=1>
<tr><th>Material</th><th>Extra materials</th><th>Extra Materials with PE</th><th>Damage/use per job</th><th>Cost</th></tr>
<?
$typeide="";
$typeid2=$typeid;
$sql="SELECT t.typeName tn, r.quantity qn, r.damagePerJob dmg,t.typeID typeid FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID != 16 and t.groupID = g.groupID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));
while ($row = $stmt->fetchObject()){
$name="<img src='http://image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->tn;
if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='extramat-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
echo "<tr><td>".$name."</td><td id='".$row->typeid."-extranumperfect'>".$row->qn."</td><td id='".$row->typeid."-extranum'></td><td id='".$row->typeid."-extradam' >".$row->dmg."</td><td id='".$row->typeid."-extracost' align=right>&nbsp;</td></tr>\n";
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
<div id="timecalcs">
<h2>Time Calculations:</h2>
<label for="prode">Blueprint PE</label><input type=text value=0 id="prode" size=3 style='width:3em;margin-right:1em;margin-left:1em'><div id="prodeslider" style='width:500px;display:inline-block;height:0.5em'></div><br>
<label for="ind">Manufacturer Industry</label><input type=text value=1 id="ind" readonly=y size=1 style='width:1em;margin-right:1em;margin-left:1em'><div id="indslider" style='width:100px;display:inline-block;height:0.5em'></div><br>
<table border=1>
<tr><th>Base time</th><th>Time with PE</th><th>Your time</th><th>Your POS time</th><tr>
<tr><td id=basetime align=right><? echo $productiontime ?></td><td id=petime align=right>&nbsp;</td><td id=youtime align=right>&nbsp;</td><td id=youpostime align=right>&nbsp;</td></tr>
<tr><th>iskh</th><td id="peiskh" align=right>&nbsp;</td><td id=youriskh align=right>&nbsp;</td><td id=posiskh align=right>&nbsp;</td></tr>
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
$inventionchecksql="select metaGroupID,parentTypeID from invMetaTypes where typeid=?";

$stmt = $dbh->prepare($inventionchecksql);
$stmt->execute(array($itemid));
$row = $stmt->fetchObject();
$dctype='';
if ($row->metaGroupID == 2)
{
$baseid=$row->parentTypeID;

$inventionsql="select invTypes.typeid,invTypes.typename,ramTypeRequirements.quantity,chance from ramTypeRequirements,invBlueprintTypes,invTypes,evesupport.inventionChance where producttypeid=? and ramTypeRequirements.typeid=invBlueprintTypes.blueprintTypeID and activityid=8 and invTypes.typeid=requiredTypeID and groupid !=716 and inventionChance.typeid=producttypeid";
$stmt = $dbh->prepare($inventionsql);
?>
<div id="invention">
<h1><a href="#" class="jqModal">Invention Material Requirements</a></h1>
<label for="inventionchance">Invention chance</label><input type=text id="inventionchance" value="40" onchange='runinventionnumbers()'>%<br>
<label for="inventprofit">Remove from isk/hr</label><input type=checkbox id="inventprofit" onchange='runinventionnumbers()'><br>
<label for="inventruns">Runs per invention</label><input type=test id="inventruns" value=10 onchange='runinventionnumbers()'>
<table border=1>
<tr><th>Datacore Name</th><th>Datacore Quantity</th><th>Datacore Cost</th></tr>
<?
$stmt->execute(array($baseid));
$chance=0.4;
while ($row = $stmt->fetchObject()){
echo "<tr><td>".$row->typename."</td><td id='inventquantity-".$row->typeid."' align=;right'>".$row->quantity."</td><td id='inventcost-".$row->typeid."' align='right'>&nbsp</td></tr>\n";
$typeid2.=",".$row->typeid;
$dctype.=",".$row->typeid;
$chance=$row->chance;
}
$typeid2=trim($typeid2,",");
$dctype=trim($dctype,",");
?>
<tr><th colspan=2>Datacore cost per Successful invention</th><td id='inventtotalcost' align='right'>&nbsp</td></tr>

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

<div id="prices" style="width:50%;position:absolute;<? echo $pricepos;?>">
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

$sql="select invTypes.typename,invTypes.typeid,if(blueprintTypeID,1,0) canmake from invTypes left join invBlueprintTypes on (invTypes.typeid= invBlueprintTypes.producttypeid) where invTypes.typeid in ($typeid2,$itemid)";
$stmt = $dbh->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetchObject())
{
    $name="<img src='http://image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->typename;
    if  (array_key_exists("HTTP_EVE_TRUSTED",$_SERVER)) {$name = "<a name='price-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";}
    echo "<tr><td>".$name."</td>";
    if ($row->canmake)
    {
        echo "<td><a href='/blueprints/".$row->typeid."/0/0' target='_blank'>make</a></td>";
    }
    else
    {
       echo "<td></td>";
    }

    echo "<td id=\"".$row->typeid.'-jitaprice" align=right class=jitaprice>';
    if (array_key_exists($row->typeid,$cookieprice) && is_numeric($cookieprice[$row->typeid])&&!$ignoreprice)
    {  
       $price=$cookieprice[$row->typeid];
       echo $cookieprice[$row->typeid];
    }
    else
    { 
        $pricedata=$memcache->get('forgesell-'.$row->typeid);
        $values=explode("|",$pricedata);
        $price=$values[0];
        if (!(is_numeric($price)))
        {
            $price=0;
        }
            echo $price;
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
dctypes=[<? echo $dctype?>];
typeide=[<? echo $typeide?>];
typetotal=[<? echo trim(trim($typeide.",".$typeid,",").",".$itemid,",")?>];
itemid=<? echo $itemid ?>;
url="http://www.fuzzwork.co.uk/blueprints/calc.php?bpid=<? echo $itemid ?>";
linkurl="http://www.fuzzwork.co.uk/blueprints/<? echo $itemid ?>/";
xmlurl="http://www.fuzzwork.co.uk/blueprints/xml/<? echo $itemid ?>/";
staticurl="http://www.fuzzwork.co.uk/blueprints/static/<? echo $itemid ?>/";
</script>
<br><br>
<div id="search" >
<form method=post action='/blueprints/calc.php' id="nextsearch">
<input type=text width=30 id="blueprintname" name='blueprintname' />
<input type=submit value="Do calculations" />
</form>
</div>
<div class="jqmWindow" id="inventiondialog">
<body>
<form id='calculator' name='calculator'>
 <? echo $chance;?>
<table>
<tr><th>Base Chance</th><th>Encryption Skill</th><th>Datacore Skill 1</th><th>Datacore Skill 2</th><th>MetaItem</th><th>Decryptor</th><th>Chance</th></tr>
<tr>
<td class="slidercell">
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
<input type=radio name="decryptor" value=1 id="dec1" checked="checked" onchange="calculateresult();"/><label for="dec1">None</label><br/>
<input type=radio name="decryptor" value=0.6 id="dec2" onchange="calculateresult();"/><label for="dec2">0.6</label><br/>
<input type=radio name="decryptor" value=1 id="dec3" onchange="calculateresult();"/><label for="dec3">1.0</label><br/>
<input type=radio name="decryptor" value=1.1 id="dec4"  onchange="calculateresult();"/><label for="dec4">1.1</label><br/>
<input type=radio name="decryptor" value=1.2 id="dec5"  onchange="calculateresult();"/><label for="dec5">1.2</label><br/>
<input type=radio name="decryptor" value=1.8 id="dec6"  onchange="calculateresult();"/><label for="dec6">1.8</label>
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
<tr><th>Decryptor Modifier</th><th>Decryptor Effects</th></tr>
<tr><td>None</td><td>No chance modifier, end copy is ME -4, PE -4 </td></tr>
<tr><td>0.6</td><td>Chance is reduced to 60% of what it was. Max runs is raised by 9.  end copy is ME -6, PE -3. Very rarely worth it.</td>
</tr>
<tr><td>1.0</td><td>Chance is unaffected. Max runs is raised by 2.  end copy is ME -3, PE 0.</td>
</tr>
<tr><td>1.1</td><td>Chance is raised to 110% of what it was.  end copy is ME -1, PE -1.</td>
</tr>
<tr><td>1.2</td><td>Chance is raised to 120% of what it was. Max runs is raised by 1.  end copy is ME -2, PE 1.</td>
</tr>
<tr><td>1.8</td><td>Chance is raised to 180% of what it was. Max runs is raised by 4.  end copy is ME -5, PE -2.</td>
</tr>
</table>
</div>

</body>
</html>
