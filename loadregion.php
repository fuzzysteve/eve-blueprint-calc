<table border=1>
<?php
$database="eve";
require_once(__DIR__.'/Price/Price.php');

require_once('db.inc.php');

$items=$_POST["items"];
$regionid='forge';
if (isset($_POST['region']) && is_numeric($_POST['region']) && $_POST['region']!=10000002)
{
$regionid=$_POST['region'];
}
$itemarray=array();

foreach (explode(":",$items) as $item)
{
    if (is_numeric($item))
    {
        $itemarray[]=$item;
    }
}


if (array_key_exists("prices",$_COOKIE))
{
    $cookieprice=json_decode($_COOKIE["prices"],true);
}
else
{
    $cookieprice=array(0);
}

$sql="select invTypes.typename,invTypes.typeid,if(blueprintTypeID,1,0) canmake from $database.invTypes left join $database.invBlueprintTypes on (invTypes.typeid= invBlueprintTypes.producttypeid) where invTypes.typeid in (".join(',',$itemarray).")";
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
        list($price,$pricebuy)=returnprice($row->typeid,$regionid);
        echo $price;

    }

echo "</td><td class=\"priceedit hidden\"><input style=\"text-align: right;\" type=text id=\"".$row->typeid."-priceedit\" align=right value=\"$price\" onchange=\"updateprice(".$row->typeid.")\" maxlength=10></td><td id=\"".$row->typeid."-jitasell\" class='hidden jitasell'>$price</td><td id=\"".$row->typeid."-jitabuy\" class='hidden jitabuy'>$pricebuy</td></tr>\n";
}


?>
</table>
<a name='editprice' onclick="toggleprice()">Edit prices</a>
<a name='saveprice' onclick="saveprice()" class="priceedit hidden">|save prices</a>
<a href="" id="clearprice" class="priceedit hidden">|reset prices</a>



