<html>
<body>
<script language="JavaScript">
var images = new Array();
<?php
include '../config.php';
$datadir = $DATA_DIRECTORY;
$bot=$_GET['bot'];
$camera=$_GET['camera'];
$year=$_GET['year'];
$month=$_GET['month'];
$day=$_GET['day'];
$event=$_GET['event'];
if(strpos($bot,"netbot") !== FALSE)
    $imagedir = "$bot/$camera/$year/$month/$day/$event";
else
    $imagedir = "$bot/$year/$month/$day/$event";
$images = scandir($datadir.$imagedir);
foreach($images as $image) {
        if(strstr($image,".jpg")) {
		echo "images.push('$image');\n";
	}
}
?>

function checkParent() {
	if(typeof parent.loadImages == 'function') parent.loadImages();
	else var t = setTimeout("checkParent()",25);
}

checkParent();

</script>
</body>
</html>
