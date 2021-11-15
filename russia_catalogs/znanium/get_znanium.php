<?php

header('Content-Type: text/html; charset=utf-8');
error_reporting(0);

set_time_limit(0);

 
               
               
# Make sure to keep alive the script when a client disconnect.
ignore_user_abort(true);
//error_reporting(0);

//Get necessary customerID from Znanium

if(isset($_REQUEST["custid"]))
{
        $custid=$_REQUEST["custid"];
}

unlink("../".$custid."/info/status");
$filestatus="../".$custid."/info/status";
file_put_contents($filestatus, "getting znanium records...", LOCK_EX);

if(isset($_REQUEST["customerId"]) && $_REQUEST["customerId"]!="")
    {
    

        if(isset($_REQUEST["customerId"]))
        {
                $customerId=$_REQUEST["customerId"];
        }
        //$customerId = "145"; // St. Petersburg State Univ

        //Retrieve BookIds from Collection
        $collectionURL="https://znanium.com/api/ebsco/get-documents-for-contragent?contragent_id=".$customerId;

        //Make sure to use cookiejar from the initial login
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $collectionURL);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp64\www\cookie1.txt');
		curl_setopt($ch, CURLOPT_USERAGENT, '"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36"');
		 

        $returnedCollection = curl_exec($ch);
        curl_close($ch);
 

        //Parse BookIDs 
        $book_ids_parsed = simplexml_load_string($returnedCollection);
		if($book_ids_parsed!=false)
		{
        //Set Accumlator for MRK output
        $MRKOutput = "";
        #$counter="0";
        #Iterate through book ids
        foreach($book_ids_parsed as $book_id){
                #if($counter < 10){
                #Set Cache location to pass to book retriever
                $cacheLocation = 'c:\\wamp64\\www\\russia_catalogs\\znanium\\tmp\\'.$book_id->id.'.txt';
                #If the checkCache function returns active, use the cached record, if not, download the record
                if(checkCache($book_id->id, $cacheLocation) == "Active"){
                        $MRKOutput = $MRKOutput.file_get_contents($cacheLocation);
                }
                else {
                        $MRKOutput = $MRKOutput.getMetadata($book_id->id,$cacheLocation);
                }
                #$counter = $counter + 1;
                #}

        }
        #Write the metdata to disk
        file_put_contents("../".$custid."/mrk/znanium.mrk",$MRKOutput);

          unlink("../".$custid."/info/status");
                       $filestatus="../".$custid."/info/status";
                       file_put_contents($filestatus, "getting znanium records was finished.", LOCK_EX);

		}
		
		else
{
   unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "znanium mrc file was created", LOCK_EX);
} 
               
  }   
else
{
   unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "znanium mrc file was created", LOCK_EX);
}  
       
function checkCache($id,$cacheLocation) {
//Given an ID, this function checks to see whether it is cached and whether the cached item is less than 30 days old
	
	if(file_exists($cacheLocation)) {
	//if it is cached, check to see whether it is less than thirty days old
		if (filemtime($cacheLocation) < ( time() - ( 30 * 24 * 60 * 60 ) ) ) {
		// If older than thirty days, delete the file and return a message 
		unlink($cacheLocation);
		return "Stale";
		}
		//Use cached data
		else {
			return "Active";
		}
	}
	else {
		return "Uncached";
	}
}


#Test Item
#$id = "357263";
#getMetadata($id, 'c:\\wamp64\\www\\znanium\\tmp\\'.$id.'.txt');

