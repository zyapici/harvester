<?php
function checkCache($id) {
//Given an ID, this function checks to see whether it is cached
	
	//Build path to record
	$cacheLocation = 'c:\\wamp64\\www\\russia_catalogs\\ubo\\tmp\\'.$id.'.json';		

	if(file_exists($cacheLocation)) {
	//if it is cached, check to see whether it is less than thirty days old
		if (filemtime($cacheLocation) < ( time() - ( 30 * 24 * 60 * 60 ) ) ) {
		// If older than thirty days, delete the file and fetch metadata Do the deletion  
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

function getMetadata($id) {
//Get metadata for uncached records, build the cache, and output marc
//the function takes a joined array which is then covertedinto an arrayin the request's JSON

	$getMetadataURL = 'https://biblioclub.ru/services/service.php?page=books&m=GetFields_S&out=json&pjson&p={"id": ['.$id.'],"fields":["name","par_name","author","pic","pages","place_publ","publisher","annot","disciplini","genres","keywords_txt","isbn","year","f_journal","izd_type","part","tom","series","otv_izd","bbk","cont_txt","lang","subname","j_name"],"cplx_name":"1"}';

	//Make sure to use cookiejar from the initial login
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $getMetadataURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp64\www\cookie\cookie.txt');

	$metadataResponse = curl_exec($ch);
	curl_close($ch);
		
	
	$metadataResponse = json_decode($metadataResponse);
	
	//Iterate through responses. This should be 500 at a time
	$mrkOutput="";
	foreach($metadataResponse as $record) {
		//var_dump($record);
		
		//Find record ID
		$id = $record->id;
		
		//Set path for Cache
		$cacheLocation = 'c:\\wamp64\\www\\russia_catalogs\\ubo\\tmp\\'.$id.'.json';		
		
		//If the ID is already cached, remove that file 
		if(file_exists($cacheLocation)) {
			unlink($cacheLocation);
		}
		
		//Save the record in the cache
		file_put_contents($cacheLocation,json_encode($record));
				
		//Process the active record to produce metadata
		$mrkOutput = $mrkOutput . processMetadata($record);
	}
	return $mrkOutput;
	
}


function processMetadata($record) {
	
         
        $output="";
    
    
           // var_dump($record);
	
		//print "<ul>";
	
		
		
		//Is book or Journal
		//This needs to be checked once we see what journal records actually look like
                $mainPubType="";
		if(!empty($record->f_journal)) {
			if($record->f_journal == '1') {
				$mainPubType = "Journal";
				
				//Containing Journal
				if(!empty($record->j_name)) {
					$containingJournal = $record->j_name;
				}
				
				//Issue
				if(!empty($record->part)){
					$issue = $record->part;
				}
				
				//Volume
				if(!empty($record->tom)){
					$volume = $record->tom;
				}
			}
			else {
				$mainPubType = "Book";
			}
		}
                
                $year="    ";
                if(!empty($record->year)){
			$year = $record->year;
		}
                
                if($mainPubType=="Journal")
                        {
                            $output=$output."=LDR  00000nas 2200000 a 45000\r\n";
                            $output=$output."=008        t".$year."                        rus  \r\n";
                        }    
                        else
                        {
                            $output=$output."=LDR  00000nam 2200000 a 45000\r\n";
                            $output=$output."=008        t".$year."                        rus  \r\n";
                        }
		
                        //Unique ID
		
		if(!empty($record->id)){
			$uniqueID = $record->id;
			//print("<li>Unique Identifier: ".$uniqueID."</li>");
                        $output=$output."=001  ubo_".$uniqueID."\r\n";
		}
                
                if(!empty($record->isbn)){
			$isbn = $record->isbn;
			//print("<li>Unique Identifier: ".$uniqueID."</li>");
                       $output=$output."=020  \\\\\$a".$isbn."\r\n";
		}
                
               
		
                //Classification Code
		if(!empty($record->bbk)) {
			$classificationCode = 'ББК '.$record->bbk;
                        
                        $output=$output."=084  \\\\\$a".trim($classificationCode)."\r\n";
		}

                
                 
                //Author
		if (!empty($record->author)) {
			$authors = $record->author;
			
			$authors = explode(',', $authors);
			
			foreach($authors as $author){
				$output=$output."=100  1\\\$a".trim($author)."\r\n";
				//print("<li>Author: ".$author."</li>");
			}
		}
		
		
		//Other Responsibility (Editor etc)
		if (!empty($record->otv_izd)) {

			$other_responsible = $record->otv_izd;
			
			$other_responsible = explode(';', $other_responsible);
			
			foreach($other_responsible as $author){
				$output=$output."=100  1\\\$a".trim($author)."\r\n";
				//print("<li>Author: ".trim(ltrim($author,": "))."</li>");
			}
		}
                
                //Title
		if(!empty($record->name)){
			$title = $record->name;
			//print("<li>Title: ".$title."</li>");
                        $output=$output."=245  14\$a$title\r\n";
		}
		//Alternate title
		
		if(!empty($record->par_name)){
			$alt_title = $record->par_name;
		        $output=$output."=246  14\$a$alt_title\r\n";
			//print("<li>Other Title: ".$alt_title."</li>");
		}
                
                //Place of Publication 
                $pubPlace="";
		if(!empty($record->place_publ)) {
			$pubPlace = $record->place_publ;
		}
		
		//Publisher
                $publisher="";
		if(!empty($record->publisher)) {
			$publisher = $record->publisher;
                       
		}
                 $output=$output."=260  \\\\\$a$pubPlace\$b$publisher,\$c$year\r\n";
                
                 
                 //Pages
		if(!empty($record->pages)) {
			$pages = $record->pages." стр.";
                         $output=$output."=300  \\\\\$a$pages\r\n";
		}
                
                //Series
		if(!empty($record->series)) {
			$series = $record->series;
                        $output=$output."=490  1\\\$a$series\r\n";
		}
                
                //Abstract 
		if(!empty($record->annot_txt)) {
			$abstract = $record->annot_txt;
                        $abstract=  str_replace("<br>", "", $abstract);
                        $abstract=  str_replace("\r", "", $abstract);
                        $abstract=  str_replace("\n", "", $abstract);
                        $output=$output."=520  3\\\$a$abstract\r\n";
                        
		}
                
                
                
                //Subjects Three variables contain subject-like things so we build a unified array , dedupe it, and then output it
		$all_subjects = array();
		if(!empty($record->disciplini)) {
			$subjects = $record->disciplini;
			
			$subjects = explode(";", $subjects);
			
			foreach($subjects as $subject){
				array_push($all_subjects, mb_strtolower($subject, 'UTF-8'));
			}
		}
		if(!empty($record->genres)) {
			$subjects = $record->genres;
			
			$subjects = explode(";", $subjects);
			
			foreach($subjects as $subject){
				array_push($all_subjects, mb_strtolower($subject, 'UTF-8'));
			}
		}
		if(!empty($record->keywords_txt)) {
			$subjects = $record->keywords_txt;

			$subjects = explode(";", $subjects);
			
			foreach($subjects as $subject){
				array_push($all_subjects, trim(mb_strtolower($subject, 'UTF-8')));
			}
		}				
		array_unique($all_subjects);
		
		foreach($all_subjects as $subject) {
			//print("<li>Subject: ".$subject."</li>");
                    $output=$output."=650  \\0\$a$subject\r\n";
                    
		}
                
                   if($mainPubType=="Book")
                    $output=$output."=655  \\0\$aElectronic books.\r\n";
                
                
		//Publication Type
		if(!empty($record->izd_type)){
			$publicationType = $record->izd_type;
                        $output=$output."=655  \\0\$a$publicationType\r\n";
		}
		
		
		
		//URL to Cover Image
		if(!empty($record->pic)){
			$imageURL = "http://img.biblioclub.ru/?p=cover&id=".$record->pic;
                        $output=$output."=956  \\0\$u$imageURL\$zCover Image\r\n";
			//print("<li>Cover Image: ".$imageURL."</li>");
		}
		
		//URL to Full Text
		
		$fullTextURL = "https://biblioclub.ru/index.php?page=book_red&id=".$uniqueID;
		//print("<li>Fulltext: ".$fullTextURL."</li>");
                $output=$output."=856  \\0\$u$fullTextURL\$zOnline Access\r\n";
			
                 
                $output=$output."=903  \\\\\$aУниверситетская библиотека онлайн\r\n";
                 
                 
		
		
		
		
	
		
		//Language if language is unsset we default to russian
		//Language is passed as the Russian term not the ISO value ... So ... sigh... we to think about how to handle this, as the language is often missing as well
		 if(!empty($record->lang)){
			 $langs = explode(",", $record->lang);
			 foreach($langs as $lang){
				 $lang;
			 }
		 }
		 else {
			 $lang = "rus";
		 }
		
		
		
		
	//print("</ul>");
                                                                
              return $output."\r\n";                                           
                                                                
	
}