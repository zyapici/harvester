<?php
error_reporting(1);
set_time_limit(0);
if(isset($_REQUEST["custid"]))
    {
            $custid=$_REQUEST["custid"];
    }
if(isset($_REQUEST["username"]))
    {
            $username=$_REQUEST["username"];
    }
if(isset($_REQUEST["password"]))
    {
            $password=$_REQUEST["password"];
    }
if(isset($_REQUEST["extra"]))
    {
            $extra=$_REQUEST["extra"];
    }
    

     unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "uploading mrc file...", LOCK_EX);
    
    
$directory=$custid."/marc";
$ftpserver = "ftp.epnet.com"; 
$ftpuser = $username; 
$ftppass = $password; 

$count=0;

$zip = new ZipArchive();
$zip->open($directory."/marc.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

foreach(glob("{$directory}/*") as $file)
       {
            $zip->addFile($file, basename($file));
       }
      
      
  $directory=$custid."/extra";
if($extra==="YES")
{
        foreach(glob("{$directory}/*") as $file)
       {
            $zip->addFile($file, basename($file));
       }
}

$zip->close();


      
 if(filesize($custid."/marc/marc.zip")>0)
        {
            $kaynakdosya = $custid."/marc/marc.zip"; 
            
            $hedefdosya = "full/".basename($kaynakdosya); 
            $baglanti = ftp_connect($ftpserver); 
            $giris = ftp_login($baglanti, $ftpuser, $ftppass); 

            if ((!$baglanti) || (!$giris)) { 
                echo "Ftp bağlantısı sağlanamadı"; 
                die; 
            } 
            else { 
                echo "Ftp'ye bağlanıldı<br>"; 
            } 
	    ftp_pasv($baglanti, true);
            $yukle = ftp_put($baglanti,$hedefdosya, $kaynakdosya, FTP_BINARY);

            if (!$yukle) {
               echo "Dosya Yüklenemiyor"; 
            } 
            else 
            { 
                echo "Dosya Yüklendi"; 
            }
            ftp_close($baglanti); 
           
        }
      

    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrc file was uploaded", LOCK_EX);
 
    unlink($custid."/info/last_upload_date");
    $last_upload_date=$custid."/info/last_upload_date";
    $datetoday = date("m/d/Y");
    file_put_contents($last_upload_date, $datetoday, LOCK_EX);


 

?>