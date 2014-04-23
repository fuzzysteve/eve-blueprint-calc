<?php
require_once(__DIR__.'/Price/Price.php');

require_once('db.inc.php');

if(isset($_REQUEST['clearprice']) && $_REQUEST['clearprice']) {
	setcookie('prices', '', time()-86400);
	unset($_COOKIE['prices']);
}

$database='eve';
$databasenumber=0;

if((array_key_exists('database',$_POST) &&  is_numeric($_POST['database']))|| (array_key_exists('database',$_GET) &&  is_numeric($_GET['database']))) {

	if(array_key_exists('database',$_POST)) {
		$dbnum=$_POST['database'];
	} else {
		$dbnum=$_GET['database'];
	}

	$sql='select id,version from evesupport.dbversions where id=?';
	$stmt = $dbh->prepare($sql);

	$stmt->execute(array($dbnum));

	while($row = $stmt->fetchObject()){
		$databasenumber=$row->id;
		$database=$row->version;
	}

}


if(isset($_REQUEST['blueprintname'])) {
	$bpid=strtolower($_REQUEST['blueprintname']);
	$bpid=str_replace(' blueprint','',$bpid);
	$sql="select typename,typeid,portionSize from $database.invTypes where lower(typename)=lower(?)";
} else if(isset($_REQUEST['bpid']) && is_numeric($_REQUEST['bpid'])) {
	$bpid=$_REQUEST['bpid'];
	$sql="select typename,typeid,portionSize from $database.invTypes where typeid=?";
} else {
	header('Location: /blueprints/index.php?error=1');
	exit;
}

$stmt = $dbh->prepare($sql);
$stmt->execute(array($bpid));

