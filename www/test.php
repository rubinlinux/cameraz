<?php
echo "<PRE>";

echo "Hello,\n";
$raw = file_get_contents("/tmp/images.raw.example1");

echo "Length of read string is ". strlen($raw). "\n";

$entries = preg_split("/\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-/", $raw );

foreach($entries as $entry) {
    echo "Processing an entry:\n";
    $lines = explode("\r\n", $entry);
    $id = array_shift($lines);
    if(strlen($id) < 1) 
       continue;
//    echo "Got ID '$id' (length: ". strlen($id).")\n";
    $contentdisposition = array_shift($lines);
//    echo "Got content-line: $contentdisposition\n";
    if(preg_match('/filename\=\"([a-zA-Z1-9_.-]+)\"/', $contentdisposition, $matches)) {
        $filename = $matches[1];
        echo "GOT A FILENAME!! '$filename' -- YAY\n";
        $mimetypeline = array_shift($lines);
//        echo "Got mimetype line: $mimetypeline\n";
        array_shift($lines); // empty line before binary
        $jpg = implode('\r\n', $lines);
        echo "Writing to /tmp/$filename from ". count($lines) ." lines left\n";
        $fh = fopen("/tmp/$filename", "w");
        fwrite($fh, $jpg);
        fclose($fh);
    }
    /*
    $lineno = 0;
    foreach($lines as $line) {
        echo "DEBUG LINE $lineno: $line\n";
        $lineno++;
    }
    print_r($entry);
    */
    echo "\nDone processing it.\n";
    
}

echo "got ". count($entries). " entries\n";
echo "\n\n<pre style=\"font-family: monospace;\">";
$c = 0;
$hex = '';
$str = '';
for($i = 0; $i < strlen($raw); $i++) {
  $c++;
  $hex .= sprintf("%02X", (ord($raw[$i])+0));
  // print char, or '.' for binary values
  $color = "#FFF";
  if($raw[$i] == "\r")
    $color = "#bba";
  elseif($raw[$i] == "\n")
    $color = "#bab";
  if(preg_match('/^[]a-zA-Z0-9^!@#$%&*().,?\/\\|-]$/', $raw[$i])) {
      $str .= "<span style=\"background: $color;\">".htmlspecialchars($raw[$i])."</span>";
  } 
  else {
      $str .= "<span style=\"background: $color;\">.</span>";
  }
  // space out every 2 chars
  if(($c+1) % 2) {
    $hex .= " ";
    $str .= "";
  }
  if($c >= 20) {
    // print the line ending and start a new line
    echo "$str - $hex\n";
    $str = ''; 
    $hex = '';
    $c = 0;
  }
  //echo dechex(ord($raw[$i]));
}
echo htmlspecialchars($raw);
echo "</pre>";
?>
