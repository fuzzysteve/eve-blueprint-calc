<? require_once('db.inc.php'); ?>
<html>
<head>
<title>Enter Blueprint List</title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  
</head>
<body>
<p>Paste in a copy and paste from your blueprint list on the S&I interface, and get a page of links to the blueprint details. No details of your blueprints are stored on the server, so you'll have to do this each time you want to see them. The list will be condensed to a count</p>

<form method=post action='bplist.php'>
<textarea id="entries" name='entries' rows=20 cols=60/>
</textarea><br />
<input type=submit value="Enter List" />
</form>
<?php include('/home/web/fuzzwork/analytics.php'); ?>
</body>
</html>
