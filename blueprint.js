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
    document.getElementById(itemid+"-cost").innerHTML=addIskCommas(Math.round(parseFloat(document.getElementById(itemid+"-jitaprice").innerHTML)*portionsize*100)/100);;
    document.getElementById("etotal").innerHTML=addIskCommas(Math.round(etotal*100)/100);
    document.getElementById("basictotal").innerHTML=addIskCommas(Math.round(total*100)/100);
    document.getElementById("perfecttotal").innerHTML=addIskCommas(Math.round(perfecttotal*100)/100);
    document.getElementById("overalltotal").innerHTML=addIskCommas(Math.round((total+etotal)*100)/100);
    document.getElementById("totaldifference").innerHTML=addIskCommas(Math.round((total-perfecttotal)*100)/100);
    document.getElementById("profit").innerHTML=addIskCommas(Math.round(((parseFloat(document.getElementById(itemid+"-jitaprice").innerHTML)*portionsize)-(total+etotal))*100)/100);
    pmargin=Math.round(((parseFloat(document.getElementById(itemid+"-jitaprice").innerHTML)*portionsize)-(total+etotal))*100)/100;
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
        runs=document.getElementById("inventruns").value;
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

    if (!noinvent)
    {
        document.getElementById("peisk24h").innerHTML=addIskCommas(Math.round((((pmargin2*runs)/(Math.floor((timewaste*runs)/86400)+1))/24)*100)/100);
        document.getElementById("yourisk24h").innerHTML=addIskCommas(Math.round((((pmargin2*runs)/(Math.floor((timewaste*(1 - .04 *parseInt(document.getElementById("ind").value))*runs)/86400)+1))/24)*100)/100);
        document.getElementById("posisk24h").innerHTML=addIskCommas(Math.round((((pmargin2*runs)/(Math.floor(((timewaste*(1 - .04 *parseInt(document.getElementById("ind").value))*.75)*runs)/86400)+1))/24)*100)/100);
    }
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
       metaitem=parseInt(document.getElementById("metaitem").value)
       if (metaitem>0)
       {
           if (metatypes[metaitem]===undefined)
           {
           document.getElementById("displaymetaitem").innerHTML="No Such Meta Item";
           document.getElementById("displaymetaitemq").innerHTML="0";
           document.getElementById("displaymetaitemc").innerHTML="0";
           }
           else
           {
               document.getElementById("displaymetaitem").innerHTML=document.getElementById("price-"+metatypes[metaitem]).innerHTML;
               document.getElementById("displaymetaitemq").innerHTML=1;
               document.getElementById("displaymetaitemc").innerHTML=addIskCommas(document.getElementById(metatypes[metaitem]+ "-jitaprice").innerHTML);
               totalcost=totalcost+parseFloat(document.getElementById(metatypes[metaitem]+ "-jitaprice").innerHTML);
           }
       }
       else
       {
           document.getElementById("displaymetaitem").innerHTML="No Meta Item";
           document.getElementById("displaymetaitemq").innerHTML="0";
           document.getElementById("displaymetaitemc").innerHTML="0";
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
    urltocookie=url+"&mpe="+pe+"&ind="+ind+"&metallurgy="+metallurgy+"&research="+research+"&setcookie=1";
    document.getElementById("cookieme").href=urltocookie;
    document.getElementById("linkme").href=urltoshow;
    document.getElementById("xmlme").href=urltoxml;
    document.getElementById("xml2me").href=urltoxml2;
    document.getElementById("staticme").href=urltostatic;
    document.getElementById("clearprice").href=urltoshow+"/?clearprice=1";
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
   var priceurl="/blueprints/prices.php";
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
   var saveurl="/blueprints/savebp.php";
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




$(function() {
		$( "#peslider" ).slider({
			value:mpe,
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
			value:me,
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
			value:pe,
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
			value:industry,
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
			value:metallurgy,
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
                        value:research,
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

   $("#togglepricedetail").click(function(){twidth=$("#prices").css('width');$("div#pricedetail").toggle();$("div#prices").css({'height': 'auto','width':twidth});showprices=showprices*-1;createCookie("showprices",showprices,700); $("#togglepricedetail").toggleClass("glyphicon-chevron-down glyphicon-chevron-up");});

   if (showprices==-1)
   {
       twidth=$("#prices").css('width');
       $("div#pricedetail").toggle();
       $("#togglepricedetail").removeClass("glyphicon-chevron-up");
       $("#togglepricedetail").addClass("glyphicon-chevron-down");
       $("div#prices").css({'height': 'auto','width':twidth});
   }


});
