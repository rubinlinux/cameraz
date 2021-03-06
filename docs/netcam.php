<?php
include '../config.php';
//$myFile = $HTML_DIRECTORY."netcam.txt";
$myFile = "/tmp/netcam.txt";
$DEBUGON = true;

ob_start();

echo date("YmdHis")."\n";

if($DEBUGON) {
    print_r($_SERVER);
    print_r($_REQUEST);
    $rawfh = fopen("/tmp/images.raw", "a") or die("Cant open image raw file");
    fwrite($rawfh, $HTTP_RAW_POST_DATA);
    //echo($HTTP_RAW_POST_DATA);
}

$host = substr(gethostbyaddr($_SERVER['REMOTE_ADDR']),0,strpos(gethostbyaddr($_SERVER['REMOTE_ADDR']),'.'));
$timestamp = date("YmdHis");
$dataroot =  $BASE_DIRECTORY;
$hostroot = $dataroot.$host."/";
$uploaddir = $hostroot."upload/";
echo "host: $host\n";

if(strpos($_SERVER['REMOTE_ADDR'],'192.168.') !== FALSE) {

	if (!file_exists($dataroot.$host)) 
            mkdir($dataroot.$host);
	if (!file_exists($dataroot.$host."/upload")) 
            mkdir($dataroot.$host."/upload");

//	if(strpos($host,'netcam') !== FALSE) { 
		echo "Found netcam $host!\n";
		$disposition =  $_SERVER['HTTP_CONTENT_DISPOSITION'];
		$jpegname = substr($disposition,strpos($disposition,"\"")+1,-1);
		if($jpegname) {
			$raw = $HTTP_RAW_POST_DATA;
			$newjpeg = fopen("$uploaddir$jpegname", 'w');
			fwrite($newjpeg, $HTTP_RAW_POST_DATA);
			fclose($newjpeg);
		}
//	}
}
else echo "Submitted from non-private address.\n";
	
// This debug file is not rotated, turn it off when your not using it!
if($DEBUGON) {
	$foo = ob_get_contents();
	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, $foo);
}
fclose($fh);
?>