function getMetadata($id,$cacheLocation) {
//Get metadata for uncached records, build the cache, and output marc

	$getMetadataURL = 'https://znanium.com/api/ebsco/get-document-metadata?id='.$id;

	//Make sure to use cookiejar from the initial login
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $getMetadataURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp64\www\cookie1.txt');
		curl_setopt($ch, CURLOPT_USERAGENT, '"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36"');

	$metadataResponse = curl_exec($ch);
	curl_close($ch);
		
	#Parse XML
	$metadataResponse = simplexml_load_string($metadataResponse);
	
	#Set accumulator
	$metadataMRKOutput = "";
	
	#LDR
	if(!empty($metadataResponse->PRODUCT_TYPE)){
		$LDR67 = "";
		#Journals
		if($metadataResponse->PRODUCT_TYPE->ID == '3'){
			$metadataMRKOutput = $metadataMRKOutput."=LDR  00000nab 2200000 a 45000\r\n";
		}
		elseif($metadataResponse->PRODUCT_TYPE->ID == '4' || $metadataResponse->PRODUCT_TYPE->ID == '9'){
			$metadataMRKOutput = $metadataMRKOutput."=LDR  00000nai 2200000 a 45000\r\n";
		}
		#everything else
		else{
			$metadataMRKOutput = $metadataMRKOutput."=LDR  00000nam 2200000 a 45000\r\n";
		}
	}
	else{
		#incase product type is blank, as LDR is required
		$metadataMRKOutput = $metadataMRKOutput."=LDR  00000nam 2200000 a 45000\r\n";
	}

	
	#001
	
	if(!empty($metadataResponse->ID)){
		$metadataMRKOutput = $metadataMRKOutput."=001  znanium_".$metadataResponse->ID."\r\n";
	}
	
	#008
	if(!empty($metadataResponse->PUBLICATION_YEAR)){
		$year008 = sanitizeText($metadataResponse->PUBLICATION_YEAR);
	}
	else{
		$year008 = "    ";
	}
	if(!empty($metadataResponse->LANGUAGE)){
		$language = sanitizeText(substr(strtolower(sanitizeText($metadataResponse->LANGUAGE)),0,3));
	}
	else{
		$language = "rus";
	}
	
	$metadataMRKOutput = $metadataMRKOutput."=008        t".$year008."                        ".$language."  \r\n";

	
	#020
	if(!empty($metadataResponse->ISBN)){
		$metadataMRKOutput = $metadataMRKOutput."=020  \\\\\$a".str_replace('-', "", $metadataResponse->ISBN)."\r\n";
	}
	if(!empty($metadataResponse->ISBN_ONLINE)){
		$metadataMRKOutput = $metadataMRKOutput."=020  \\\\\$a".str_replace('-', "", $metadataResponse->ISBN_ONLINE)."\r\n";
	}
	#022
	if(!empty($metadataResponse->ISSN)){
		$metadataMRKOutput = $metadataMRKOutput."=022  \\\\\$a".str_replace('-', "", $metadataResponse->ISSN)."\r\n";
	}
	
	#041
	if(!empty($language)){
		$metadataMRKOutput = $metadataMRKOutput. "=041  \\\\\$a".$language."\r\n";
	}
	unset($language);
	unset($year008);
	
	#100 and 700
	#Authors are in a single field that is '.' delimited
	if(!empty($metadataResponse->AUTHORS)){
		$authors = explode(',', $metadataResponse->AUTHORS);
		
		$firstAuthor = True;
		foreach($authors as $author){
			if($firstAuthor == True){
				$metadataMRKOutput= $metadataMRKOutput."=100  1\\\$a".sanitizeText($author)."\r\n";
				$firstAuthor = False;
			}
			else{
				$metadataMRKOutput= $metadataMRKOutput."=700  1\\\$a".sanitizeText($author)."\r\n";
			}
		}
	}
	#245
	if(!empty($metadataResponse->TITLE)){
		$metadataMRKOutput= $metadataMRKOutput."=245  10\$a".sanitizeText($metadataResponse->TITLE);
		if(!empty($metadataResponse->SUBTITLE)){
			$metadataMRKOutput= $metadataMRKOutput."\$b".sanitizeText($metadataResponse->SUBTITLE);
		}
		$metadataMRKOutput= $metadataMRKOutput."\r\n";
	}
	#260
	if(!empty($metadataResponse->CITY) || !empty($metadataResponse->CITY) || !empty($metadataResponse->PUBLICATION_YEAR)){
		$metadataMRKOutput= $metadataMRKOutput."=260  \\\\";
		if(!empty($metadataResponse->CITY)){
			$metadataMRKOutput= $metadataMRKOutput."\$a".sanitizeText($metadataResponse->CITY);
		}
		if(!empty($metadataResponse->PUBLISHER)){
			$metadataMRKOutput = $metadataMRKOutput."\$b".sanitizeText($metadataResponse->PUBLISHER);
		}
		if(!empty($metadataResponse->PUBLICATION_YEAR)){
			$metadataMRKOutput= $metadataMRKOutput."\$c".sanitizeText($metadataResponse->PUBLICATION_YEAR);
		}
		$metadataMRKOutput= $metadataMRKOutput."\r\n";
	}
	#300
	if(!empty($metadataResponse->PAGES)){
		$metadataMRKOutput= $metadataMRKOutput."=300  \\\\\$a".sanitizeText($metadataResponse->PAGES)." с.\r\n";
	}
	#520
	if(!empty($metadataResponse->ABSTRACT)){
		$metadataMRKOutput= $metadataMRKOutput."=520  \\\\\$aAннотация: ".sanitizeText($metadataResponse->ABSTRACT)."\r\n";
	}
		if(!empty($metadataResponse->EDUCATION_LEVEL)){
		$metadataMRKOutput= $metadataMRKOutput."=521  \\\\\$aУровень образования: ".sanitizeText($metadataResponse->EDUCATION_LEVEL)."\r\n";
	}
	if(!empty($metadataResponse->BIBLIO_RECORD)){
		$metadataMRKOutput= $metadataMRKOutput."=524  \\\\\$aБиблиографическая ссылка: ".sanitizeText($metadataResponse->BIBLIO_RECORD)."\r\n";
	}

	#655
	if($metadataResponse->PRODUCT_TYPE->ID != '3' &&  $metadataResponse->PRODUCT_TYPE->ID != '4' && $metadataResponse->PRODUCT_TYPE->ID != '9'){
		$metadataMRKOutput= $metadataMRKOutput."=655  \\0\$aElectronic books\r\n";
	}
	if(!empty($metadataResponse->PRODUCT_TYPE->NAME)){
		$metadataMRKOutput= $metadataMRKOutput."=655  \\0\$a".sanitizeText($metadataResponse->PRODUCT_TYPE->NAME)."\r\n";
	}
	#653
	if(!empty($metadataResponse->THEMES)){
		foreach($metadataResponse->THEMES as $theme_group){
			$themes_group_items = explode(". ",(string)$theme_group->item);
			foreach($themes_group_items as $theme){
				$metadataMRKOutput= $metadataMRKOutput."=653  \\0\$a".sanitizeText($theme)."\r\n";
			}
		}
	}
	
	#903
	$metadataMRKOutput= $metadataMRKOutput."=903  \\\\\$aЗнаниум\r\n";
	
	#856
	if(!empty($metadataResponse->URL)){
		$metadataMRKOutput= $metadataMRKOutput."=856  \\0\$u".$metadataResponse->URL."\$zПолный текст - ЭБС Знаниум\r\n";
	}
	#956 Book cover
	if(!empty($metadataResponse->COVER)){
		$metadataMRKOutput= $metadataMRKOutput."=956  \\\\\$u".$metadataResponse->COVER."\$zCover Image\r\n";
	}
	$metadataMRKOutput= $metadataMRKOutput."\r\n";
	
	#Write MRK to Cache
	file_put_contents($cacheLocation,$metadataMRKOutput);
	
	#Return MRK text to program
	return $metadataMRKOutput;
}

function sanitizeText($dirtyText) {
	return trim(strip_tags(str_replace(array("\n", "\r"), ' ', $dirtyText)));
} 
