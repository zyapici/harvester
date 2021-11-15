<?php
	set_time_limit(1000000);
    date_default_timezone_set('Europe/Istanbul');
    
    if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
                
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "creating mrk file...", LOCK_EX);
    
$myDirectory = opendir($custid."/xml/");
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}
closedir($myDirectory);
$indexCount	= count($dirArray);

$fileallmark=$custid."/marc/allmark(records).mrk";

    
$totalLoaded=0;
for($i=0;$i<$indexCount;$i=$i+1)
{
  if (substr("$dirArray[$i]", 0, 1) != "."){
    $doc = new DOMDocument();
    $doc->load($custid."/xml/".$dirArray[$i]);
   
    $yordam = $doc->getElementsByTagName("kayit");
    
    foreach($yordam as $kayit)
    {
	 $eseradi = $kayit->getElementsByTagName("eseradi");
	 $eseradi1 = $eseradi->item(0)->nodeValue;
	 
	 $demirbas = $kayit->getElementsByTagName("demirbas");
	 $demirbas1 = $demirbas->item(0)->nodeValue;
	 
	 $siniflama = $kayit->getElementsByTagName("siniflama");
	 $siniflama1 = $siniflama->item(0)->nodeValue;
	 $siniflama1=str_replace("/","|\$b",$siniflama1);
	 
         $dilKN = $kayit->getElementsByTagName("dilKN");
	 $dilKN1 = $dilKN->item(0)->nodeValue;
         
	 $dil = $kayit->getElementsByTagName("dil");
	 $dil1 = $dil->item(0)->nodeValue;
	 
	 $url = $kayit->getElementsByTagName("url");
	 $url1 = $url->item(0)->nodeValue;
	 
	 $yayinyeri = $kayit->getElementsByTagName("yayinyeri");
	 $yayinyeri1 = $yayinyeri->item(0)->nodeValue;
	 
	 $yayinlayan = $kayit->getElementsByTagName("yayinlayan");
	 $yayinlayan1 = $yayinlayan->item(0)->nodeValue;
	 
         $yayintarihi = $kayit->getElementsByTagName("yayintarihi1");
	 $yayintarihi1 = $yayintarihi->item(0)->nodeValue;
         
	 $yazar = $kayit->getElementsByTagName("yazar");
	 $yazar1 = $yazar->item(0)->nodeValue;
	 
	 $konubasliklari = $kayit->getElementsByTagName("konubasliklari");
	 $konubasliklari1 = $konubasliklari->item(0)->nodeValue;
	 
	 $turKN = $kayit->getElementsByTagName("turKN");
	 $turKN1 = $turKN->item(0)->nodeValue;
         
         $sekilKN = $kayit->getElementsByTagName("sekilKN");
	 $sekilKN1 = $sekilKN->item(0)->nodeValue;
	
         $ortamKN = $kayit->getElementsByTagName("ortamKN");
	 $ortamKN1 = $ortamKN->item(0)->nodeValue;
         
	 $kutuphane = $kayit->getElementsByTagName("ktp");
	 if(!empty($kutuphane))
	    $kutuphane1 = $kutuphane->item(0)->nodeValue;
     else
	    $kutuphane1="";
	 $bolum = $kayit->getElementsByTagName("bolum");
	 $bolum1 = $bolum->item(0)->nodeValue;
	 
	 $fizikseltanim = $kayit->getElementsByTagName("fizikseltanim");
	 $fizikseltanim1 = $fizikseltanim->item(0)->nodeValue;
	 
	 $kapakyolu = $kayit->getElementsByTagName("kapakyolu");
	 $kapakyolu1 = $kapakyolu->item(0)->nodeValue;
	 
	 $notlar = $kayit->getElementsByTagName("notlar");
	 $notlar1 = $notlar->item(0)->nodeValue;
	  
	 $icindekiler = $kayit->getElementsByTagName("icindekiler");
	 $icindekiler1 = $icindekiler->item(0)->nodeValue;
	 
	 if($turKN1=="01" && $sekilKN1=="05")  //elektronik kitap
	   file_put_contents($fileallmark, "=LDR  00000nmm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
         else if($turKN1=="04" && ($ortamKN1=="05" || $ortamKN1=="06"))  //DVD, VCD
	   file_put_contents($fileallmark, "=LDR  00000ngm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
         else if($turKN1=="04" && $ortamKN1=="03")  //CD
	   file_put_contents($fileallmark, "=LDR  00000njm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
         else if($turKN1=="03")  //Süreli yayınlar
	   file_put_contents($fileallmark, "=LDR  00000nas 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
         else if($turKN1=="02")  //Tez
	   file_put_contents($fileallmark, "=LDR  00000ntm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
	 else 
	    file_put_contents($fileallmark, "=LDR  00000nam 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
		
	 if($demirbas1!="")
	 {
	 	$demirbas1last=str_replace("\n","",$demirbas1);	 	
	    file_put_contents($fileallmark, "=001  $demirbas1last\r\n", FILE_APPEND | LOCK_EX);
	 }
         
	 if($dilKN1!="")
	 {
            if($yayintarihi1!="")
                {
                    $yayintarihi1=str_replace("\n","",$yayintarihi1);
                    $yayintarihi1=str_replace("\r","",$yayintarihi1);
                    $dilKN1last=str_replace("\n","",$dilKN1);
	 	    file_put_contents($fileallmark, "=008        t".$yayintarihi1."                             ".$dilKN1last."  \r\n", FILE_APPEND | LOCK_EX);
                }
	    else
                {
	 	    $dilKN1last=str_replace("\n","",$dilKN1);
	 	    file_put_contents($fileallmark, "=008                                     ".$dilKN1last."  \r\n", FILE_APPEND | LOCK_EX);
                }
	 }
         
         if($dil1!="")
	 {
	 	$dil1last=str_replace("\n","",$dil1);
	 	file_put_contents($fileallmark, "=041  \\0\$a$dil1last\r\n", FILE_APPEND | LOCK_EX);
	 }
         
	  
	 if($siniflama1!="")
	 {
	 	$siniflama1last=str_replace("\n","",$siniflama1);
	 	file_put_contents($fileallmark, "=050  \\\\\$a$siniflama1last\r\n", FILE_APPEND | LOCK_EX);
	 }
	    
	 if($yazar1!="")
	 {
	    $yazar1last=str_replace("\n","",$yazar1);
	    file_put_contents($fileallmark, "=100  1\\\$a$yazar1last\r\n", FILE_APPEND | LOCK_EX);
	 }
	 if($eseradi1!="")
	 {
	 	$eseradi1last=str_replace("\n","",$eseradi1);
	    file_put_contents($fileallmark, "=245  14\$a$eseradi1last\r\n", FILE_APPEND | LOCK_EX);
	 }
	 if($yayinyeri1!="")
	 {
	 	$yayinyeri1last=str_replace("\n","",$yayinyeri1);
                $yayinyeri1last=str_replace("\r","",$yayinyeri1last);
	 	
                $yayinlayan1last=str_replace("\n","",$yayinlayan1);
                $yayinlayan1last=str_replace("\r","",$yayinlayan1last);
                
                $yayintarihi1=str_replace("\n","",$yayintarihi1);
                $yayintarihi1=str_replace("\r","",$yayintarihi1);
	 	file_put_contents($fileallmark, "=260  \\\\\$a$yayinyeri1last :\$b$yayinlayan1last,\$c$yayintarihi1\r\n", FILE_APPEND | LOCK_EX);
	 }
	 if($fizikseltanim1!="")
	 {
	 	$fizikseltanim1last=str_replace("\n","",$fizikseltanim1);
	 	file_put_contents($fileallmark, "=300  \\\\\$a$fizikseltanim1last\r\n", FILE_APPEND | LOCK_EX);
	 }
	 
	 if($notlar1!="")
	 {
	 	$notlar2=str_replace("\n","",$notlar1);
                $notlar1last=substr($notlar2, 0, 9200);
                
	 	file_put_contents($fileallmark, "=500  \\\\\$a$notlar1last\r\n", FILE_APPEND | LOCK_EX);
	 }
	 
	 if($icindekiler1!="" && $icindekiler1!="içindekiler")
	 {
	 	$icindekiler2=str_replace("\n","",$icindekiler1);
                $icindekiler3=str_replace("-----","",$icindekiler2);
	 	$icindekiler4=str_replace("<br/>","--",$icindekiler3);
                $icindekiler5=str_replace(".....","",$icindekiler4);
                
                $icindekiler0last=substr($icindekiler5, 0, 6000);
                $icindekiler1last=trim($icindekiler0last);
	 	file_put_contents($fileallmark, "=520  \\\\\$a$icindekiler1last\r\n", FILE_APPEND | LOCK_EX);
	 }
	 
	 if($konubasliklari1!="")
	 {
	 	$konubasliklari1=str_replace("<br/>","",$konubasliklari1);
		$konubasliklari1=str_replace("\n","--",$konubasliklari1);
		$konubasliklariarray=explode("--",$konubasliklari1);
		foreach($konubasliklariarray as $konu)
	 	  file_put_contents($fileallmark, "=650  \\0\$a$konu\r\n", FILE_APPEND | LOCK_EX);
	 }
	 
	 
	 if($url1!="")
         {
            
             $url1=str_replace("\r", "|",$url1);
             $url1=str_replace("\n", "|",$url1);
             $urlarray=explode("|",$url1);
           
             $addurl=substr($urlarray[0], 0, 400);
	    file_put_contents($fileallmark, "=856  \\0\$u$addurl\r\n", FILE_APPEND | LOCK_EX);
         }
		
	 if($kutuphane1!="")
	    file_put_contents($fileallmark, "=906  \\\\\$a$kutuphane1\r\n", FILE_APPEND | LOCK_EX);
	 
	  if($bolum1!="")
	    file_put_contents($fileallmark, "=903  \\\\\$a$bolum1\r\n", FILE_APPEND | LOCK_EX);
	 
	 if($kapakyolu1!="")
         {
                $kapakyolu2=substr($kapakyolu1, 0, 400);
	 	file_put_contents($fileallmark, "=956  \\\\\$u$kapakyolu2\r\n", FILE_APPEND | LOCK_EX);
         }
	 
	 file_put_contents($fileallmark, "\r\n", FILE_APPEND | LOCK_EX);
	 $totalLoaded=$totalLoaded+1;
	} 
  }
}

      $filenumberofLoaded=$custid."/log/number_of_records_Loaded";
          file_put_contents($filenumberofLoaded, $totalLoaded, FILE_APPEND | LOCK_EX);
          
          
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "mrk file was created.", LOCK_EX);
	
?>