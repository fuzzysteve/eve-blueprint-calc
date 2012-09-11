<?php

require_once('db.inc.php');
?>
<html>
<head>
<title>BP Costs - Blueprint selection</title>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  
<script>
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
});
</script>

<style>
.ui-menu .ui-menu-item a {
    display: block;
    line-height: 1;
    padding: 0.2em 0.4em;
    text-decoration: none;
}
</style>
</head>
<body>
<?
if (array_key_exists('error',$_GET))
{
echo "There was a problem finding your item. Did you put in the blueprint name, rather than the item name?";
}
?>
Select your blueprint:
<form method=post action='calc2.php'>
<input type=text width=30 id="blueprintname" name='blueprintname' />
<input type=submit value="Do calculations" />
</form>
<?

if (array_key_exists('blueprints',$_COOKIE))
{
    $currentstructure=json_decode($_COOKIE["blueprints"],true);
    $keys='';
    foreach (array_keys($currentstructure) as $key)
    {   
        if (is_numeric($key))
        {
            $keys.=$key.",";
        }
    }
    $keys=trim($keys,",");

    $sql="select typename,typeid from invTypes where invTypes.published=1 and typeid in ($keys)";

    $stmt = $dbh->prepare($sql);

    $stmt->execute();
    echo "<h2>Saved Blueprints</h2>\n";
    while ($row = $stmt->fetchObject()){
        if (is_numeric($currentstructure[$row->typeid]['me']) && is_numeric($currentstructure[$row->typeid]['pe']))
        {
            echo '<a href="/blueprints/'.$row->typeid.'/'.$currentstructure[$row->typeid]['me'].'/'.$currentstructure[$row->typeid]['pe'].'">'.$row->typename.'</a> <a href="/shopping/'.$row->typeid.'/'.$currentstructure[$row->typeid]['me'].'/'.$currentstructure[$row->typeid]['pe'].'">Run Calculator</a><br>';
        }
    }
}
?>
<p>If you save any blueprints, be aware you can only have one set of details per item type (it's keyed on the item id. Editing something seperate requires more major changes). In addition, the saved blueprints are browser specific, and will last for one year from the last update. This is to keep from keeping your details on this server. They're all kept in your cookies.</p>
</body>
</html>
