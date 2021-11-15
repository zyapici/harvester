<?php

set_time_limit(0);
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
   
    foreach(glob("{$directory}/*") as $file)
    {
        if(is_dir($file)) { 
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
    }
    
    if($filedir=="download" || $filedir=="upload" )
    {
       $directorymrk=$custid."/".$filedir."_mrk";

            foreach(glob("{$directorymrk}/*") as $file)
            {
                if(is_dir($file)) { 
                    recursiveRemoveDirectory($file);
                } else {
                    unlink($file);
                }
            }
    }
    
    
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, $filedir." files were deleted.", LOCK_EX);
   