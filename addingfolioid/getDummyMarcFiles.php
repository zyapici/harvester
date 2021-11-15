<?php

set_time_limit(0);
if(isset($_REQUEST["custid"]))
    {
            $custid=$_REQUEST["custid"];
    }
if(isset($_REQUEST["dummyftp"]))
    {
            $dummyftp=$_REQUEST["dummyftp"];
    }
if(isset($_REQUEST["password"]))
    {
            $password=$_REQUEST["password"];
    }

    
    
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting marc records from dummy ftp...", LOCK_EX);

    

$ftpserver = "ftp.epnet.com"; 
$ftpuser = $dummyftp; 
$ftppass = $password;

    
            
           $loadcommand="update.mrc";
            $connection = ftp_connect($ftpserver); 
			
            $login = ftp_login($connection, $ftpuser, $ftppass); 
            ftp_pasv($connection, true);
            $ftp_nlist = ftp_nlist($connection, ".");
           
            
			
            foreach ($ftp_nlist as $file)
            {
                if(strpos($file,".mrc") || strpos($file,".marc"))
                {
                    //echo $file;
                    $loadcommand=$file;
                    $local_file = $custid."/download/".$loadcommand;
                    $local_file2 = $custid."/downloadbackup/".$loadcommand.date("Y.m.d").".mrc";
                    $server_file = $loadcommand;

                    $load = ftp_get($connection, $local_file, $server_file, FTP_BINARY);
                    $load2 = ftp_get($connection, $local_file2, $server_file, FTP_BINARY);
                     
                    
                    if (!$load) {
                       echo "not downloaded"; 
                    } 
                    else 
                    { 
                        echo "Downloaded"; 
                        ftp_delete($connection, $server_file);
                    }
            //break;
                }
            }
            
            

            ftp_close($connection); 
            
            
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting marc records from dummy ftp is completed.", LOCK_EX);