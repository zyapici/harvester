<?php
error_reporting(0);
set_time_limit(1000000);
if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}
unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "creating mrc file...", LOCK_EX);
    
$cmd="\"C:\Program Files\MarcEdit 6\cmarcedit.exe\" -utf8 -make -s \"c:\wamp64\www\dogusharvesting\\".$custid."\marc\allmark(records).mrk\" -d \"c:\wamp64\www\dogusharvesting\\".$custid."\marc\allmark(records).mrc\"";
shell_exec($cmd);

unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrc file was created", LOCK_EX);
