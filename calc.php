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
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link href="/blueprints/main.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  <link href="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" rel="stylesheet" type="text/css"/>
  <script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="/blueprints/dataTables.currencySort.js"></script>
  <script type="text/javascript" src="/blueprints/ColVis.min.js"></script>

  <script src="/blueprints/format.js"></script>

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
        basematerials.fnUpdate(addCommas(Math.round(perfect+(perfect*wasteage))),document.getElementById("basemat-"+typeid[type]),2,0);
        document.getElementById(typeid[type] + "-you").innerHTML=addCommas(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe)))));
        basematerials.fnUpdate(addCommas(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))),document.getElementById("basemat-"+typeid[type]),3,0);
        document.getElementById(typeid[type] + "-cost").innerHTML=addIskCommas(Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100);
        basematerials.fnUpdate(addIskCommas(Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100),document.getElementById("basemat-"+typeid[type]),5,0);
        document.getElementById(typeid[type] + "-perfectcost").innerHTML=addIskCommas(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100);
         basematerials.fnUpdate(addIskCommas(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100),document.getElementById("basemat-"+typeid[type]),4,0);
        document.getElementById(typeid[type] + "-diff").innerHTML=addCommas(Math.round(((Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100)-(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100))*100)/100);
        basematerials.fnUpdate(addCommas(Math.round(((Math.round(Math.round(perfect+(perfect*wasteage)+(perfect*(0.25-(0.05*pe))))*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100)-(Math.round(perfect*parseFloat(document.getElementById(typeid[type] + "-jitaprice").innerHTML)*100)/100))*100)/100),document.getElementById("basemat-"+typeid[type]),6,0); 
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
     basematerials.fnDraw();

    for (type in typeide)
    {
        number=parseInt(document.getElementById(typeide[type] + "-extranumperfect").innerHTML);
        if (document.getElementById(typeide[type] + "-perfect"))
        {
            number=(number+(number*(0.25-(0.05*pe))));
        }
        document.getElementById(typeide[type] + "-extranum").innerHTML=addCommas(Math.round(number));
        extramaterials.fnUpdate(addCommas(Math.round(number)),document.getElementById("extramat-"+typeide[type]),2,0);
        document.getElementById(typeide[type] + "-extracost").innerHTML=addIskCommas(Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100);
        extramaterials.fnUpdate(addIskCommas(Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100),document.getElementById("extramat-"+typeide[type]),4,0);
        etotal=etotal+Math.round((number*document.getElementById(typeide[type] + "-extradam").innerHTML*document.getElementById(typeide[type] + "-jitaprice").innerHTML)*100)/100;
    }
    extramaterials.fnDraw();
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
        decryptor=$('input:radio[name=decryptor]:checked').val();
        if (decryptor != "none" )
        { 
            document.getElementById("displaydecryptor").innerHTML=document.getElementById("decryptorname-"+decryptor).innerHTML;
            document.getElementById("displaydecryptorq").innerHTML=1;
            document.getElementById("displaydecryptorc").innerHTML=addIskCommas(document.getElementById(document.getElementById("decryptorid-"+decryptor).innerHTML + "-jitaprice").innerHTML);
            totalcost=totalcost+parseFloat(document.getElementById(document.getElementById("decryptorid-"+decryptor).innerHTML + "-jitaprice").innerHTML);
            
        }
        else
        {
            document.getElementById("displaydecryptor").innerHTML="No Decryptor";
            document.getElementById("displaydecryptorq").innerHTML=0;
            document.getElementById("displaydecryptorc").innerHTML=0;
        }


       document.getElementById("inventtotalcost").innerHTML=addIskCommas(((Math.round(totalcost/(inventionchance/100))/100))*100);
       inventioncost=totalcost/(inventionchance/100);
       document.getElementById("inventioncost").innerHTML=addIskCommas(((Math.round((totalcost/(inventionchance/100))/parseInt(document.getElementById("inventruns").value))/100))*100);
    }
    runmenumbers();
}

