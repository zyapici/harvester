<?php
set_time_limit(1000000);
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
    file_put_contents($filestatus, "uploading data file...", LOCK_EX);
 
$ftpserver = "ftp.epnet.com"; 
$ftpuser = $username; 
$ftppass = $password; 

$directory=$custid."/data";
      
	  foreach(glob("{$directory}/*") as $file)
       {
        if(filesize($file)>0 & pathinfo($file, PATHINFO_EXTENSION)=="zip")
        {
		
		//$file=$custid."\data\data".date('dmY').".zip";
 
                    $kaynakdosya = $file; 
                    $hedefdosya = basename($file);   
                    $baglanti = ftp_connect($ftpserver); 
                    $giris = ftp_login($baglanti, $ftpuser, $ftppass); 
                    if ((!$baglanti) || (!$giris)) { 
                        echo "Ftp bağlantısı sağlanamadı"; 
                        die; 
                    } 
                    else  
                        echo "Ftp'ye bağlanıldı<br>"; 
					ftp_pasv($baglanti, true);
                   $yukle = ftp_put($baglanti,$hedefdosya, $kaynakdosya, FTP_BINARY); 

                    if (!$yukle) { 
                        echo "Dosya Yüklenemiyor"; 
                    } 
                    else 
                        echo "Dosya Yüklendi"; 
              

 
    ftp_close($baglanti); 
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "data file was uploaded", LOCK_EX);
 
    unlink($custid."/info/last_upload_date");
    $last_upload_date=$custid."/info/last_upload_date";
    $datetoday = date("m/d/Y");
    file_put_contents($last_upload_date, $datetoday, LOCK_EX);
 
	   }
	
	}
	
 $directoryextra=$custid."\extra\\";
if($extra=="YES")
{
        foreach(glob("{$directoryextra}/*") as $fileextra)
       {

            $kaynakdosyaextra = $fileextra; 
            $hedefdosyaextra = basename($fileextra); 
            $baglantiextra = ftp_connect($ftpserver); 
            $girisextra = ftp_login($baglantiextra, $ftpuser, $ftppass); 

            if ((!$baglantiextra) || (!$girisextra)) { 
                echo "Ftp bağlantısı sağlanamadı"; 
                die; 
            } 
            else { 
                echo "Ftp'ye bağlanıldı<br>"; 
            } 
			ftp_pasv($baglantiextra, true);
            $yukleextra = ftp_put($baglantiextra,$hedefdosyaextra, $kaynakdosyaextra, FTP_BINARY);

            if (!$yukle) {
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