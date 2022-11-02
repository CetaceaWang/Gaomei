<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>高美濕地木棧道開放時間</title>
	<link href="style.css" rel="stylesheet" type="text/css">
	<link href="calendar.css" rel="stylesheet" type="text/css">
</head>

<?
include "config.php";
include "data.php";
include "calendar.php";
set_data_updated();



?>
<body>
	    <nav class="navtop">
	    	<div>
	    		<h1>高美濕地木棧道開放時間表</h1>
	    	</div>
	    </nav>
		<div class="content home">
			<? echo display_calendar();?>
		</div>
	</body>
</html>