if($row = $stmt->fetchObject()) {
	$itemname=$row->typename;
	$itemid=$row->typeid;
	$portionsize=$row->portionSize;
} else {
	header('Location: /blueprints/index.php?error=1');
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

$inventionchecksql="select metaGroupID,parentTypeID from $database.invMetaTypes where typeid=?";

$stmt = $dbh->prepare($inventionchecksql);
$stmt->execute(array($itemid));
if($row = $stmt->fetchObject()) {
	$metaGroupID=$row->metaGroupID;
	$baseid=$row->parentTypeID;
} else {
	$metaGroupID=0;
	$baseid=0;
}

if($metaGroupID==2) {
	$me=-4;
	$pe=-4;
}

if(isset($_COOKIE['mpe']) && is_numeric($_COOKIE['mpe']) && $_COOKIE['mpe'] >= 0 && $_COOKIE['mpe'] <= 5) {
	$mpe=$_COOKIE['mpe'];
}

if(isset($_COOKIE['industry']) && is_numeric($_COOKIE['industry']) && is_numeric($_COOKIE['industry']) >= 0 && is_numeric($_COOKIE['industry']) <= 5) {
	$ind=$_COOKIE['industry'];
}

if(isset($_REQUEST['mpe']) && is_numeric($_REQUEST['mpe']) && $_REQUEST['mpe'] >= 0 && $_REQUEST['mpe'] <= 5) {
	$mpe=$_REQUST['mpe'];
}

if(isset($_REQUEST['ind']) && is_numeric($_REQUEST['ind']) && $_REQUEST['ind'] >= 0 && $_REQUEST['ind'] <= 5) {
	$ind=$_GET['ind'];
}

if(isset($_REQUEST['metallurgy']) && is_numeric($_REQUEST['metallurgy']) && $_REQUEST['metallurgy'] >= 0 && $_REQUEST['metallurgy'] <= 5) {
	$metallurgy=$_REQUST['metallurgy'];
}

if(isset($_REQUEST['research']) && is_numeric($_REQUEST['research']) && $_REQUEST['research'] >= 0 && $_REQUEST['research'] <= 5) {
	$research=$_REQUST['research'];
}

if(isset($_REQUEST['me']) && is_numeric($_REQUEST['me']) && $_REQUEST['me'] >= -6) {
	$me=$_REQUEST['me'];
}

if(isset($_REQUEST['pe']) && is_numeric($_REQUEST['pe']) && $_REQUEST['pe'] >= -6) {
	$pe=$_GET['pe'];
}

if(isset($_REQUEST['setcookie']) && $_REQUEST['setcookie']) {
	setcookie('industry', $ind, time()+31536000);
	setcookie('mpe', $mpe, time()+31536000);
	setcookie('metallurgy', $metallurgy, time()+31536000);
	setcookie('research', $research, time()+31536000);
}

$pricepos='';
if(isset($_COOKIE['pricepos'])) {
	$coords=json_decode(stripslashes($_COOKIE['pricepos']),true);
	$top=$coords[0]['coordTop'];
	$left=$coords[0]['coordLeft'];
	$pwidth=substr($coords[0]['width'],0,-2);
	if(is_numeric($top)&& is_numeric($left)&& is_numeric($pwidth)) {
		$pricepos=sprintf('left:%dpx;top:%dpx;', $left, $top);
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>BP Costs - <?= $itemname ?></title>
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
			var itemid=<?= (isset($itemid)?$itemid:'0') ?>;
			var portionsize=<?= (isset($portionsize)?$portionsize:'0') ?>;
			var productiontime=<?= (isset($productiontime)?$productiontime:'0') ?>;
			var productionmodifier=<?= (isset($productionmodifier)?$productionmodifier:'0') ?>;
			var waste=<?= (isset($wasteFactor)?$wasteFactor:'0') ?>;
			var mpe=<?= (isset($mpe)?$mpe:'0') ?>;
			var me=<?= (isset($me)?$me:'0') ?>;
			var pe=<?= (isset($pe)?$pe:'0') ?>;
			var industry=<?= (isset($ind)?$ind:'0') ?>;
			var metallurgy=<?= (isset($metallurgy)?$metallurgy:'0') ?>;
			var research=<?= (isset($research)?$research:'0') ?>;
		</script>

		<script type="text/javascript" src="/blueprints/items.js"></script>
		<script type="text/javascript" src="/blueprints/blueprint.js"></script>

		<?php
		if(file_exists('/home/web/fuzzwork/htdocs/bootstrap/header.php')) {
			include('/home/web/fuzzwork/htdocs/bootstrap/header.php');
		}
		?>
	</head>
	<body>

		<?php
		if(file_exists('/home/web/fuzzwork/htdocs/menu/menubootstrap.php')) {
			include('/home/web/fuzzwork/htdocs/menu/menubootstrap.php');
		}
		?>

		<div class="container">
			<div class="main">
				<h1 class="title"><?php
				if(isset($_SERVER['HTTP_EVE_TRUSTED'])) {
					printf('<a onclick="CCPEVE.showMarketDetails(%1$d)" class="marketlink">%2$s <img src="//image.eveonline.com/InventoryType/%1$d_64.png" alt="%2$s" class="icon64"/></a>', $itemid, $itemname);
				} else {
					printf('%2$s <img src="//image.eveonline.com/InventoryType/%1$d_64.png" alt="%2$s" class="icon64"/>', $itemid, $itemname);
				}
				?></h1>
			</div>
			<p>Things should now be working right for extra materials and how waste is applied there. Thanks go to <a href="https://gate.eveonline.com/Profile/Lutz%20Major">Lutz Major</a>, and other people from the forum.</p>

			<a href="" id="linkme">Link to these details</a>
			|
			<a href="" id="xmlme">XML</a>
			|
			<a href="" id="xml2me">Alternate Format XML (with times as well)</a>
			|
			<a href="" id="staticme">Bare Tables</a>
			|
			<a href="" id="cookieme">Set your Industry and Production Efficiency in a cookie</a>
			|
			<a name="savebp" onclick="saveblueprint()" class="marketlink">save blueprint</a>

			<div id="accordion">
				<div id="mecalcs" class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">
							<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Materials</a>
						</h3>
					</div>
					<div id="collapseOne" class="panel-collapse collapse in">
						<div class="panel-body">
							<label for="me">Blueprint ME</label>
							<input type=text value=0 id="me" size=3 style="width:3em;margin-right:1em;margin-left:1em">
							<div id="meslider" style="width:500px;display:inline-block;height:0.5em">
						</div>
						<br/>
						<label for="pe">Manufacturer PE</label>
						<input type="text" value="1" id="pe" size="1" style="width:1em;margin-right:1em;margin-left:1em">
						<div id="peslider" style="width:100px;display:inline-block;height:0.5em"></div>
						<br/>
						<input type="button" value="Update ME/PE" onclick="runmenumbers();">
						<h2>Base Materials</h2>
						<table border="1" id="basematerials">
							<thead>
								<tr>
									<th>Material</th>
									<th>Perfect</th>
									<th>With ME waste</th>
									<th>With your production waste</th>
									<th>Perfect Cost</th>
									<th>Cost</th>
									<th>Difference</th>
									<th>Waste Eliminated at</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$max=0;
							$sql="select typeid,name,greatest(0,sum(quantity)) quantity from (select invTypes.typeid typeid,invTypes.typeName name,quantity  from $database.invTypes,$database.invTypeMaterials where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID=? union select invTypes.typeid typeid,invTypes.typeName name,invTypeMaterials.quantity*r.quantity*-1 quantity from $database.invTypes,$database.invTypeMaterials,$database.ramTypeRequirements r,$database.invBlueprintTypes bt where invTypeMaterials.materialTypeID=invTypes.typeID and invTypeMaterials.TypeID =r.requiredTypeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and r.recycle=1) t group by typeid,name";
							$stmt = $dbh->prepare($sql);
							$stmt->execute(array($itemid,$itemid));
							$typeid="";
							while($row = $stmt->fetchObject()){
								if($row->quantity > 0) {
									if(isset($_SERVER['HTTP_EVE_TRUSTED'])) {
										$name = sprintf('<a name="mat-%1$d" onclick="CCPEVE.showMarketDetails(%1$d)" class="marketlink"><img src="//image.eveonline.com/InventoryType/%1$d_32.png" alt="%2$s" class="icon32"/> %2$s</a>', $row->typeid, $row->name);
									} else {
										$name = sprintf('<img src="//image.eveonline.com/InventoryType/%1$d_32.png" alt="%2$s" class="icon32"/> %2$s', $row->typeid, $row->name);
									}

									printf('<tr id="basemat-%d">', $row->typeid);
									printf('<td>%s</td>', $name);
									printf('<td id="%d-perfect">%d</td>', $row->typeid, $row->quantity);
									printf('<td id="%d-bp">0</td>', $row->typeid);
									printf('<td id="%d-you">0</td>', $row->typeid);
									printf('<td id="%d-perfectcost" style="text-align:right;">0</td>', $row->typeid);
									printf('<td id="%d-cost" style="text-align:right;">0</td>', $row->typeid);
									printf('<td id="%d-diff" style="text-align:right;">0</td>', $row->typeid);
									printf('<td id="%1$d-me" style="text-align:right;color:blue;text-decoration:underline;" onclick="setme(%2$d);">%2$d</td>', $row->typeid, floor($row->quantity*(($wasteFactor/100)/0.5)));
									printf('</tr>');

									$typeid .= sprintf('%d,', $row->typeid);
									$max = max($max, $row->quantity);
								}
							}
							$typeid=trim($typeid, ',');
							?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="4">Total</td>
									<td id="perfecttotal" style="text-align:right">&nbsp;</td>
									<td id="basictotal" style="text-align:right">&nbsp;</td>
									<td id="totaldifference" style="text-align:right">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="5">Total with Extra materials</td>
									<td id="overalltotal" style="text-align:right">&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td colspan="5">Sell Price</td>
									<td id="<?= $itemid?>-cost" style="text-align:right">&nbsp;</td>
									<td id=profit>&nbsp;</td>
								</tr>
								<?php if($metaGroupID==2) { ?>
									<tr>
										<td colspan="5">Invention Cost Per Unit</td>
										<td colspan=2 id="inventioncost" style="text-align:right">&nbsp;</td>
									</tr>
								<?php } ?>
							</tfoot>
						</table>

						<p>A no waste ME is: <?= floor($max*(($wasteFactor/100)/0.5)); ?></p>

						<h2>Extra Materials</h2>
						<table border="1" id="extramaterials">
							<thead>
								<tr>
									<th>Material</th>
									<th>Extra materials</th>
									<th>Extra Materials with PE</th>
									<th>Damage/use per job</th>
									<th>Cost</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$typeide="";
								$typeid2=$typeid;
								$sql="SELECT t.typeName tn, r.quantity qn, r.damagePerJob dmg,t.typeID typeid FROM $database.ramTypeRequirements r,$database.invTypes t,$database.invBlueprintTypes bt,$database.invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID != 16 and t.groupID = g.groupID";
								$stmt = $dbh->prepare($sql);
								$stmt->execute(array($itemid));
								while($row = $stmt->fetchObject()){
									if(isset($_SERVER['HTTP_EVE_TRUSTED'])) {
										$name = sprintf('<a name="extramat-%1$d" onclick="CCPEVE.showMarketDetails(%1$d)" class="marketlink"><img src="//image.eveonline.com/InventoryType/%1$d_32.png" alt="%2$s" class="icon32"/> %2$s</a>', $row->typeid, $row->tn);
									} else {
										$name = sprintf('<img src="//image.eveonline.com/InventoryType/%1$d_32.png" alt="%2$s" class="icon32"/> %2$s', $row->typeid, $row->tn);
									}

									printf('<tr id="extramat-%d">', $row->typeid);
									printf('<td>%s</td>', $name);
									printf('<td id="%d-extranumperfect">%d</td>', $row->typeid, $row->qn);
									printf('<td id="%d-extranum"></td>', $row->typeid);
									printf('<td id="%d-extradam">%s</td>', $row->typeid, $row->dmg);
									printf('<td id="%d-extracost" style="text-align:right"></td>', $row->typeid);
									printf('</tr>');

									$typeid2 .= sprintf(',%d', $row->typeid);
									$typeide .= sprintf(',%d', $row->typeid);
								}
								$typeid2 = trim($typeid2, ',');
								$typeide = trim($typeide, ',');
								?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="4">Total</td>
									<td id="etotal"></td>
								</tr>
							</tfoot>
						</table>

						<p>Extra Materials have PE waste applied, if they also exist in the main list.</p>

						<h2>Skills Required</h2>
						<table border=1>
							<tr>
								<th>Skill</th>
								<th>Level</th>
							</tr>
							<?php
							$sql="SELECT t.typeName tn, r.quantity qn FROM $database.ramTypeRequirements r,$database.invTypes t,$database.invBlueprintTypes bt,$database.invGroups g  where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID AND r.activityID = 1 and bt.productTypeID=? and g.categoryID = 16 and t.groupID = g.groupID";
							$stmt = $dbh->prepare($sql);
							$stmt->execute(array($itemid));
							while($row = $stmt->fetchObject()){
								echo "<tr><td>".$row->tn."</td><td>".$row->qn."</td></tr>\n";
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>

			<div id="accordion2">
				<div id="timecalcs" class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">
							<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">Time Calculations</a>
						</h3>
					</div>
					<div id="collapseTwo" class="panel-collapse collapse in">
						<div class="panel-body">
							<label for="prode">Blueprint PE</label>
							<input type=text value="0" id="prode" size="3" style="width:3em;margin-right:1em;margin-left:1em"/>
							<div id="prodeslider" style='width:500px;display:inline-block;height:0.5em'></div>
							<br/>
							<label for="ind">Manufacturer Industry</label>
							<input type="text" value="1" id="ind" readonly="y" size="1" style="width:1em;margin-right:1em;margin-left:1em">
							<div id="indslider" style="width:100px;display:inline-block;height:0.5em"></div>
							<br/>
							<table border="1">
								<tr>
									<th>Base time</th>
									<th>Time with PE</th>
									<th>Your time</th>
									<th title="POS assembly arrays get a time multiplier of 0.75.">Your POS time</th>
								<tr>
								<tr>
									<td id="basetime" style="text-align:right"><?= $productiontime ?></td>
									<td id="petime" style="text-align:right">&nbsp;</td>
									<td id="youtime" style="text-align:right">&nbsp;</td>
									<td id="youpostime" style="text-align:right">&nbsp;</td>
								</tr>
								<tr>
									<th>iskh</th>
									<td id="peiskh" style="text-align:right">&nbsp;</td>
									<td id="youriskh" style="text-align:right">&nbsp;</td>
									<td id="posiskh" style="text-align:right">&nbsp;</td>
								</tr>

								<?php if($metaGroupID==2) { ?>
									<tr title="The ISK/hr assuming you only put jobs in once a day. 3 hours =24 hours. 10 hours =24 hours. 25 hours=48 hours">
										<th>iskh 24H rounding</th>
										<td id="peisk24h" style="text-align:right">&nbsp;</td>
										<td id="yourisk24h" style="text-align:right">&nbsp;</td>
										<td id="posisk24h" style="text-align:right">&nbsp;</td>
									</tr>
								<?php } ?>
							</table>

							<h3>Material Efficiency Research Time</h3>
							<label for="met">Metallurgy</label>
							<input type="text" value="0" id="met" size="3" style="width:3em;margin-right:1em;margin-left:1em">
							<div id="metslider" style='width:500px;display:inline-block;height:0.5em'></div>
							<br/>
							<table border="1">
								<tr>
									<th>Base Research time</th>
									<th>Your Time</th>
									<th>POS Time</th>
								</tr>
								<tr>
									<td id="basemetime"><?= $researchMaterialTime ?></td>
									<td id="yourmetime">&nbsp</td>
									<td id="yourmepostime">&nbsp</td>
								</tr>
							</table>
							<h3>Production Efficiency Research Time</h3>
							<label for="research">Research</label>
							<input type="text" value="0" id="research" size="3" style="width:3em;margin-right:1em;margin-left:1em">
							<div id="researchslider" style="width:500px;display:inline-block;height:0.5em"></div>
							<br/>
							<table border="1">
								<tr>
									<th>Base Research time</th>
									<th>Your Time</th>
									<th>POS Time</th>
								</tr>
								<tr>
									<td id="basepetime"><?= $researchProductivityTime ?></td>
									<td id="yourpetime">&nbsp</td>
									<td id=yourpepostime>&nbsp</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
			<?php
			$dctype='';
			$metatypeid=array();
			if($metaGroupID == 2) {
				$inventionsql="select invTypes.typeid,invTypes.typename,ramTypeRequirements.quantity,chance from $database.ramTypeRequirements,$database.invBlueprintTypes,$database.invTypes,evesupport.inventionChance where producttypeid=? and ramTypeRequirements.typeid=invBlueprintTypes.blueprintTypeID and activityid=8 and invTypes.typeid=requiredTypeID and groupid !=716 and inventionChance.typeid=producttypeid";
				$stmt = $dbh->prepare($inventionsql);
				?>

				<div id="accordion3">
					<div id="invention" class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#collapseThree">Invention Calculations</a>
							</h3>
						</div>
						<div id="collapseThree" class="panel-collapse collapse in">
							<div class="panel-body">
								<span onclick="$('#inventiondialog').dialog('open');" class="btn btn-primary btn-sm">Open calculator</span>
								<p><a href="/blueprints/inventionxml/<?= $databasenumber ?>/<?= $itemid ?>">xml for materials</a></p>
								<label for="inventionchance">Invention chance</label>
								<input type="text" id="inventionchance" value="40" onchange="runinventionnumbers()">%
								<br/>
								<label for="inventprofit">Subtract from isk/hr</label>
								<input type="checkbox" id="inventprofit" onchange="runinventionnumbers()">
								<br/>
								<label for="inventruns">Runs per invention</label>
								<input type="text" id="inventruns" value="<?= $maxruns; ?>" disabled="disabled">
								<input type="hidden" id="baseruns" value="<?= $maxruns; ?>">
								<table border="1">
									<tr>
										<th>Invention Material Name</th>
										<th>Invention Material Quantity</th>
										<th>Datacore Cost</th>
									</tr>
									<?php
									$stmt->execute(array($baseid));
									$chance=0.4;
									while($row = $stmt->fetchObject()) {
										// TODO - refactoring not fully done below this point
										echo "<tr>";
										echo "<td>".$row->typename."</td>";
										echo "<td id='inventquantity-".$row->typeid."' align=;right'>".$row->quantity."</td>";
										echo "<td id='inventcost-".$row->typeid."' align='right'>&nbsp</td>";
										echo "</tr>";
										$typeid2 .= sprintf(',%d', $row->typeid);
										$dctype .= sprintf(',%d', $row->typeid);
										$chance = $row->chance;
									}

									$decryptorsql="select it2.typeid,it2.typename,coalesce(dta2.valueint,dta2.valueFloat) modifier from invBlueprintTypes ibt join ramTypeRequirements rtr on (ibt.blueprinttypeid=rtr.typeid)join invTypes it1 on (rtr.requiredTypeID=it1.typeid and it1.groupid=716  and activityid=8) join dgmTypeAttributes dta on ( it1.typeid=dta.typeid and dta.attributeid=1115) join invTypes it2 on (it2.groupid=coalesce(dta.valueint,dta.valueFloat)) join dgmTypeAttributes dta2 on (dta2.typeid=it2.typeid and dta2.attributeid=1112) where ibt.producttypeid=?";
									$stmt = $dbh->prepare($decryptorsql);
									$stmt->execute(array($baseid));
									while($row = $stmt->fetchObject()){
										echo "<tr class='hidden' id='decryptorrow-".$row->modifier."'>";
										echo "<td id='decryptorname-".$row->modifier."'>".$row->typename."</td>";
										echo "<td>1</td>";
										echo "<td id='decryptorcost-".$row->modifier."' align='right'>&nbsp</td>";
										echo "<td class='hidden' id='decryptorid-".$row->modifier."'>$row->typeid</td>";
										echo "</tr>";
										$typeid2.=",".$row->typeid;
									}

									$metatypessql="select invMetaTypes.typeid,coalesce(valuefloat,valueint) level from invMetaTypes join dgmTypeAttributes on (dgmTypeAttributes.typeid=invMetaTypes.typeid and attributeID=633) where metaGroupID=1 and parenttypeid=?";
									$stmt = $dbh->prepare($metatypessql);
									$stmt->execute(array($baseid));
									while($row = $stmt->fetchObject()){
										$typeid2.=",".$row->typeid;
										$metatypeid[$row->level]=$row->typeid;
									}

									$typeid2=trim($typeid2,",");
									$dctype=trim($dctype,",");
									?>
									<tr>
										<td id="displaydecryptor">No Decryptor</td>
										<td id="displaydecryptorq">0</td>
										<td id=displaydecryptorc align='right'></td>
									</tr>
									<tr>
										<td id="displaymetaitem">No metaitem</td>
										<td id="displaymetaitemq">0</td>
										<td id=displaymetaitemc align='right'></td>
									</tr>
									<tr>
										<th colspan=2>Material cost per Successful invention</th>
										<td id='inventtotalcost' align='right'>&nbsp</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
				<script>var noinvent=0;</script>
			<?php } else { ?>
				<script>var noinvent=1;</script>
			<?php } ?>

			<div id="prices" style="position:absolute;<?= $pricepos ?>" class="panel panel-info">
				<div id="priceheader" class="panel-heading">
					<span id="togglepricedetail" style="float:right" class="glyphicon glyphicon-chevron-up"></span> Prices
				</div>
				<div id="pricedetail" class="panel-body">
					<div>
						<select id="priceregion" onchange='$.post("/blueprints/loadregion.php",{"region":$("#priceregion").val(),"items":allitems.join(":")},function(data) {updateprices(data);});'>
						<?php
						$regionsql='select regionid,regionname from eve.mapRegions  where regionname not like "%-%" order by regionname';
						$regionstmt = $dbh->prepare($regionsql);
						$regionstmt->execute();

						while($row = $regionstmt->fetchObject()){
							if($row->regionid==10000002) {
								printf('<option value="%d">%s</option>', $row->regionid, $row->regionname);
							} else {
								printf('<option value="%d" selected="selected">%s</option>', $row->regionid, $row->regionname);
							}
						}
						?>
						</select>
				   </div>
					<div id="pricetable">
						<table border="1">
							<?php
							if(isset($_COOKIE['prices'])) {
								$cookieprice=json_decode($_COOKIE["prices"],true);
							} else {
								$cookieprice=array(0);
							}

							$sql="select invTypes.typename,invTypes.typeid,if(blueprintTypeID,1,0) canmake from $database.invTypes left join $database.invBlueprintTypes on (invTypes.typeid= invBlueprintTypes.producttypeid) where invTypes.typeid in ($typeid2,$itemid)";
							$stmt = $dbh->prepare($sql);
							$stmt->execute();
							while($row = $stmt->fetchObject()) {
								$name="<img src='//image.eveonline.com/InventoryType/".$row->typeid."_32.png' class='icon32'>".$row->typename;
								if(isset($_SERVER['HTTP_EVE_TRUSTED'])) {
									$name = "<a name='price-".$row->typeid."' id='price-".$row->typeid."' onclick=\"CCPEVE.showMarketDetails(".$row->typeid.")\" class='marketlink'>$name</a>";
								}
								echo "<tr><td id='toggle-".$row->typeid."' class='togglebuy' title='Toggle buy/sell'>S</td><td>".$name."</td>";
								if($row->canmake) {
									echo "<td><a href='/blueprints/".$row->typeid."/0/0' target='_blank'>make</a></td>";
								} else {
									echo "<td></td>";
								}

								echo "<td id=\"".$row->typeid.'-jitaprice" align=right class=jitaprice>';
								if(isset($cookieprice) && array_key_exists($row->typeid,$cookieprice) && is_numeric($cookieprice[$row->typeid])) {
									$price=$cookieprice[$row->typeid];
									echo $cookieprice[$row->typeid];
								} else {
									list($price,$pricebuy)=returnprice($row->typeid);
									echo $price;

								}

								echo "</td><td class=\"priceedit hidden\"><input style=\"text-align: right;\" type=text id=\"".$row->typeid."-priceedit\" align=right value=\"$price\" onchange=\"updateprice(".$row->typeid.")\" maxlength=10></td><td id=\"".$row->typeid."-jitasell\" class='hidden jitasell'>$price</td><td id=\"".$row->typeid."-jitabuy\" class='hidden jitabuy'>$pricebuy</td></tr>\n";
							}
							?>
						</table>
						<a name="editprice" onclick="toggleprice()">Edit prices</a>
						<a name="saveprice" onclick="saveprice()" class="priceedit hidden">save prices</a>
						<a href="" id="clearprice" class="priceedit hidden">reset prices</a>
					</div>
				</div>
			</div>

			<script type="text/javascript">
				var typeid=[<? echo $typeid?>];
				var dctypes=[<? echo $dctype?>];
				var typeide=[<? echo $typeide?>];
				var typetotal=[<? echo trim(trim($typeide.",".$typeid,",").",".$itemid,",")?>];
				var metatypes=new Array();
				<?php
				foreach( $metatypeid as $key=>$value) {
					echo "metatypes[$key] = $value;";
				}
				?>
				var allitems=[<? echo trim(trim($typeide.",".$typeid,",").",".$itemid.",".$dctype.",".$typeid2,",")?>];
				var itemid=<? echo $itemid ?>;
				var url='/blueprints/calc.php?bpid=<?= $itemid ?>';
				var linkurl='/blueprints/<?= $databasenumber ?>/ <?= $itemid ?>/';
				var xmlurl='/blueprints/xml/<?= $itemid ?>/';
				var xml2url='/blueprints/xml2/<?= $itemid ?>/';
				var staticurl='/blueprints/static/<?= $itemid ?>/';
			</script>

			<br/><br/>

			<div id="search" >
				<form method="post" action="/blueprints/calc.php" id="nextsearch">
					<input type="text" width="30" id="blueprintname" name="blueprintname"/>
					<input type="hidden" name="database" value="<?= $databasenumber ?>">
					<label for="newwindow">New Window?</label>
					<input type="checkbox" id="newwindow" value="1" onchange="if($('#newwindow').is(':checked')) { document.getElementById('nextsearch').target='_blank'; } else { document.getElementById('nextsearch').target='_self'; }">
					<input type=submit value="Do calculations" />
				</form>
			</div>
			<div id="inventiondialog" title="Invention Calculator">
				<form id="calculator" name="calculator">
					<table>
						<tr>
							<th>Base</th>
							<th>Encryption</th>
							<th>DC 1</th>
							<th>DC 2</th>
							<th>MetaItem</th>
							<th>Decryptor</th>
							<th>Chance</th>
						</tr>
						<tr>
							<td class="slidercell">
								<?php
								if(!isset($chance)) $chance=0;
								?>

								<input type=radio name="basechance" value=20 id="bc20" onchange="calculateresult();" <?php if($chance=="0.2") echo "checked";?>/>
								<label for="bc20">20%</label>
								<br/>
								<input type=radio name="basechance" value=25 id="bc25" onchange="calculateresult();" <?php if($chance=="0.25") echo "checked";?>/>
								<label for="bc25">25%</label>
								<br/>
								<input type=radio name="basechance" value=30 id="bc30" onchange="calculateresult();" <?php if($chance=="0.3") echo "checked";?>/>
								<label for="bc30">30%</label>
								<br/>
								<input type=radio name="basechance" value=40 id="bc40" onchange="calculateresult();" <?php if($chance=="0.4") echo "checked";?>/>
								<label for="bc40">40%</label>
							</td>

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
					<span class="note">Base chance is 20% for battlecruisers, battleships, Hulk</span><br/>
					<span class="note">Base chance is 25% for cruisers, industrials, Mackinaw</span><br/>
					<span class="note">Base chance is 30% for frigates, destroyers, Skiff, freighters</span><br/>
					<span class="note">Base chance is 40% for all other inventables</span><br/>
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

		<?php
		if(file_exists('/home/web/fuzzwork/htdocs/bootstrap/footer.php')) {
			include('/home/web/fuzzwork/htdocs/bootstrap/footer.php');
		}
		?>
	</body>
</html>
