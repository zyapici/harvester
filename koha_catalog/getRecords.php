<?php         
    	set_time_limit(1000000);
        date_default_timezone_set('Europe/Istanbul');
       
		if(isset($_REQUEST["url"]))
		{
			$url=$_REQUEST["url"];
		}
		if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
		 if(isset($_REQUEST["total"]))
		{
			$total=$_REQUEST["total"];
		}
		 
                unlink($custid."/info/status");
                $filestatus=$custid."/info/status";
		file_put_contents($filestatus, "getting records...", LOCK_EX);
                
                $filelog=$custid."/log/error_log.txt";
		//$total=45000; 
                 $filenumberofILS=$custid."/log/number_of_records_ILS";
                  file_put_contents($filenumberofILS, $total, FILE_APPEND | LOCK_EX);
                 
            
		for($i=0;$i<$total;$i=$i+50)
		{
                    $ids="";
                   
                    for($y=$i+1;$y<=$i+50;$y++)
                    {
                        if($y==$i+1)
                           $ids=$y;
                        else
                           $ids=$ids."+".$y;
                    }
                   
		  try {
                           $url50=$url."/cgi-bin/koha/ilsdi.pl?service=GetRecords&id=".$ids;
                        
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
  
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

?>