function updatelink()
{
    me=parseInt(document.getElementById("me").value);
    pe=parseInt(document.getElementById("pe").value);
    prode=parseInt(document.getElementById("prode").value);
    ind=parseInt(document.getElementById("ind").value);
    urltoshow=linkurl+me+"/"+pe+"/"+prode+"/"+ind;
    urltoxml=xmlurl+me+"/"+pe;
    urltoxml2=xml2url+me+"/"+pe+"/"+prode+"/"+ind;
    urltostatic=staticurl+me+"/"+pe;
    urltocookie=url+"&mpe="+pe+"&ind="+ind+"&setcookie=1";
    document.getElementById("cookieme").href=urltocookie;
    document.getElementById("linkme").href=urltoshow;
    document.getElementById("xmlme").href=urltoxml;
    document.getElementById("xml2me").href=urltoxml2;
    document.getElementById("staticme").href=urltostatic;
    document.getElementById("clearprice").href=urltoshow+"/clearprice";
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

function updateprices(data)
{
   $("#pricetable").html(data);
   runmenumbers();
    $("td.togglebuy").click( function() {
        buy=parseFloat($(this).parents("tr").children(".jitabuy")[0].innerHTML);
        sell=parseFloat($(this).parents("tr").children(".jitasell")[0].innerHTML);
        current=parseFloat($(this).parents("tr").children(".jitaprice")[0].innerHTML);
        if (sell==current)
        {
            $(this).parents("tr").children(".jitaprice")[0].innerHTML=buy;
            $(this)[0].innerHTML='B';
        }
        else if (buy==current)
        {
            $(this).parents("tr").children(".jitaprice")[0].innerHTML=sell;
            $(this)[0].innerHTML='S';
        }
        runmenumbers();
   });

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
   var priceurl="//www.fuzzwork.co.uk/blueprints/prices.php";
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
   var saveurl="//www.fuzzwork.co.uk/blueprints/savebp.php";
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
<script type="text/javascript" src="/blueprints/items.php"></script>
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
		$( "#prices" ).draggable({ handle: "#priceheader" }).mouseup(function(){  
                    var coords=[];  
                    var coord = $(this).position();  
                    var item={ coordTop:  Math.floor(coord.top), coordLeft: Math.floor(coord.left),width: $("#prices").css('width') };  
                    coords.push(item);
                    createCookie("pricepos",JSON.stringify(coords),700);
                 });
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
switch (decryptor)
{
case "none":
document.getElementById("me").value=-4;
document.getElementById("prode").value=-4;
decryptor=1;
document.getElementById("inventruns").value=document.getElementById("baseruns").value;
break;
case "0.6":
document.getElementById("me").value=-6;
document.getElementById("prode").value=-3;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+9;
break;
case "0.9":
document.getElementById("me").value=-2;
document.getElementById("prode").value=-4;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+7;
break;
case "1":
document.getElementById("me").value=-3;
document.getElementById("prode").value=0;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+2;
break;
case "1.1":
document.getElementById("me").value=-1;
document.getElementById("prode").value=-1;
document.getElementById("inventruns").value=document.getElementById("baseruns").value;
break;
case "1.2":
document.getElementById("me").value=-2;
document.getElementById("prode").value=1;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+1;
break;
case "1.5":
document.getElementById("me").value=-3;
document.getElementById("prode").value=-5;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+3;
break;
case "1.8":
document.getElementById("me").value=-5;
document.getElementById("prode").value=-2;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+4;
break;
case "1.9":
document.getElementById("me").value=-3;
document.getElementById("prode").value=-5;
document.getElementById("inventruns").value=parseInt(document.getElementById("baseruns").value)+2;
break;
}


InventionChance = Math.min((basechance * (1 + (0.01 * encryption)) * (1 + ((datacore1+datacore2) * (0.1 / (5 -metaitem))))*decryptor),100);
document.getElementById("results").innerHTML=Math.floor(InventionChance*100)/100+"%";
document.getElementById("inventionchance").value=Math.floor(InventionChance*100)/100;
runinventionnumbers();
}


$(document).ready(function() {


    basematerials=$("#basematerials").dataTable({
            "bPaginate": false,
            "bFilter": false,
            "bInfo": false,
            "bAutoWidth": false,
            "bSortClasses": false,
            "bDeferRender": false,
            "aoColumns":[null,null,{ "sType": "currency" },{ "sType": "currency" },{ "sType": "currency" },{ "sType": "currency" },{ "sType": "currency" },null],
            "sDom": 'C<"clear">lfrtip'
    });
    extramaterials=$("#extramaterials").dataTable({
            "bPaginate": false,
            "bFilter": false,
            "bInfo": false,
            "bAutoWidth": false,
            "bSortClasses": false,
            "bDeferRender": false,
            "aoColumns":[null,null,null,null,{ "sType": "currency" }],
    });


    $("input#blueprintname").autocomplete({ source: source });
    runmenumbers();
    runinventionnumbers();
    $('#inventiondialog').dialog({autoOpen: false,width:600});
    calculateresult();

    $("td.togglebuy").click( function() {
        buy=parseFloat($(this).parents("tr").children(".jitabuy")[0].innerHTML);
        sell=parseFloat($(this).parents("tr").children(".jitasell")[0].innerHTML);
        current=parseFloat($(this).parents("tr").children(".jitaprice")[0].innerHTML);
        if (sell==current)
        {
            $(this).parents("tr").children(".jitaprice")[0].innerHTML=buy;
            $(this)[0].innerHTML='B';
        }
        else if (buy==current)
        {
            $(this).parents("tr").children(".jitaprice")[0].innerHTML=sell;
            $(this)[0].innerHTML='S';
        }
        runmenumbers();
   });

   showprices=readCookie("showprices");
   if (!showprices)
   {
      showprices=1;
   }

   $("#togglepricedetail").click(function(){twidth=$("#prices").css('width');$("div#pricedetail").toggle();$("div#prices").css({'height': 'auto','width':twidth});showprices=showprices*-1;createCookie("showprices",showprices,700)});
   
   if (showprices==-1)
   {
       twidth=$("#prices").css('width');
       $("div#pricedetail").toggle();
       $("div#prices").css({'height': 'auto','width':twidth});
   }
  
 
});
</script>

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

</head>
<body>
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
            <select id="priceregion" onchange='$.post("/blueprints/loadregion.php",{"region":$("#priceregion").val(),"items":typetotal.join(":")},function(data) {updateprices(data);});'>
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
<input type=submit value="Do calculations" />
</form>
</div>
<div id="inventiondialog" title="Invention Calculator" class="hidden">
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
All images are  copyright 2012 CCP hf. All rights reserved. 'EVE', 'EVE Online', 'CCP', and all related logos and images are trademarks or registered trademarks of CCP hf.
<?php include('/home/web/fuzzwork/analytics.php'); ?>
</body>
</html>
