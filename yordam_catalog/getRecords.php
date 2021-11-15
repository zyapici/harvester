<?php         
    	set_time_limit(1000000000);
        date_default_timezone_set('Europe/Istanbul');
       
		if(isset($_REQUEST["url"]))
		{
			$url=$_REQUEST["url"];
		}
		if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
		 
                unlink($custid."/info/status");
                $filestatus=$custid."/info/status";
		file_put_contents($filestatus, "getting records...", LOCK_EX);
                
 /////////////////////////////////////////LOOK_AT_TOTAL///////////////////////////////////////////   
		$urltotal="".$url."/yordambt/bilgiver/yordamxml.php?listele=1&atla=2";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urltotal);
                curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
                $returned = curl_exec($ch);
                curl_close($ch);
                $xml = simplexml_load_string($returned);

			
		
		$filetotal=$custid."/log/total.xml";
		$filelog=$custid."/log/error_log.txt";
		file_put_contents($filetotal, $xml->asXML(), LOCK_EX);
		 
		 $handle = fopen($filetotal, "r");
         if ($handle) 
		 {
                   while (($line = fgets($handle)) !== false) {
                    if(strpos($line,"kayittoplam"))
                                {
                                         $total=substr($line,strpos($line,"kayittoplam=\"")+13,strpos($line,"\" kayitbulunan")-30);
                                }

                   }
                 } 
         else 
         {
            file_put_contents($filelog, "Dosya Okuma Hatas?", FILE_APPEND | LOCK_EX);
         } 
        fclose($handle);
        
        $filenumberofILS=$custid."/log/number_of_records_ILS";
          file_put_contents($filenumberofILS, $total, FILE_APPEND | LOCK_EX);
////////////////////////////////////END_LOOK_AT_TOTAL/////////////////////////////////////
		 
///////////////////////////////DOWNLOAD_RECORDS_50_BY_50/////////////////////////////////
               
		for($i=0;$i<$total;$i=$i+20)
		{
		
		  try {
                          $url50="".$url."/yordambt/bilgiver/yordamxml.php?listele=20&atla=$i";
                          $ch = curl_init();
                          curl_setopt($ch, CURLOPT_URL, $url50);
                          curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                          curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
                          $returned = curl_exec($ch);
                          curl_close($ch);
                          @$xml50 = simplexml_load_string($returned);


                          if($xml50==FALSE)
                          {
                             file_put_contents($filelog, "\nError in ".$url50, FILE_APPEND | LOCK_EX);
                             continue;
                          }

                          $file50=$custid."/xml/".$i.".xml";
                          file_put_contents($file50, $xml50->asXML(), FILE_APPEND | LOCK_EX);
		 }
		
             catch(Exception $e) {
   	        file_put_contents($filelog, "Error in xml cannot be continue ", FILE_APPEND | LOCK_EX);
      
	    }
		
       }
   
 
       
       check_error_again(1,$custid);
       check_error_again(2,$custid);
       check_error_again(3,$custid);
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

    function check_error_again($number,$custid)
    {
              $path=$number-1;
              if($path==0)
                  $path="";
              $filelognumber=$custid."/log/error_log".$number.".txt";
              $handleerror = fopen($custid."/log/error_log".$path.".txt", "r");
              if ($handleerror) 
                     {
                       while (($lineerror = fgets($handleerror)) !== false) {
                           $lineerror=str_replace("\n", "", $lineerror);
                           $lineerror=str_replace("\n", "", $lineerror);
                           $lineerror=str_replace("Error in ", "", $lineerror);
                           if($lineerror!="")
                           {
                             try {
                                  $url50=$lineerror;
                                  $ch = curl_init();
                                  curl_setopt($ch, CURLOPT_URL, $url50);
                                  curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                                  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                  curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
                                  $returned = curl_exec($ch);
                                  curl_close($ch);
                                  @$xml50 = simplexml_load_string($returned);


                                  if($xml50==FALSE)
                                  {
                                     file_put_contents($filelognumber, "\nError in ".$url50, FILE_APPEND | LOCK_EX);
                                     continue;
                                  }
                                  $filename=substr($url50, strpos($url50, "atla")+5);
                                  
                                  $file50=$custid."/xml/".$filename.".xml";
                                  file_put_contents($file50, $xml50->asXML(), FILE_APPEND | LOCK_EX);
                                 }

                             catch(Exception $e) {
                                file_put_contents($filelognumber, "Error in xml cannot be continue ", FILE_APPEND | LOCK_EX);

                            }
                       }


                       }
                     } 
        
        fclose($handleerror);
    }
?>