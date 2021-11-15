<?php         
    	set_time_limit(10000000000);
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
		
                 $filenumberofILS=$custid."/log/number_of_records_ILS";
                 file_put_contents($filenumberofILS, $total, FILE_APPEND | LOCK_EX);
                 $fileall=$custid."/marc/allmark(records).mrc";
                 
                $client = new SoapClient($url."/LibraService.asmx?WSDL");
                $params = new StdClass;
		for($i=1;$i<$total;$i=$i+1)
		{
		  try {
                        $params->kayitNo = $i;
                        $result = $client->KunyeGetir($params)->KunyeGetirResult;
                        if($result->base64record!="")
                           file_put_contents($fileall,base64_decode($result->base64record), FILE_APPEND | LOCK_EX);
                        
		      }
                  catch(Exception $e) 
                      {
   	                 file_put_contents($filelog, "Error in xml cannot be continue ", FILE_APPEND | LOCK_EX);
	              }
		
                }
  
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

?>