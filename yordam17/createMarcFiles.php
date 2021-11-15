<?php
error_reporting(0);
	set_time_limit(1000000);
	//error_reporting(0);
    date_default_timezone_set('Europe/Istanbul');
    
    if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
                
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "creating mrk file...", LOCK_EX);
    
$myDirectory = opendir($custid."/json/");
while($entryName = readdir($myDirectory)) {
	$dirArray[] = $entryName;
}
closedir($myDirectory);
$indexCount	= count($dirArray);

$fileallmark=$custid."/marc/allmark(records).mrk";

    
$totalLoaded=0;



function endsWith($currentString, $target)
{
    $length = strlen($target);
    if ($length == 0) {
        return true;
    }
 
    return (substr($currentString, -$length) === $target);
}


for($i=0;$i<$indexCount;$i=$i+1)
{
  if (substr("$dirArray[$i]", 0, 1) != "."){
   
    $str_json = file_get_contents($custid."/json/".$dirArray[$i]);
	
    $yordam_json = json_decode($str_json, true);
    //print_r($yordam_json["VERI"]["kayitKNo"]);
    for($z=0;$z<count($yordam_json["VERI"]["kayitKNo"]);$z=$z+1)
    {
         $kayit=$yordam_json["VERI"];
         //echo $kayit["kunyeEserAdi"][$z];
	 $eseradi1=$kayit["kunyeEserAdi"][$z];
         $eseradi2=$kayit["kunyeEserAdiYazarlar"][$z];
         $ISBN1=$kayit["kunyeISBN"][$z];
	 $demirbas1=$kayit["kunyeDemirbas"][$z];
	 $siniflama1 = $kayit["kunyeSiniflama"][$z];
         $siniflama1=str_replace("<br />","",$siniflama1);
	 $siniflama1=str_replace("/","|\$b",$siniflama1);
	 $dilKN1 = $kayit["kunyeDilKN"][$z];
	 $yayinyeri1 = $kayit["kunyeYayinYeri"][$z];
	 $yayinlayan1 = $kayit["kunyeYayinlayan"][$z];
	 $yayintarihi1 = $kayit["kunyeYayinTarihi"][$z];
	 $yazar1 = $kayit["kunyeYazar"][$z];
	 $sorumlular1 = $kayit["kunyeSorumlular"][$z];
	 $konubasliklari1 = $kayit["kunyeKonuBasligi"][$z];
	 $turKN1 = $kayit["kunyeTurKN"][$z];
	 $sekilKN1 = $kayit["kunyeSekilKN"][$z];
	 $ortamKN1 = $kayit["kunyeOrtamKN"][$z];
	 $kutuphane1 = $kayit["kunyeKutuphane"][$z];
	 $bolum1 = $kayit["kunyeBolum"][$z];
	 $fizikseltanim1 = $kayit["kunyeFizikselTanim"][$z];
	 $notlar1 = $kayit["kunyeNotlar"][$z];
	 $icindekiler1 = $kayit["kunyeIcindekiler"][$z];
	 $bagislayan = $kayit["kunyeBagislayan"][$z];
	 $kunyeAltTurKN=$kayit["kunyeAltTurKN"][$z];
         
         $url1 = $kayit["kunyeURL"][$z];
         $kapakyolu1 = $kayit["kunyeKapakYolu"][$z];
         
         $mrkrecord="";
         
	 if($turKN1=="01" && $sekilKN1=="05")  //elektronik kitap
         {
	   //file_put_contents($fileallmark, "=LDR  00000nmm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
           $mrkrecord=$mrkrecord."=LDR  00000nmm 2200000 a 45000\r\n";
         }
         else if($turKN1=="04" && ($ortamKN1=="05" || $ortamKN1=="06"))  //DVD, VCD
         {
	   //file_put_contents($fileallmark, "=LDR  00000ngm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
           $mrkrecord=$mrkrecord."=LDR  00000ngm 2200000 a 45000\r\n";
         }
         else if($turKN1=="04" && $ortamKN1=="03")  //CD
         {
	   //file_put_contents($fileallmark, "=LDR  00000njm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
           $mrkrecord=$mrkrecord."=LDR  00000njm 2200000 a 45000\r\n";
         }
         else if($turKN1=="03" || $turKN1=="05")  //Süreli yayınlar
         {
	   //file_put_contents($fileallmark, "=LDR  00000nas 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
           $mrkrecord=$mrkrecord."=LDR  00000nas 2200000 a 45000\r\n";
         }
         else if($turKN1=="02")  //Tez
         {
	   //file_put_contents($fileallmark, "=LDR  00000ntm 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
           $mrkrecord=$mrkrecord."=LDR  00000ntm 2200000 a 45000\r\n";
         }
	 else 
         {
	    //file_put_contents($fileallmark, "=LDR  00000nam 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=LDR  00000nam 2200000 a 45000\r\n";
         }
		
	 if($demirbas1!="")
	 {
	    $demirbas1last=str_replace("\n","",$demirbas1);	 	
	    //file_put_contents($fileallmark, "=001  $demirbas1last\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=001  $demirbas1last\r\n";
	 }
         
	 if($dilKN1!="")
	 {
            if($yayintarihi1!="")
                {
                    $yayintarihi1=str_replace("\n","",$yayintarihi1);
                    $yayintarihi1=str_replace("\r","",$yayintarihi1);
                    $yayintarihi1=str_replace("<br />","",$yayintarihi1);
                    $dilKN1last=str_replace("\n","",$dilKN1);
	 	    //file_put_contents($fileallmark, "=008        t".$yayintarihi1."                        ".$dilKN1last."  \r\n", FILE_APPEND | LOCK_EX);
                    //file_put_contents($fileallmark, "=041  \\0\$a$dilKN1last\r\n", FILE_APPEND | LOCK_EX);
                    $mrkrecord=$mrkrecord."=008        t".$yayintarihi1."                        ".$dilKN1last."  \r\n";
                    $mrkrecord=$mrkrecord."=041  \\0\$a$dilKN1last\r\n";
                }
	    else
                {
	 	    $dilKN1last=str_replace("\n","",$dilKN1);
	 	    //file_put_contents($fileallmark, "=008                                     ".$dilKN1last."  \r\n", FILE_APPEND | LOCK_EX);
                    //file_put_contents($fileallmark, "=041  \\0\$a$dilKN1last\r\n", FILE_APPEND | LOCK_EX);
                    $mrkrecord=$mrkrecord."=008                                     ".$dilKN1last."  \r\n";
                    $mrkrecord=$mrkrecord."=041  \\0\$a$dilKN1last\r\n";
                }
	 }
          
	 if($dilKN1=="")
	 {
           if($yayintarihi1!="")
                {
                  $yayintarihi1=str_replace("\n","",$yayintarihi1);
                  $yayintarihi1=str_replace("\r","",$yayintarihi1);
                  $yayintarihi1=str_replace("<br />","",$yayintarihi1);
                  //file_put_contents($fileallmark, "=008        t".$yayintarihi1."                             \r\n", FILE_APPEND | LOCK_EX);
                  $mrkrecord=$mrkrecord."=008        t".$yayintarihi1."                             \r\n";
                }
         }
         
      
         if($ISBN1!="")
	 {
	 	$ISBN1last=str_replace("\n","",$ISBN1);
                $ISBN1last=str_replace("<br />","",$ISBN1last);
	 	//file_put_contents($fileallmark, "=020  \\\\\$a$ISBN1last\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=020  \\\\\$a$ISBN1last\r\n";
	 }
         
	 if($siniflama1!="")
	 {
	 	$siniflama1last=str_replace("\n","",$siniflama1);
                $siniflama1last=str_replace("<br />","",$siniflama1last);
	 	//file_put_contents($fileallmark, "=050  \\\\\$a$siniflama1last\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=050  \\\\\$a$siniflama1last\r\n";
	 }
	    
	 if($yazar1!="")
	 {
	    $yazar1last=str_replace("\n","",$yazar1);
            $yazar1last=str_replace("<br />","",$yazar1last);
			$yazar1last=str_replace("♀","",$yazar1last);
			
	    //file_put_contents($fileallmark, "=100  1\\\$a$yazar1last\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=100  1\\\$a$yazar1last\r\n";
	 }
	 
	 if($sorumlular1!="")
	 {
	    $sorumlular1last=str_replace("\n","",$sorumlular1);
            $sorumlular1last=str_replace("<br />","",$sorumlular1last);
			$sorumlular1last=str_replace("♀","",$sorumlular1last);
			
	    //file_put_contents($fileallmark, "=100  1\\\$a$yazar1last\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=100  1\\\$a$sorumlular1last\r\n";
	 }
	
         if($eseradi2!="")
	 {
            $eseradi2last=str_replace("\n","",$eseradi2);
            $eseradi2last=str_replace("<br />","",$eseradi2last);
            $eseradi2last=str_replace("/","/\$c",$eseradi2last);
			if($sorumlular1!="")
                $eseradi2last=$eseradi2last." ; ".$sorumlular1;
			//file_put_contents($fileallmark, "=245  14\$a$eseradi2last\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=245  14\$a$eseradi2last\r\n";
         }
          
	 if($eseradi2=="")
	 {
	     $eseradi1last=str_replace("\n","",$eseradi1);
            $eseradi1last=str_replace("<br />","",$eseradi1last);
             $eseradi1last=str_replace("\r","",$eseradi1last);
            if($yazar1last1!="")
                $eseradi1last=$eseradi1last." / ".$yazar1last1;
            if($sorumlular1!="")
                $eseradi1last=$eseradi1last." ; ".$sorumlular1;
	    //file_put_contents($fileallmark, "=245  14\$a$eseradi1last\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=245  14\$a$eseradi1last\r\n";
	 }
         
	 if($yayinyeri1!="")
	 {
	 	$yayinyeri1last=str_replace("\n","",$yayinyeri1);
                $yayinyeri1last=str_replace("\r","",$yayinyeri1last);
	 	$yayinyeri1last=str_replace("<br />","",$yayinyeri1last);
                
                $yayinlayan1last=str_replace("\n","",$yayinlayan1);
                $yayinlayan1last=str_replace("\r","",$yayinlayan1last);
                $yayinlayan1last=str_replace("<br />","",$yayinlayan1last);
                
                $yayintarihi1=str_replace("\n","",$yayintarihi1);
                $yayintarihi1=str_replace("\r","",$yayintarihi1);
                $yayintarihi1=str_replace("<br />","",$yayintarihi1);
	 	//file_put_contents($fileallmark, "=260  \\\\\$a$yayinyeri1last :\$b$yayinlayan1last,\$c$yayintarihi1\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=260  \\\\\$a$yayinyeri1last :\$b$yayinlayan1last,\$c$yayintarihi1\r\n";
	 }
	 if($fizikseltanim1!="")
	 {
	 	$fizikseltanim1last=str_replace("\n","",$fizikseltanim1);
                $fizikseltanim1last=str_replace("<br />","",$fizikseltanim1last);
	 	//file_put_contents($fileallmark, "=300  \\\\\$a$fizikseltanim1last\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=300  \\\\\$a$fizikseltanim1last\r\n";
	 }
	 if($turKN1=="01" && $kunyeAltTurKN=="222")
		$mrkrecord=$mrkrecord."=300  \\\\\$aaudiobook\r\n";
	 if($turKN1=="01" && $sekilKN1=="05")  
         {
		//file_put_contents($fileallmark, "=338  \\\\\$aElektronik\r\n", FILE_APPEND | LOCK_EX); 
                $mrkrecord=$mrkrecord."=338  \\\\\$aElektronik\r\n";
         }
	
	 if($notlar1!="")
	 {
	 	$notlar2=str_replace("\n","",$notlar1);
                $notlar1last=substr($notlar2, 0, 9200);
                $notlar1last=str_replace("<br />","",$notlar1last);
	 	//file_put_contents($fileallmark, "=500  \\\\\$a$notlar1last\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=500  \\\\\$a$notlar1last\r\n";
	 }
	 
	 if($icindekiler1!="" && $icindekiler1!="içindekiler")
	 {
	 	$icindekiler2=str_replace("\n","",$icindekiler1);
                $icindekiler3=str_replace("-----","",$icindekiler2);
	 	$icindekiler4=str_replace("<br />","_",$icindekiler3);
                $icindekiler5=str_replace(".....","",$icindekiler4);
                $icindekiler5=str_replace("§","",$icindekiler5);
                $icindekiler5=str_replace("’","",$icindekiler5);
                $icindekiler5=str_replace("‘","",$icindekiler5);
                $icindekiler5=str_replace("“","",$icindekiler5);
                $icindekiler5=str_replace("”","",$icindekiler5);
                $icindekiler5=str_replace("¬","",$icindekiler5);
                $icindekiler5=str_replace("★","",$icindekiler5);
                $icindekiler5=str_replace("■","",$icindekiler5);
                $icindekiler5=str_replace("•","",$icindekiler5);
                $icindekiler5=str_replace("»","",$icindekiler5);
                $icindekiler5=str_replace("«","",$icindekiler5);
                $icindekiler5=str_replace("«","^",$icindekiler5);
                $icindekiler5=str_replace("î","i",$icindekiler5);
                $icindekiler5=str_replace("â","a",$icindekiler5);
				$icindekiler5=str_replace("~","",$icindekiler5);
				$icindekiler5=str_replace("…","",$icindekiler5);
				$icindekiler5=str_replace("*","",$icindekiler5);
				
				
				
                
                
                if($custid=="ns205584" && $demirbas1last=="0000375")
					$icindekiler5="";
                $icindekiler0last=$icindekiler5;
                $icindekiler1last=trim($icindekiler0last);
                $icindekiler1last=$icindekiler1last."-";
             
	 	//file_put_contents($fileallmark, "=520  \\\\\$a$icindekiler1last\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=520  \\\\\$a$icindekiler1last\r\n";
	 }
         
         if($bagislayan!="")
         {
            $mrkrecord=$mrkrecord."=541  1\\\$a$bagislayan\r\n"; 
         }
         
	 
	 if($konubasliklari1!="")
	 {
	 	//$konubasliklari1=str_replace("<br/>","",$konubasliklari1);
		$konubasliklari1=str_replace("\n","--",$konubasliklari1);
		$konubasliklariarray=explode("<br />",$konubasliklari1);
		foreach($konubasliklariarray as $konu)
                {
	 	  //file_put_contents($fileallmark, "=650  \\0\$a$konu\r\n", FILE_APPEND | LOCK_EX);
                  $mrkrecord=$mrkrecord."=650  \\0\$a$konu\r\n";
                }
	 }
	 
	 
	 if($url1!="")
         {
            
             $url1=str_replace("\r", "|",$url1);
             $url1=str_replace("\n", "|",$url1);
             $urlarray=explode("|",$url1);
           
             $addurl=substr($urlarray[0], 0, 400);
             $addurl=str_replace("<br />","",$addurl);
	    //file_put_contents($fileallmark, "=856  \\0\$u$addurl\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=856  \\0\$u$addurl\r\n";
         }
		
	 if($kutuphane1!="")
         {
	    //file_put_contents($fileallmark, "=906  \\\\\$a$kutuphane1\r\n", FILE_APPEND | LOCK_EX);
             $mrkrecord=$mrkrecord."=906  \\\\\$a$kutuphane1\r\n";
         }
	 
	  if($bolum1!="" &&$bolum1!="Kutlubey Kütüphanesi")
          {
	    //file_put_contents($fileallmark, "=903  \\\\\$a$bolum1\r\n", FILE_APPEND | LOCK_EX);
            $mrkrecord=$mrkrecord."=903  \\\\\$a$bolum1\r\n";
          }
	 
	 if($kapakyolu1!="" && endsWith($kapakyolu1,"veriler/yordambt/cokluortam/")==false)
         {
                $kapakyolu2=substr($kapakyolu1, 0, 400);
                $kapakyolu2=str_replace("<br />","",$kapakyolu2);
	 	//file_put_contents($fileallmark, "=956  \\\\\$u$kapakyolu2\r\n", FILE_APPEND | LOCK_EX);
                $mrkrecord=$mrkrecord."=956  \\\\\$u$kapakyolu2\r\n";
         }
	 
	 //file_put_contents($fileallmark, "\r\n", FILE_APPEND | LOCK_EX);
         if(strlen($mrkrecord)>99999)
         {
             $remove=strlen($mrkrecord)-99999;
            // echo strlen($mrkrecord);
              $mrkrecord=str_replace("=520  \\\\\$a$icindekiler1last\r\n","",$mrkrecord);
            //  echo strlen($mrkrecord)." ";
            //  echo $remove." ";
            //  echo strlen(substr($icindekiler1last,0,strlen($icindekiler1last)))." ";
             $mrkrecord=$mrkrecord."=520  \\\\\$a".substr($icindekiler1last,0,strlen($icindekiler1last)-$remove-6000)."\r\n";
            // echo strlen($mrkrecord);
         }
         //echo $mrkrecord;
         file_put_contents($fileallmark, "$mrkrecord\r\n", FILE_APPEND | LOCK_EX);
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