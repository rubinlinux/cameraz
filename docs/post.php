<?php

include '../config.php';
//$myFile = $HTML_DIRECTORY."camera.txt";
$myFile = "/tmp/camera.txt";
$DEBUGON = true;

ob_start();

echo date("YmdHis")."\n";

#if($DEBUGON) {
#    print "TEST";
#    print_r($_REQUEST);
#    print_r($_SERVER);
#    //$postdata = file_get_contents("php://input");
#    $postdata = $HTTP_RAW_POST_DATA;
#    echo "Raw post data:\n";
#    print_r($postdata);
#    echo "\nEnd raw data.\n";
#}

$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$host = substr($host,0,strpos($host,'.'));
$timestamp = date("YmdHis");
$dataroot = $BASE_DIRECTORY;
$uploaddir = "$dataroot$host/upload/";
$found = "";
$files = FALSE;
$type = "";
$createtime = "";
$cameraname = "";
$cameradesc = "";
$xmlfilename = "";
$data = "";

//Detect if submitted from private address
//if((strpos($_SERVER['REMOTE_ADDR'],'192.168.') !== FALSE or $_SERVER['REMOTE_ADDR'] == '128.101.221.139' ) && strpos($host,'netbot') !== FALSE) {
//if((strpos($_SERVER['REMOTE_ADDR'],'192.168.') !== FALSE || $_SERVER['REMOTE_ADDR'] == '128.101.220.229' ) && strpos($host,'netbot') !== FALSE) {
if(strpos($host, 'netbot') !== FALSE || strpos($host, 'snark') !== FALSE) {

    //Create upload directory if missing
    if (!file_exists(dirname($uploaddir))) {
       print "Trying to make $uploaddir\n";
       mkdir(dirname($uploaddir));
    }
    if (!file_exists($uploaddir)) {
       print "Trying to make $uploaddir\n";
       mkdir($uploaddir);
    }


    //Iterate through attached files to find XML file
    foreach($_FILES as $key => $value) {
		if(strpos($value['name'],'xml')>0) {
			echo "Found XML file $value[tmp_name]!\n";
			$xmlfilename = $value['name'];
			if (move_uploaded_file($value['tmp_name'],$uploaddir.$xmlfilename)) {
				echo " XML file moved successfully.\n";
				chmod($uploaddir.$xmlfilename,0664);
				$xml_parser = xml_parser_create();
				xml_set_element_handler($xml_parser, "startElement", "endElement");
				xml_set_character_data_handler($xml_parser, "characterData");
				if(!($fp = fopen($uploaddir.$xmlfilename, 'r'))) {
					echo "could not open XML input $xmlfilename";
					break;
				}
				while (!feof($fp)) {
                    $data .= fread($fp, 4096);
                }
                $data = str_replace("&apos;","",$data);
                if (!xml_parse($xml_parser, $data, TRUE)) {
                    echo sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($xml_parser)),
                    xml_get_current_line_number($xml_parser));
                }
                xml_parser_free($xml_parser);
			} else {
				echo " XML file was not moved.\n";
			}
		}	
	}
	
}
elseif(strpos($_SERVER['REMOTE_ADDR'],'192.168.') !== FALSE && strpos($host,'netbot') === FALSE && strpos($host, 'snark') === FALSE) {
	echo "Invalid host name $host\n";
	return cleanup();
}
else {
	echo "Submitted from non-private address.\n";
	return cleanup();
}

//Check for valid XML file and successful parsing
if(!$xmlfilename) {
    echo "ERROR: No XML file found!\n";
    return cleanup();
}
if(!$cameraname) {
    echo "ERROR: Failed to parse camera name from XML file!\n";
    return cleanup();
}

$hostroot = "$dataroot$host/$cameraname/";

//Create hostroot directory if missing
if (!file_exists($hostroot)) mkdir($hostroot);

//Write camera description to file
if($cameradesc) {
    $fp = fopen($hostroot."description",'w');
    fwrite($fp, $cameradesc);
}

