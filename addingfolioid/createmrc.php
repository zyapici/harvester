<?php
error_reporting(0);
set_time_limit(0);
if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}
unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "creating mrc file...", LOCK_EX);
    
    
$myDirectory = opendir($custid."/upload_mrk/");
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}
closedir($myDirectory);
$indexCount = count($dirArray);

for($i=0;$i<$indexCount;$i=$i+1)
{
    if (substr("$dirArray[$i]", 0, 1) != ".")
    { 
        $cmd="\"C:\Program Files\MarcEdit 6\cmarcedit.exe\" -utf8 -make -s \"c:\wamp64\www\addingfolioid\\".$custid."\upload_mrk\\".$dirArray[$i]."\" -d \"c:\wamp64\www\addingfolioid\\".$custid."\upload\\".$dirArray[$i].".mrc\"";
        shell_exec($cmd);
    }
}
unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrc file was created.", LOCK_EX);
