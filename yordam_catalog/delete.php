<?php
set_time_limit(1000000);
if(isset($_REQUEST["file"]))
{
	$filedir=$_REQUEST["file"];
}
if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}
$directory=$custid."/".$filedir;

    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "deleting ".$filedir." files...", LOCK_EX);
    if($filedir=="log")
    {
        copy($custid."/log/number_of_records_ILS", $custid."/info/last_number_of_records_ILS");
        copy($custid."/log/number_of_records_Loaded", $custid."/info/last_number_of_records_Loaded");
    }
    foreach(glob("{$directory}/*") as $file)
    {
        if(is_dir($file)) { 
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
    }

    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, $filedir." files were deleted.", LOCK_EX);
   