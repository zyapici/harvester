<?php
error_reporting(0);
set_time_limit(0);
if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}
unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "creating mrk file...", LOCK_EX);
    
    
  
    
$myDirectory = opendir($custid."/download/");
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}
closedir($myDirectory);
$indexCount = count($dirArray);

    
for($i=0;$i<$indexCount;$i=$i+1)
{
    if (substr("$dirArray[$i]", 0, 1) != ".")
    { 
        $cmd="\"C:\Program Files\MarcEdit 6\cmarcedit.exe\" -utf8 -break -s \"c:\wamp64\www\addingfolioid\\".$custid."\download\\".$dirArray[$i]."\" -d \"c:\wamp64\www\addingfolioid\\".$custid."\download_mrk\\".$dirArray[$i].".mrk\"";
        shell_exec($cmd);
    }
}
unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrk file was created.", LOCK_EX);
