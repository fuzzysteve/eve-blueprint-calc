<?php

require_once('db.inc.php');
?>
<html>
<head>
<title>BP Costs - Blueprint selection</title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  
<script src="/blueprints/items.js"></script>
<script>
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
<?php include('/home/web/fuzzwork/htdocs/bootstrap/header.php'); ?>
</head>
<body>
<?php include('/home/web/fuzzwork/htdocs/menu/menubootstrap.php'); ?>
<div class="container">
<div class='row'><div class="span10">

<?
if (array_key_exists('error',$_GET))
{
echo "There was a problem finding your item. Did you put in the blueprint name, rather than the item name?";
}
?>
Select your blueprint:
<form method=post action='calc.php'>
<input type=text width=30 id="blueprintname" name='blueprintname' />
<select name=database id=database>
<option value=0>Current</option>
<?
$sql='select id,name from evesupport.dbversions order by id desc';

$stmt = $dbh->prepare($sql);

$stmt->execute();

while ($row = $stmt->fetchObject()){
echo "<option value='".$row->id."'>".$row->name."</option>";
}
?>
</select>

<input type=submit value="Do calculations" />
</form>
<br><br>
<a href="/blueprints/enterlist.php">Form based Entry</a> - Cut and paste a list from your S&amp;I screen, to have links generated for you.<br>
<?

if (array_key_exists('blueprints',$_COOKIE))
{
    $currentstructure=json_decode(stripslashes($_COOKIE["blueprints"]),true);
    $keys='';
    foreach (array_keys($currentstructure) as $key)
    {   
        if (is_numeric($key))
        {
            $keys.=$key.",";
        }
    }
    $keys=trim($keys,",");

    $sql="select typename,typeid,parentgroupid from invTypes,invMarketGroups where invTypes.published=1 and invMarketGroups.marketgroupid=invTypes.marketgroupid and typeid in ($keys) order by parentgroupid,typename";


    $stmt = $dbh->prepare($sql);

    $stmt->execute();

    echo "<h2>Saved Blueprints</h2>\n";
$marketgroupid=0;
    while ($row = $stmt->fetchObject()){
        if ($marketgroupid!=$row->parentgroupid)
        {
            echo "<br>";
            $marketgroupid=$row->parentgroupid;
        }

        if (is_numeric($currentstructure[$row->typeid]['me']) && is_numeric($currentstructure[$row->typeid]['pe']))
        {
            echo '<a href="/blueprints/'.$row->typeid.'/'.$currentstructure[$row->typeid]['me'].'/'.$currentstructure[$row->typeid]['pe'].'">'.$row->typename.'</a> <a href="/shopping/'.$row->typeid.'/'.$currentstructure[$row->typeid]['me'].'/'.$currentstructure[$row->typeid]['pe'].'">Run Calculator</a><br>';
        }
    }
}
?>
<p>If you save any blueprints, be aware you can only have one set of details per item type (it's keyed on the item id. Editing something seperate requires more major changes). In addition, the saved blueprints are browser specific, and will last for one year from the last update. This is to keep from keeping your details on this server. They're all kept in your cookies.</p>
</div>
</div>
</div>
<?php include('/home/web/fuzzwork/htdocs/bootstrap/footer.php'); ?>

</body>
</html>
