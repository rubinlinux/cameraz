<?
include 'config.php';
?>

<html>
<head>
<title>Camera Event Viewer</title>
<link rel="shortcut icon" type="image/x-icon" href="https://monitor.physics.umn.edu/netbotz/favicon.ico">
<style type="text/css">
A { font:10pt arial;color:blue;text-decoration:none; }
A:hover { text-decoration:underline; }
.bot { padding-left:10px;padding-top:2px;padding-bottom:2px; }
.header { padding-top:2px;padding-bottom:3px;font:bold 10pt arial;text-align:center;background-color:#999999;color:white;width:100%; }
BUTTON { _vertical-align:middle; }
</style>
</head>
<body style="margin-top:10px;margin-bottom:0;">
<?php

$basedir = $BASE_DIRECTORY;
$imagedir = $IMAGE_BASE_DIRECTORY;
$dirs = scandir($basedir);
$numbotz = 0;
$botz = array();
foreach($dirs as $botname) {
	if(strpos($botname,".") !== 0 && is_dir($basedir.$botname)) {
        $botz[$botname]['cameras'] = array();
        if(strpos($botname,"xxxxxxxnetbot") !== FALSE) {
            $botdirs = scandir($basedir.$botname);
            foreach($botdirs as $camera) {
                if(strpos($camera,"nb") !== FALSE && is_dir("$basedir$botname/$camera")) {
                    $botz[$botname]['cameras'][$camera] = array("dir" => "$basedir$botname/$camera", "imagedir" => "$imagedir$botname/$camera");
                    $cameradirs = scandir("$basedir$botname/$camera");
                    foreach($cameradirs as $file) {
                        if($file == "description") {
                            $fh = fopen("$basedir$botname/$camera/$file", 'r');
                            $botz[$botname]['cameras'][$camera]['description'] = fread($fh, 100);
                            fclose($fh);
                        }
                    }
                    $numbotz++;
                }
            }
        } else {
            $numbotz++;
            $botz[$botname]['cameras'][] = array("dir" => $basedir.$botname, "imagedir" => "$imagedir$botname");
        }
	}
}

$botname = $_GET['bot'];
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$cameraname = $_GET['camera'];

if(!$botname || !isset($botz[$botname])) {
    $botname = $DEFAULT_BOTNAME;
    $cameraname = $DEFAULT_CAMERANAME;
    $bot = $botz[$botname];
}
else $bot = $botz[$botname];

if(!$cameraname || !isset($bot['cameras'][$cameraname])) {
    $cameraname = 0;
}

$camera = $bot['cameras'][$cameraname];

if($year && is_dir($camera['dir']."/$year")) {
	if($month && is_dir($camera['dir']."/$year/$month")) {
		if($day && is_dir($camera['dir']."/$year/$month/$day")) {
			if(!$event || !is_dir($camera['dir']."/$year/$month/$day/$event")) $event = "";
        }
        else $day = "";
	}
	else $month = "";
}
else {
	$year = date(Y);

	if(is_dir($camera['dir']."/$year/".date(m))) $month = date(m);
	else $month = "";

	if(is_dir($camera['dir']."/$year/".date(m)."/".date(d))) $day = date(d);
	else $day = "";

	$event = "";
}

$botlistheight=$numbotz*20+48;

echo '<CENTER><TABLE style="height:100%;" cellspacing="0" cellpadding="0"><TR><TD>';
echo "<DIV id=\"botlist\" style=\"padding-bottom:2px;width:175px;\">\n";
echo "<DIV class=header>Current Camera</DIV>\n";

foreach($botz as $name => $current_bot) {
    foreach($current_bot['cameras'] as $cam => $current_cam) {
        if($name == $botname && $cam == $cameraname)
            echo "<DIV class=bot><a href=\"index.php?bot=$name&camera=$cam\" style=\"font-weight:bold;color:red\">$name";
        else
            echo "<DIV class=bot><a href=\"index.php?bot=$name&camera=$cam\">$name";
        if(isset($current_cam['description'])) echo " (".$current_cam['description'].")";
        echo "</a></DIV>\n";
    }
}

echo "<DIV class=header>Current Event</DIV>\n";
echo "</DIV>\n";
echo "<DIV id=\"eventlist\" style=\"margin-left:15;width:160px;overflow:auto;\">\n";

if(strlen($camera['dir']) < 1) {
    echo "ERROR: Camera directory empty string!\n<BR>";
    exit;
}
$years = scandir($camera['dir']);
$eventnum = 0;

foreach($years as $curyear) {
    if(strpos($curyear,".") !== 0 && strpos($curyear,"upload") === FALSE && strpos($curyear,"description") === FALSE) {
        printLine($curyear,$year,"index.php?bot=$botname&camera=$cameraname&amp;year=$curyear",0);
        if($curyear == $year) {
            $months = scandir($camera['dir']."/".$year);
            if($month == "") $month = $months[2];
            foreach($months as $curmonth) {
                if(strpos($curmonth,".") !== 0) {
                    printLine($curmonth,$month,"index.php?bot=$botname&camera=$cameraname&amp;year=$curyear&amp;month=$curmonth",4);
                    if($curmonth == $month) {
                        $days = scandir($camera['dir']."/".$year."/".$month);
                        if($day == "") $day = $days[2];
                        foreach($days as $curday) {
                            if(strpos($curday,".") !== 0) {
                                printLine($curday,$day,"index.php?bot=$botname&camera=$cameraname&amp;year=$curyear&amp;month=$curmonth&amp;day=$curday",8);
                                if($curday == $day) {
                                    $events = scandir($camera['dir']."/".$year."/".$month."/".$day);
                                    if($event == "") $event = $events[2];
                                    foreach($events as $curevent) {
                                        if(strpos($curevent,".") !== 0) {
                                            $eventnum++;
                                            $time = mktime(
                                                        substr($curevent,0,2),
                                                        substr($curevent,2,2),
                                                        substr($curevent,4,2),
                                                        $curmonth,
                                                        $curday,
                                                        $curyear
                                                    )*1000;
                                            printEvent($time,$curevent,$event,12);
}}}}}}}}}}}

if($event) $time = mktime(substr($event,0,2),substr($event,2,2),substr($event,4,2),$month,$day,$year)*1000;
else $time = '';

echo <<<END

</DIV></TD>

<TD style="text-align:center;padding-top:2px;padding-left:10px;padding-right:10px;">

<DIV style="font:bold 10pt arial;">
<span style="background-color:#999999;color:white;padding-left:10px;padding-top:2px;padding-right:10px;padding-bottom:3;">Current Event Time</span>
<span id="eventtime" style="padding-left:10px;"></span>
</DIV>

<DIV style="display:table;padding-top:10px;height:480px;width:640px;text-align:center;_line-height:480px;_font-size:480px;">
<DIV id="imagediv" style="display: table-cell;vertical-align:middle;_margin-top:-28px;_margin-bottom:-25px;">
</DIV></DIV>

<DIV id="buttons" style="padding-top:2;font:bold 10pt arial;">Event&nbsp;
<button style="_margin-left:5px;_width:25px;_font-weight:bold;" title="Go to previous event" onClick="prevEvent()"><</button>&nbsp;/
<button style="_margin-left:5px;_width:25px;_font-weight:bold;" title="Go to next event" onClick="nextEvent();">></button>&nbsp;&nbsp;Image&nbsp;
<button style="_margin-left:5px;_width:25px;_font-weight:bold;" title="Previous Image" onClick="prevImage();"><</button>&nbsp;/
<button style="_margin-left:5px;_width:25px;_font-weight:bold;" title="Next Image" onClick="nextImage();">></button>&nbsp;&nbsp;Playback&nbsp;
<button style="_margin-left:5px;_font-weight:bold;" title="Resume video" onClick="showImage(activeImageNum);">Play</button>&nbsp;&nbsp;
<button style="_font-weight:bold;" title="Pause video" onClick="clearTimeout(t);">Pause</button>&nbsp;&nbsp;Speed&nbsp;
<button style="_margin-left:5px;_width:25px;_font-weight:bold;" title="Slow down video" onClick="slowDown();">-</button>&nbsp;/
<button style="_margin-left:5px;_width:25px;_font-weight:bold;" title="Speed up video" onClick="speedUp();">+</button>&nbsp;
<button style="_font-weight:bold;" title="Default speed" onClick="resetTimeout();">Normal</button>
</DIV>

</TD>

<TD>

<DIV id=imagesheader style="font:bold 10pt arial;width:140px;color:white;text-align:center;padding-top:2px;padding-bottom:3px;background-color:#999999;">Event Images</DIV>
<DIV id=imageslist style="margin-top:2px;width:140px;overflow:auto;height:510px;"></DIV>

</TD>
</TR></TABLE></CENTER>

<DIV style="display:none;">
<IFRAME name=imagelist id=imagelist src="imagelist.php?bot=$botname&camera=$cameraname&amp;year=$year&amp;month=$month&amp;day=$day&amp;event=$event"></IFRAME>
</DIV>

END;
?>

<SCRIPT language="JavaScript" type="text/javascript">

var activeEvent = "event<?php echo $event ?>";
var activeEventTime = new Date();
activeEventTime.setTime(<?php echo $time ?>);
document.getElementById("eventtime").innerHTML = activeEventTime.toString();
var imageBaseURL = "<?php echo $camera['imagedir']."/$year/$month/$day/$event/"; ?>";
var numImagesComplete = 0;
var images = new Array();
var activeImageNum = 0;
var t;
var imageDiv = document.getElementById('imagediv');
var eventImage = document.createElement('img');
var loadingImage = document.createElement('img');
loadingImage.src = "images/loading.gif";
imageDiv.appendChild(loadingImage);
eventImage.onClick = "copy(this.src)";
eventImage.setAttribute("title", "Click to copy image URL to clipboard");
eventImage.setAttribute("alt", "Event Image");
eventImage.setAttribute("width", "640px");
eventImage.style.verticalAlign = "middle";
var imageListHTML = "";
var timeout = 200;

function loadImages() {
	numImagesComplete = 0;
	imageListHTML = "<center>";
	images = new Array();
	for(var i = 0; i < parent.imagelist.images.length; i++) {
		images[i] = new Image();
		images[i].onLoad = imageComplete();
		images[i].src = imageBaseURL+parent.imagelist.images[i];
		imageListHTML += "<a href=\"javascript:changeImage("+i+")\"><img alt=\""+parent.imagelist.images[i]+"\" id=\""+parent.imagelist.images[i]+"\" style=\"border-color:white;border-width:3;width:100px;\" src=\""+imageBaseURL+parent.imagelist.images[i]+"\"><\/a><br>\n";
	}
	imageListHTML += "<\/center>\n";
	document.getElementById('imageslist').innerHTML = imageListHTML;
}

function imageComplete() {
	numImagesComplete++;
	if(numImagesComplete == parent.imagelist.images.length) {
		imageDiv.replaceChild(eventImage, loadingImage);
		showImage(0);
	}
}

function showImage(num) {
	if(num == images.length) num = 0;
	changeImage(num);
	t = setTimeout("showImage("+(num+1)+")",timeout);
}

function nextImage() {
	if(activeImageNum == images.length - 1) num = 0;
	else num = activeImageNum + 1;
	changeImage(num);
}

function prevImage() {
	if(activeImageNum == 0) num = images.length - 1;
	else num = activeImageNum - 1;
	changeImage(num);
}

function changeImage(num) {
	clearTimeout(t);
	if(document.getElementById(parent.imagelist.images[num])) {
		document.getElementById(parent.imagelist.images[num]).style.borderColor = "red";
		document.getElementById(parent.imagelist.images[activeImageNum]).style.borderColor = "white";
	}
	eventImage.src = images[num].src;
	activeImageNum = num;
}

function copy(text2copy) {
	if (window.clipboardData) window.clipboardData.setData("Text",text2copy);
	else {
		var divholder = document.createElement('div');
		divholder.id = 'flashcopier';
		document.body.appendChild(divholder);
		document.getElementById('flashcopier').innerHTML = '<embed src="images/_clipboard.swf" FlashVars="clipboard='+escape(text2copy)+'" width="0" height="0" type="application/x-shockwave-flash"><\/embed>';
	}
}

function setSize() {
	document.getElementById('eventlist').style.height = 533 - document.getElementById('botlist').clientHeight + "px";
	document.getElementById('eventlist').scrollTop = document.getElementById(activeEvent).offsetTop - document.getElementById('eventlist').offsetTop - 25;
}

function changeEvent(newEvent,eventTime) {
	clearTimeout(t);
	imageDiv.replaceChild(loadingImage, eventImage);
	activeEventTime.setTime(eventTime);
	document.getElementById(activeEvent).style.fontWeight = "normal";
	document.getElementById(activeEvent).style.color = "blue";
	document.getElementById(newEvent).style.fontWeight = "bold";
	document.getElementById(newEvent).style.color = "red";
	document.getElementById('eventtime').innerHTML = activeEventTime.toString();
	document.getElementById('imagelist').src = "<?php echo "imagelist.php?bot=$botname&camera=$cameraname&year=$year&month=$month&day=$day&event="; ?>"+newEvent.substring(5);
	activeEvent = newEvent;
	imageBaseURL = "<?php echo $camera['imagedir']."/$year/$month/$day/"; ?>"+newEvent.substring(5)+"/";
}

function nextEvent() {
	var newEvent = document.getElementById(activeEvent).nextSibling;
	if(newEvent.nextSibling) {
		newEvent = newEvent.nextSibling;
		if(newEvent.nextSibling) {
			newEvent = document.getElementById(activeEvent).nextSibling.nextSibling.nextSibling;
			window.location = newEvent.href;
		}
	}
}

function prevEvent() {
	var newEvent = document.getElementById(activeEvent).previousSibling.previousSibling.previousSibling;
	if(newEvent.href.indexOf("javascript") == 0) window.location = newEvent.href;
	else window.location = newEvent.previousSibling.previousSibling.previousSibling.href;
}

function slowDown() {
	timeout = timeout+100;
}

function speedUp() {
	timeout = (timeout > 100 ? timeout-100 : 100);
}

function resetTimeout () {
	timeout = 200;
}

setSize();

</SCRIPT>

<?php

function printLine($curvar, $var, $link, $depth) {
	for($i=0;$i<$depth;$i++) echo "&nbsp;";
	if($curvar == $var) {
		echo "<a href=\"$link\" style=\"font-weight:bold\">$curvar</a><BR>\n";
	}
	else echo "<a href=\"$link\">$curvar</a><BR>\n";
}

function printEvent($time, $curvar, $var, $depth) {
	for($i=0;$i<$depth;$i++) echo "&nbsp;";
	if($curvar == $var) {
		echo "<a id=event$curvar href=\"javascript:changeEvent('event$curvar','$time')\" style=\"color:red;font-weight:bold\">".substr($curvar,0,2).":".substr($curvar,2,2).":".substr($curvar,4,2)."</a><BR>\n";
	}
	else echo "<a id=event$curvar href=\"javascript:changeEvent('event$curvar','$time')\">".substr($curvar,0,2).":".substr($curvar,2,2).":".substr($curvar,4,2)."</a><BR>\n";
}

?>
</body>
</html>
