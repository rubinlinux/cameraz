<?php

/* This file requires an apache hack, because php CANNOT access raw content when mime type is
 * multipart/form-data. Fix is to rewrite the header to form-data-alternate so php doesn't handle
 * it, and we just hacky-parse it here without a mime library.
 Add to apache config:
 <Location "/path/to/netbotz/php/files/oldnetbot.php">
     SetEnvIf Content-Type ^(multipart/form-data)(.*) NEW_CONTENT_TYPE=multipart/form-data-alternate$2   OLD_CONTENT_TYPE=$1$2
     RequestHeader set Content-Type %{NEW_CONTENT_TYPE}e env=NEW_CONTENT_TYPE
 </Location>
 */

include 'config.php';
//$myFile = $HTML_DIRECTORY."netcam.txt";
$myFile = "/tmp/netcam.txt";
$DEBUGON = true;

ob_start();

echo date("YmdHis")."\n";

if($DEBUGON) {
    //print_r($_SERVER);
    //print_r($_REQUEST);
    //$rawfh = fopen("/tmp/images.raw", "a") or die("Cant open image raw file");
    //fwrite($rawfh, $HTTP_RAW_POST_DATA);
    //echo($HTTP_RAW_POST_DATA);
}

$host = substr(gethostbyaddr($_SERVER['REMOTE_ADDR']),0,strpos(gethostbyaddr($_SERVER['REMOTE_ADDR']),'.'));
$timestamp = date("YmdHis");
$dataroot =  $BASE_DIRECTORY;
$hostroot = $dataroot.$host."/";
$uploaddir = $hostroot."upload/";

// time settings
$createtime = getdate(time());
$year = $createtime[year];
$month = sprintf("%02d",$createtime[mon]);
$day = sprintf("%02d",$createtime[mday]);
$time = sprintf("%02d%02d%02d",$createtime[hours],$createtime[minutes],$createtime[seconds]);
echo "host: $host\n";

$files = array();
if(strpos($_SERVER['REMOTE_ADDR'],'192.168.') !== FALSE) {

	if (!file_exists($dataroot.$host)) 
            mkdir($dataroot.$host);
	if (!file_exists($dataroot.$host."/upload")) 
            mkdir($dataroot.$host."/upload");

	if(strpos($host,'netbot') !== FALSE) { 
            echo "Found netbot $host!\n";
            // Start raw mime processing the hacky way that should be criminal.

            // break it up by mime header
            $raw = $HTTP_RAW_POST_DATA;
            $entries = preg_split("/\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-/", $raw );

            $c = 0;
            foreach($entries as $entry) {
                echo "Processing an entry:\n";
                //$lines = explode("\r\n", $entry);
                $lines = preg_split("/\r\n/", $entry);
                $id = array_shift($lines);
                if(strlen($id) < 1) 
                   continue;
            //  echo "Got ID '$id' (length: ". strlen($id).")\n";
                $contentdisposition = array_shift($lines);
            //  echo "Got content-line: $contentdisposition\n";
                if(preg_match('/filename\=\"([a-zA-Z1-9_.-]+\.jpg)\"/', $contentdisposition, $matches)) {
                    $jpegname = $matches[1]; // ignored
                    $c++;
                    $n = sprintf("%02d", $c);
                    $jpegname = "$image$time$n.jpg";

                    echo "GOT A FILENAME!! '$jpegname' -- YAY\n";
                    $mimetypeline = array_shift($lines);
            //      echo "Got mimetype line: $mimetypeline\n";
                    array_shift($lines); // empty line before binary
                    $jpg = implode('\r\n', $lines);
                    echo "Writing to $uploaddir$jpegname from ". count($lines) ." lines left\n";
                    $newjpeg = fopen("$uploaddir$jpegname", 'w');
                    fwrite($newjpeg, $jpg);
                    fclose($newjpeg);
                    $files[] = "$jpegname";
                }
                echo "\nDone processing it.\n";
                
            }
	}
}
else 
  echo "Submitted from non-private address.\n";

movefiles($files);
	
function movefiles($files) {
    global $hostroot, $uploaddir;
        $createtime = getdate(time());
        $year = $createtime[year];
        $month = sprintf("%02d",$createtime[mon]);
        $day = sprintf("%02d",$createtime[mday]);
        $time = sprintf("%02d%02d%02d",$createtime[hours],$createtime[minutes],$createtime[seconds]);
        if (!file_exists($hostroot.$year)) { 
            mkdir($hostroot.$year);
            chmod($hostroot.$year,0775);
        }

        if (!file_exists($hostroot.$year."/".$month)) {
            mkdir($hostroot.$year."/".$month);
            chmod($hostroot.$year."/".$month,0775);
        }
        if (!file_exists($hostroot.$year."/".$month."/".$day)) {
            mkdir($hostroot.$year."/".$month."/".$day);
            chmod($hostroot.$year."/".$month."/".$day,0775);
        }

        $destination = $hostroot.$year."/".$month."/".$day."/".$time;
        mkdir($destination);
        chmod($destination,0775);

        foreach($files as $value) {
            $filename = $value;
            echo $filename;
            if(rename("$uploaddir$filename","$destination/$timestamp$filename")) 
                echo " moved       successfully.\n";
            else 
                echo " was not moved.\n";
        }
}

// This debug file is not rotated, turn it off when your not using it!
if($DEBUGON) {
	$foo = ob_get_contents();
	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, $foo);
}
fclose($fh);
?>
