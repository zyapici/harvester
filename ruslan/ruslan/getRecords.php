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
                if(isset($_REQUEST["serverURL"]))
		{
			$serverURL=$_REQUEST["serverURL"];
		}
                if(isset($_REQUEST["database"]))
		{
			$database=$_REQUEST["database"];
		}
		
                
                
		 
                unlink("../".$custid."/info/status");
                $filestatus="../".$custid."/info/status";
		file_put_contents($filestatus, "getting records...", LOCK_EX);
      
		
                  
  $filemrk="../".$custid."/mrk/ruslan.mrk";            
//$serverURL = "https://ruslan.utmn.ru";
//$database = "BOOKS";

//Prime Starting Record for Paging
$startRecord ="1";

do {
	$collectionURL=$serverURL."/rrs-web/db/".$database."?operation=searchRetrieve&version=1.2&query=ncip.bibliographicRecordIdentifier=*&startRecord=".$startRecord."&maximumRecords=100";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $collectionURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp64\www\cookieruslan.txt');

	$returnedCollection = curl_exec($ch);
	curl_close($ch);

	//Local Test
	//$returnedCollection = file_get_contents("C:\\wamp64\\www\\ruslan\\103.xml");

	//Parse RusMARC
	$metaDataResponse = simplexml_load_string($returnedCollection);

	//Retrieve namespaces
	$ns = $metaDataResponse->getNamespaces(true);
	
	//Get next starting record
	if(@count($metaDataResponse->children($ns['ns3'])->nextRecordPosition)>0){
		$startRecord = $metaDataResponse->children($ns['ns3'])->nextRecordPosition;
	}
	//On the final page there is no nextRecordPosition --so we set startRecord to 0
	else{
		$startRecord = 0;
	}

	#Iterate through books

	//First deal with namespaced nodes and iterate
	$metaDataResponse = $metaDataResponse->children($ns['ns3'])->records;

	foreach($metaDataResponse->children($ns['ns3'])->record as $record){
		#Process metadata and add to text output
		
		$record = $record->children($ns['ns3'])->recordData->children();

		
		$MRKOutput = getMetadata($record,$database,$serverURL);
		//echo $MRKOutput;
                  file_put_contents($filemrk, $MRKOutput."\r\n", FILE_APPEND | LOCK_EX);
	}
}
//Change to startRecord > 0 for full run
while($startRecord > 0);
//while($startRecord < 100);
    
    
    
    
    

                  
                  
                  
  
    unlink("../".$custid."/info/status");
    $filestatus="../".$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

    
    
    function getMetadata($record,$database,$server) {
#Process metadata for each record -- basically we are just decomposing it from XML into UNIMARC .mrk
		

	#Set accumulator
	$metadataMRKOutput = "";

	#LDR
	$metadataMRKOutput = $metadataMRKOutput."=LDR  ".$record[0]->leader->length.$record[0]->leader->status.$record[0]->leader->type.$record[0]->leader->leader07.$record[0]->leader->leader08.$record[0]->leader->leader09.$record[0]->leader->indicatorCount.$record[0]->leader->identifierLenbth->dataBaseAddress.$record[0]->leader->leader17.$record[0]->leader->leader18.$record[0]->leader->leader19.$record[0]->leader->entryMap."\r\n";
	#Iterate through fields and create output
	
	foreach($record[0]->field as $field){
		if (@count($field->children()) == 0){
                    
			
			if($field['id'] == "001"){
				$ILSLink=$server."/pwb/detail?db=".$database."&id=".urlencode($field);
                                $metadataMRKOutput = $metadataMRKOutput."=".$field['id']."  ruslan_".$field."\r\n";
			}
                        else
                            $metadataMRKOutput = $metadataMRKOutput."=".$field['id']."  ".$field."\r\n";
		}
		else{
			$metadataMRKOutput = $metadataMRKOutput."=".$field['id']."  ";
			foreach($field[0]->indicator as $indicatorPart){
				if($indicatorPart == " "){
					$metadataMRKOutput = $metadataMRKOutput."\\";
				}
				else{
					$metadataMRKOutput = $metadataMRKOutput.$indicatorPart;
				}
			}
			foreach($field[0]->subfield as $subfield){
				$metadataMRKOutput = $metadataMRKOutput."$".$subfield['id'].sanitizeText($subfield);
			}
			$metadataMRKOutput = $metadataMRKOutput."\r\n";
		}

	}
	$metadataMRKOutput = $metadataMRKOutput."=956  \\\\\$u".$ILSLink."\$zПодробнее в Электронном каталоге\r\n";;

	
	$metadataMRKOutput= $metadataMRKOutput."\r\n";
	return $metadataMRKOutput;
}

function sanitizeText($dirtyText) {
    return trim(strip_tags(str_replace(array("\n", "\r"), ' ', $dirtyText)));
} 



?>