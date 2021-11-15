<?php        
error_reporting(1); 
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
		$urltotal="".$url."/yordambt/bilgiver/yordambilgi.php?dIstekTuru=sAramaDetayliListe&dKBS=0&dKLS=1";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urltotal);
                curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
				if(strstr($url,"https"))
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
                $returned = curl_exec($ch);
                curl_close($ch);
                $json_response_total = json_decode($returned);
               
		$filetotal=$custid."/log/total.json";
		$filelog=$custid."/log/error_log.txt";
		file_put_contents($filetotal, $returned, LOCK_EX);
		 
		 $total=$json_response_total->KAYITSAYISI;
		 
                $filenumberofILS=$custid."/log/number_of_records_ILS";
                file_put_contents($filenumberofILS, $total, FILE_APPEND | LOCK_EX);
////////////////////////////////////END_LOOK_AT_TOTAL/////////////////////////////////////
		 
///////////////////////////////DOWNLOAD_RECORDS_50_BY_50/////////////////////////////////
               echo $total;
		for($i=0;$i<$total;$i=$i+50)
		{
		
		   try {
                          $url50="".$url."/yordambt/bilgiver/yordambilgi.php?dIstekTuru=sAramaDetayliListe&dKBS=$i&dKLS=50";
                          $ch = curl_init();
                          curl_setopt($ch, CURLOPT_URL, $url50);
                          curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                          curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
						  if(strstr($url,"https"))
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                          $returned = curl_exec($ch);
                          curl_close($ch);
                          $json50 = $returned;


                          if($json50==FALSE)
                          {
                             file_put_contents($filelog, "\nError in ".$url50, FILE_APPEND | LOCK_EX);
                             continue;
                          }

                          $file50=$custid."/json/".$i.".json";
						  if(!file_exists($file50))
							file_put_contents($file50, $json50, FILE_APPEND | LOCK_EX);
		       }
		
             catch(Exception $e) {
   	        file_put_contents($filelog, "Error in json cannot be continue ", FILE_APPEND | LOCK_EX);
      
	    }
		
       }
   
 
       
       //check_error_again(1,$custid);
       //check_error_again(2,$custid);
       //check_error_again(3,$custid);
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
								  if(strstr($url,"https"))
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                  $returned = curl_exec($ch);
                                  curl_close($ch);
                                  $json50 = $returned;


                                  if($json50==FALSE)
                                  {
                                     file_put_contents($filelognumber, "\nError in ".$url50, FILE_APPEND | LOCK_EX);
                                     continue;
                                  }
                                  $filename=substr($url50, strpos($url50, "dKLS")+5);
                                  
                                  $file50=$custid."/json/".$filename.".json";
                                  file_put_contents($file50, $json50, FILE_APPEND | LOCK_EX);
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