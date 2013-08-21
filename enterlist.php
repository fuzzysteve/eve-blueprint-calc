<? require_once('db.inc.php'); ?>
<html>
<head>
<title>Enter Blueprint List</title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<?php include('/home/web/fuzzwork/htdocs/bootstrap/header.php'); ?>
</head>
<body>
<?php include('/home/web/fuzzwork/htdocs/menu/menubootstrap.php'); ?>
<div class="container">


  
<p>Paste in a copy and paste from your blueprint list on the S&I interface, and get a page of links to the blueprint details. No details of your blueprints are stored on the server, so you'll have to do this each time you want to see them. The list will be condensed to a count</p>
<p>To get the list in a useful fashion, open the S&amp;I interface and go to the blueprints tab. open the relevant grey heading. Then double click it. you'll get a window you can C&amp;P from </p>
<p>The second submit button takes all your blueprints, and gives you a list of everything you can invent from them, without the use of decryptors.</p>
<form method=post action='bplist.php'>
<textarea id="entries" name='entries' rows=20 cols=120/>
</textarea><br />
<input type=submit value="Enter List as is" onclick="this.form.action='bplist.php'"/>
<input type=submit value="Enter List - T2 Invention" onclick="this.form.action='t2list.php'"/>
</form>

</div>
</div>
<?php include('/home/web/fuzzwork/htdocs/bootstrap/footer.php'); ?>
</body>
</html>
