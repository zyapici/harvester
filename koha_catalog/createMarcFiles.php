<?php
	set_time_limit(1000000);
    date_default_timezone_set('Europe/Istanbul');
    
    if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
                
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "creating marcxml file...", LOCK_EX);
    
$myDirectory = opendir($custid."/xml/");
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}
closedir($myDirectory);
$indexCount = count($dirArray);

$fileallxml=$custid."/marc/allmark(records).xml";

    
 file_put_contents($fileallxml, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", FILE_APPEND | LOCK_EX);
 file_put_contents($fileallxml, "\r\n<collection>", FILE_APPEND | LOCK_EX);
 
$totalLoaded=0;
for($i=0;$i<$indexCount;$i=$i+1)
{
  if (substr("$dirArray[$i]", 0, 1) != ".")
  {
    $doc = new DOMDocument();
    $doc->load($custid."/xml/".$dirArray[$i]);
    $allrecords = $doc->getElementsByTagName("record");
    foreach($allrecords as $record)
    {
        $marcxml = $record->getElementsByTagName("marcxml");
        if(isset($marcxml->item(0)->nodeValue) && strpos($marcxml->item(0)->nodeValue,"</record>"))
        {
        $marcxmlvalue = $marcxml->item(0)->nodeValue;
        $marcxmlvalue = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "", $marcxmlvalue);
         file_put_contents($fileallxml, $marcxmlvalue, FILE_APPEND | LOCK_EX);
        $totalLoaded=$totalLoaded+1;
        }

       
    }
  }
  
}
     file_put_contents($fileallxml, "</collection>", FILE_APPEND | LOCK_EX);

      $filenumberofLoaded=$custid."/log/number_of_records_Loaded";
          file_put_contents($filenumberofLoaded, $totalLoaded, FILE_APPEND | LOCK_EX);
          
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "marcxml file was created.", LOCK_EX);
	