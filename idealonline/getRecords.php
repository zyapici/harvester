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
                
           
                
                for($i=0;$i<300;$i++)
                {
                    $urlarticles="https://idealonline.com.tr/IdealOnline/paper/paper-".$i.".xml";
                        
                    $charticles = curl_init();
                    curl_setopt($charticles, CURLOPT_URL, $urlarticles);
                    curl_setopt($charticles, CURLOPT_FAILONERROR, 1);
                    curl_setopt($charticles, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($charticles, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($charticles, CURLOPT_TIMEOUT, 15000000);
                    curl_setopt($charticles, CURLOPT_SSL_VERIFYPEER, 0);
                    $returnedarticles = curl_exec($charticles);
                    curl_close($charticles);
                   // @$xmlarticles = simplexml_load_string($returned);
                    $filearticles=$custid."/data/paper-".$i.".xml";
                    file_put_contents($filearticles, $returnedarticles, LOCK_EX);    
                }
                
                
//                http://idealonline.com.tr/IdealOnline/paper/paper-1.xml
           
                 
               
                               
                $urlbooks="https://idealonline.com.tr/IdealOnline/book.xml";
                        
                $chbooks = curl_init();
                curl_setopt($chbooks, CURLOPT_URL, $urlbooks);
                curl_setopt($chbooks, CURLOPT_FAILONERROR, 1);
                curl_setopt($chbooks, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($chbooks, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($chbooks, CURLOPT_TIMEOUT, 15000000);
                curl_setopt($chbooks, CURLOPT_SSL_VERIFYPEER, 0);
                $returnedbooks = curl_exec($chbooks);
                curl_close($chbooks);
                //@$xmlbooks = simplexml_load_string($returned);


                $filebooks=$custid."/data/books.xml";
		$returnedbooks=str_replace("<identifier.other>IDEAL/","<identifier.other>kitapIDEAL/",$returnedbooks);
                file_put_contents($filebooks, $returnedbooks, LOCK_EX);
                          
		
	
  
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

?>