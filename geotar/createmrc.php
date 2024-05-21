<?php
//error_reporting(0);
set_time_limit(0);

if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}

if(isset($_REQUEST["publisher"]))
{
	$publisher=$_REQUEST["publisher"];
}




unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, " creating mrc file...", LOCK_EX);
    
$cmd="\"C:\Program Files\MarcEdit 6\cmarcedit.exe\" -utf8 -make -s \"c:\wamp64\www\\geotar\\".$custid."\mrk\\".$publisher.".mrk\" -d \"c:\wamp64\www\\geotar\\".$custid."\marc\\".$publisher."_".date('dmY').".mrc\"";

echo $cmd;
shell_exec($cmd);

unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrc file was created", LOCK_EX);
