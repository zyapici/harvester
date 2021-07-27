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

if(strpos($url,"istinye")>0)
{
	$datatotal = array(
                                'dIstekTuru' => 'sAramaDetayliListe',
                                'dSurum' => '18',
                                    'dKBS' => '0',
                                    'dKLS' => '1',
                                    'dToken' => 'f7900fecff0db5f7fd228cdcd5061282'
                                );
}
	else
	{
$datatotal = array(
                                'dIstekTuru' => 'sAramaDetayliListe',
                                'dSurum' => '18',
                                    'dKBS' => '0',
                                    'dKLS' => '1',
                                    'dToken' => '64df94d8ab67adf1bf443d92b3b155ea'
                                );
	}
								
		$urltotal="".$url."/yordambt/bilgiver/yordambilgi.php";
                $ch = curl_init();
                 curl_setopt($ch, CURLOPT_POST, TRUE);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('multipart/form-data'));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($ch, CURLOPT_URL, $urltotal);
                                curl_setopt($ch, CURLOPT_REFERER, $urltotal);
                                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $datatotal);
				
                $returned = curl_exec($ch);
				
                curl_close($ch);
                $json_response_total = json_decode($returned);
               
		$filetotal=$custid."/log/total.json";
		$filelog=$custid."/log/error_log.txt";
		file_put_contents($filetotal, $returned, LOCK_EX);
		 
		 $total=$json_response_total->messages[0]->{'total-count'};
		 
                $filenumberofILS=$custid."/log/number_of_records_ILS";
                file_put_contents($filenumberofILS, $total, FILE_APPEND | LOCK_EX);
////////////////////////////////////END_LOOK_AT_TOTAL/////////////////////////////////////
		 
///////////////////////////////DOWNLOAD_RECORDS_50_BY_50/////////////////////////////////
               echo $total;
		for($i=0;$i<$total;$i=$i+50)
		{
		
		   try {
                         if(strpos($url,"istinye")>0)
						{
							 $data = array(
                                'dIstekTuru' => 'sAramaDetayliListe',
                                'dSurum' => '18',
                                    'dKBS' => $i,
                                    'dKLS' => '50',
                                    'dToken' => 'f7900fecff0db5f7fd228cdcd5061282'
                                );
						}
							
							else{
                          $data = array(
                                'dIstekTuru' => 'sAramaDetayliListe',
                                'dSurum' => '18',
                                    'dKBS' => $i,
                                    'dKLS' => '50',
                                    'dToken' => '64df94d8ab67adf1bf443d92b3b155ea'
                                );
								
							}
                                  //$postfields = json_encode($data);
                                $curlURL=$url."/yordambt/bilgiver/yordambilgi.php";
                                $curl = curl_init();
                                curl_setopt($curl, CURLOPT_POST, TRUE);
                                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($curl, CURLOPT_HTTPHEADER, array('multipart/form-data'));
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($curl, CURLOPT_URL, $curlURL);
                                curl_setopt($curl, CURLOPT_REFERER, $curlURL);
                                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
                                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

                                $returned = curl_exec($curl);
                                curl_close($curl);

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
   
 
       
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

   
