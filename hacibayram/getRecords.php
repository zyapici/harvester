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
                
                
                 $filepublications=$custid."/data/data.xml";
               
           
                
                $urltotal="https://lib.hacibayram.edu.tr/modules/yayinlar_export.php";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urltotal);
                curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $returned = curl_exec($ch);
                curl_close($ch);
                $xml = simplexml_load_string($returned);
                header("content-type: text/xml; charset=utf-8");
                 file_put_contents($filepublications, '<?xml version="1.0" encoding="UTF-8"?>', FILE_APPEND | LOCK_EX);  
                 file_put_contents($filepublications, '<EISIR:data xmlns:EISIR="namespaceplaceholder.com">', FILE_APPEND | LOCK_EX);  
                
                $i=1;
                 foreach($xml->yayin as $yayin)
                {
                     
                     file_put_contents($filepublications, '<EISIR:record recordStatus="load" pubStatus="published">', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, '<EISIR:identifierMain>'.$i.'</EISIR:identifierMain>', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, '<EISIR:author><EISIR:nameDisplay>'.$yayin->yazar.'</EISIR:nameDisplay></EISIR:author>', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, '<EISIR:titleDisplay>'.$yayin->ad.'</EISIR:titleDisplay>', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, '<EISIR:datePublished>'.$yayin->tarih.'</EISIR:datePublished>', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, '<EISIR:typeDoc>'.$yayin->tür.'</EISIR:typeDoc>', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, '<EISIR:URL>'.$yayin->link.'</EISIR:URL>', FILE_APPEND | LOCK_EX); 
                     file_put_contents($filepublications, "</EISIR:record>", FILE_APPEND | LOCK_EX); 
                     $i=$i+1;
                }
                file_put_contents($filepublications, '</EISIR:data>', FILE_APPEND | LOCK_EX); 
                          
		
	
  
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

?>