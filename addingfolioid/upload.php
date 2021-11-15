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
    
    
$directory=$custid."/upload";
$ftpserver = "ftp.epnet.com"; 
$ftpuser = $username; 
$ftppass = $password; 

$count=0;
foreach(glob("{$directory}/*") as $file)
       {
        if(filesize($file)>0)
        {
            $kaynakdosya = $file; 
            
//            if(basename($file)=="full.mrc")
//                $hedefdosya = "full/".basename($file); 
//            else
                $hedefdosya = "update/".basename($file); 
            
            $baglanti = ftp_connect($ftpserver); 
            $giris = ftp_login($baglanti, $ftpuser, $ftppass); 
              ftp_pasv($baglanti, true);
			  echo $baglanti;echo $hedefdosya; echo $kaynakdosya; echo $giris;
			  
            if ((!$baglanti) || (!$giris)) { 
                echo "Ftp bağlantısı sağlanamadı"; 
                die; 
            } 
            else { 
                echo "Ftp'ye bağlanıldı<br>"; 
            } 
            $yukle = ftp_put($baglanti,$hedefdosya, $kaynakdosya,FTP_BINARY);

            if (!$yukle) {
               echo "Dosya Yüklenemiyor"; 
            } 
            else 
            { 
                echo "Dosya Yüklendi"; 
            }
            ftp_close($baglanti); 
            $count=$count+1;
        }
      }

      

    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrc file was uploaded.", LOCK_EX);
 
    unlink($custid."/info/last_upload_date");
    $last_upload_date=$custid."/info/last_upload_date";
    $datetoday = date("m/d/Y");
    file_put_contents($last_upload_date, $datetoday, LOCK_EX);



 

 $directory=$custid."/extra";
if($extra==="YES")
{
    
        foreach(glob("{$directory}/*") as $file)
       {

            $kaynakdosyaextra = $file; 
            $hedefdosyaextra = "update/".basename($file); 
            $baglantiextra = ftp_connect($ftpserver); 
            $girisextra = ftp_login($baglantiextra, $ftpuser, $ftppass); 
              ftp_pasv($baglantiextra, true);
            if ((!$baglantiextra) || (!$girisextra)) { 
                echo "Ftp bağlantısı sağlanamadı"; 
                die; 
            } 
            else { 
                echo "Ftp'ye bağlanıldı<br>"; 
            } 
            $yukleextra = ftp_put($baglantiextra,$hedefdosyaextra, $kaynakdosyaextra, FTP_BINARY);

            if (!$yukleextra) {
               echo "Dosya Yüklenemiyor"; 
            } 
            else 
            { 
                echo "Dosya Yüklendi"; 
            }
            ftp_close($baglantiextra); 
      }
}





?>