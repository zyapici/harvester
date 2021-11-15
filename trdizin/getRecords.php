<?php         
    	set_time_limit(1000000);
        ini_set('memory_limit','16M');ini_set('memory_limit','512M');
        date_default_timezone_set('Europe/Istanbul');
       
		if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
		 
                unlink($custid."/info/status");
                $filestatus=$custid."/info/status";
		file_put_contents($filestatus, "getting records...", LOCK_EX);
                
           
                
                for($i=0;$i<450000;$i=$i+100)
                {
                   
                   $header=array(
    "Authorization: Bearer ff29461301ae216654ee79dfded7b7da709cae2ea09253e58c7da98a69beb4a3"
  );
                 
                    $urlarticles="https://services.trdizin.gov.tr/xml/oaidc?limit=".($i+100)."&offset=$i";

                    $charticles = curl_init();
                    curl_setopt($charticles, CURLOPT_URL, $urlarticles);
                    curl_setopt($charticles, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($charticles, CURLOPT_FAILONERROR, 1);
                    curl_setopt($charticles, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($charticles, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($charticles, CURLOPT_TIMEOUT, 15000000);
                    curl_setopt($charticles, CURLOPT_HTTPHEADER, $header);
                    
                    $returnedarticles = curl_exec($charticles);
                    curl_close($charticles);
    
          
                    
                    $filearticles=$custid."/data/paper-".$i."-".date("dmY").".xml";
					if($returnedarticles!="" || $returnedarticles!=null)
						file_put_contents($filearticles, $returnedarticles, LOCK_EX);    
                }
                
    
                          
		

	 $cmd="zip -r \"c:\wamp64\www\\trdizin\\trdizin\data\data".date("dmY").".zip\" \"c:\wamp64\www\\trdizin\\trdizin\data\"";
         shell_exec($cmd);

  
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

?>