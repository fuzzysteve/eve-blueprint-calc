<?php
require_once('db.inc.php');



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
   if (preg_match("/^(.+?)\t(\d+?)?\t(.+?)\tBlueprint\t(Yes|No)\t(\d+?)\t(\d+?)\t?(\d+?)?$/",trim($entry),$matches))
   {
       if(isset($typeidlookup[$matches[1]]))
       {
           $quantity=1;
           if (is_numeric($matches[2]))
           {
               $quantity=$matches[2];
           }

           if(isset($inventory[$typeidlookup[$matches[1]]."-".$matches[4].":".$matches[7]."-".$matches[5]."-".$matches[6]]))
           {
               $inventory[$typeidlookup[$matches[1]]."-".$matches[4].":".$matches[7]."-".$matches[5]."-".$matches[6]]+=$quantity;
           }
           else
           {
               $inventory[$typeidlookup[$matches[1]]."-".$matches[4].":".$matches[7]."-".$matches[5]."-".$matches[6]]=$quantity;
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
<tr><th>id</th><th>Name</th><th>Quantity</th><th>ME</th><th>PE</th><th>Copy?</th></tr>
</thead>
<tbody>
<?


foreach (array_keys($inventory) as $blueprint ){
list($typeid,$copy,$me,$pe)=explode("-",$blueprint);
echo "<tr><td>".$typeid."</td><td><a href=\"//www.fuzzwork.co.uk/blueprints/".$productlookup[$typeid]."/$me/$pe\" target='_blank'>".$typenamelookup[$typeid]."</a></td><td>".$inventory[$blueprint]."</td><td>$me</td><td>$pe</td><td>$copy</td></tr>";
}



?>

</tbody>
<tfoot>
</tfoot>
</table>

<?php include('/home/web/fuzzwork/analytics.php'); ?>

</body>
</html>