if($type == "return") {
	if(unlink($uploaddir.$xmlfilename)) echo "Return To Normal XML file deleted successfully.\n";
	else echo "Could not delete Return To Normal XML file.\n";
}
elseif($type == "test") {
	if(unlink($uploaddir.$xmlfilename)) echo "Test Alert XML file deleted successfully.\n";
	else echo "Could not delete Test Alert XML file.\n";
}
elseif($type == "humidity") {
	if(unlink($uploaddir.$xmlfilename)) echo "Humidity Alert XML file deleted successfully.\n";
	else echo "Could not delete Humidity Alert XML file.\n";
}
elseif($type == "door") {
	if(unlink($uploaddir.$xmlfilename)) echo "Door Switch XML file deleted successfully.\n";
	else echo "Could not delete Door Switch XML file.\n";

	$link = mysql_connect('mysql', 'netbotz', '!netb0tz')
	    or die('Could not connect: ' . mysql_error());
	echo "Connected successfully!\n";
	mysql_select_db('netbotz') or die('Could not select database');

	// Performing SQL query
	$query = "CREATE TABLE IF NOT EXISTS `$hostname` LIKE `test`";
	$result = mysql_query($query) or die('Create query failed: ' . mysql_error());
	$query = "INSERT INTO `$hostname` () VALUES()";
	$result = mysql_query($query) or die('Insert query failed: ' . mysql_error());
	echo "Inserted timestamp!\n";

	// Closing connection
	mysql_close($link);
}
elseif($type == "motion") {
    if($files) {
        $createtime = getdate($createtime);
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

        foreach($_FILES as $key => $value) {
            $filename = $value['name'];
            echo $filename;
            if (strpos($filename,"xml") > 0) {
                if(rename("$uploaddir$filename","$destination/$timestamp$filename")) echo " moved successfully.\n";
                else echo " was not moved.\n";
            }
            elseif (move_uploaded_file($value['tmp_name'],$destination."/".$timestamp.$filename)) {
                echo " moved successfully.\n";
                chmod($destination."/".$timestamp.$filename,0664);
            } else {
                echo " was not moved.\n";
            }
        }
    } else {
        echo "No files were sent with XML file $xmlfilename! Leaving in upload directory for debugging...\n";
    }
}
else {
	echo "No known types found in XML file $xmlfilename! Leaving in upload directory for debugging...\n";
}

cleanup();

function cleanup() {
	global $myFile, $DEBUGON;
	if($DEBUGON) {
		$foo = ob_get_contents();
		$fh = fopen($myFile, 'a') or die("can't open file");
		fwrite($fh, $foo);
		fclose($fh);
	}
}

function startElement($parser, $name, $attrs)
{
	global $found,$type,$files;
	if($name == "NLS-STRING-VAL" && $type == "") {
		foreach($attrs as $key => $value) {
			if($value == "Return To Normal") {
				$type = "return";
				echo "Found \"Return To Normal!\"\n";
			}
			elseif(strpos($value,"Test Alert")) {
				$type = "test";
				echo "Found \"Test Alert!\"\n";
			}
			elseif(strpos($value,"Door Switch")) {
				$type = "door";
				echo "Found \"Door Switch!\"\n";
			}
			elseif(strpos($value,"Humidity")) {
				$type = "humidity";
				echo "Found \"Humidity Alert!\"\n";
			}
			elseif(strpos($value,"Camera Motion")) {
				$type = "motion";
				echo "Found \"Camera Motion Alert!\"\n";
			}
		}
	}
	elseif($name == "STRUCT-ELEMENT") {
		foreach($attrs as $key => $value) {
			if($value == "numfiles") $files = TRUE;
			else $found = $value;
		}
	}
}

function endElement($parser, $name)
{
	global $found;
	$found = "";
}

function characterData($parser, $data) 
{
	global $found,$hostname,$createtime,$cameraname,$cameradesc;
	if($found == "hostname" && strlen(ltrim($data))>0) {
		echo "Found NetBot $data!\n";
		$hostname = $data;
	}
	elseif($found == "createtime" && strlen(ltrim($data))>0) {
        echo "Found createtime $data!\n";
		$createtime = $data;
	}
	elseif($found == "cameralist" && strlen(ltrim($data))>0) {
        echo "Found camera name $data!\n";
		$cameraname = $data;
	}
    elseif(strpos($data,"picture sequence")) {
        $startpos = strpos($data,"(")+1;
        $endpos = strpos($data,")");
        if($startpos !== FALSE && $endpos !== FALSE) {
            $cameradesc = substr($data,$startpos,$endpos-$startpos);
            echo "Found camera description '$cameradesc'!\n";
        }
        else {
            echo "Camera has no description";
        }
    }
}

#phpinfo();

?>
