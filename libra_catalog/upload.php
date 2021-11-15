<?php
set_time_limit(1000000000);
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
//ftp serverımızı, kullanıcı adı ve şifremizi yazıyoruz.
 
$ftpserver = "ftp.epnet.com"; 
 
$ftpuser = $username; 
 
$ftppass = $password; 
 
//POST metodu ile dosyamızı çekiyoruz. 
 
$kaynakdosya = $custid."\marc\allmark(records).mrc"; 
 
$hedefdosya = "full/allmark(records).mrc"; 
 
//ftp_connect ile ftp serverımıza bağlanıyoruz 
 
$baglanti = ftp_connect($ftpserver); 
 
//kullanııcı bilgilerimiz ile giriş yapıyoruz. 
 
$giris = ftp_login($baglanti, $ftpuser, $ftppass); 
 
//bağlantı ve girişin olumlu olup olmadığını kontrol ediyoruz. 
 
if ((!$baglanti) || (!$giris)) { 
 
echo "Ftp bağlantısı sağlanamadı"; 
 
die; 
 
} else { 
 
echo "Ftp'ye bağlanıldı<br>"; 
 
} 
 
//ftp_put fonksiyonu ile dosyamızı yüklüyoruz. 
 ftp_pasv($baglanti, true);
$yukle = ftp_put($baglanti,$hedefdosya, $kaynakdosya, FTP_BINARY); 
 
//yüklenip yüklenemdiğini kontrol ediyoruz 


if (!$yukle) { 
 
echo "Dosya Yüklenemiyor"; 
 
} else { 
 
echo "Dosya Yüklendi"; 
  unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrc file was uploaded", LOCK_EX);
 
    unlink($custid."/info/last_upload_date");
    $last_upload_date=$custid."/info/last_upload_date";
    $datetoday = date("m/d/Y");
    file_put_contents($last_upload_date, $datetoday, LOCK_EX);
} 
   
//bağlantımızı kapatıyoruz 
 
ftp_close($baglanti); 
sleep(10);
 $directory=$custid."/extra";
if($extra==="YES")
{
        foreach(glob("{$directory}/*") as $file)
       {

            $kaynakdosyaextra = $file; 
            $hedefdosyaextra = "update/".basename($file); 
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