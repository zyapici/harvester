<?php
//ibooks.ru loader
//November 2020
//This loader takes RusMARC XML from ibooks.ru and transforms it into mnemonic MARC21 for loading into EBSCO Discovery Service


header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);
ignore_user_abort(true);

if(isset($_REQUEST["custid"]))
{
        $custid=$_REQUEST["custid"];
}

if(isset($_REQUEST["customerId"]) && $_REQUEST["customerId"]!="")
{

            //Get necessary customer string from ibooks
             if(isset($_REQUEST["customerId"]))
                    {
                        $customerId = $_REQUEST["customerId"]; 
                    }
                    
                       unlink($custid."/info/status");
                       $filestatus=$custid."/info/status";
                       file_put_contents($filestatus, "getting ibooksru records...", LOCK_EX);

            $filemrk=$custid."/mrk/ibooksru.mrk";
            //Retrieve RusMARC
            $collectionURL="https://ibooks.ru/public-downloads/".$customerId.".xml";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $collectionURL);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15000);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp64\www\cookieibooks.txt');

            $returnedCollection = curl_exec($ch);
            curl_close($ch);


            //Parse RusMARC
            $metaDataResponse = simplexml_load_string($returnedCollection) or die("can't load string");


            //Set Accumlator for MRK output
            $MRKOutput = "";
            $counter="0";
            #Iterate through books
            foreach($metaDataResponse as $record){
                    #Process metadata and add to text output
                    $MRKOutput = getMetadata($record);
                    file_put_contents($filemrk, $MRKOutput."\r\n", FILE_APPEND | LOCK_EX);
            }
            
            fclose($filemrk);
            
          unlink($custid."/info/status");
          $filestatus=$custid."/info/status";
          file_put_contents($filestatus, "getting ibooksru records was finished.", LOCK_EX);

          
}

else
{
  unlink($custid."/info/status");
  $filestatus=$custid."/info/status";
  file_put_contents($filestatus, "ibooksru mrc file was created", LOCK_EX);
}


function getMetadata($record) {
#Process metadata for each record

	#Set accumulator
	$metadataMRKOutput = "";

	#LDR
	$metadataMRKOutput = $metadataMRKOutput."=LDR  00000nam 2200000 a 45000\r\n";
	
	#Iterate through fields and create output
	foreach($record as $field){

		#001 Unique Identifier
		
		if(!empty($field['id'] && $field['id'] == "001")){
			$metadataMRKOutput = $metadataMRKOutput."=001  ibooksru_".sanitizeText(str_replace("\\","",$field[0]))."\r\n";
		}
		
		#020 ISBN and 956 book cover
		elseif(!empty($field['id'] && $field['id'] == "010")){

			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$metadataMRKOutput = $metadataMRKOutput."=020  \\\\\$a".sanitizeText(str_replace("-","",$subfield[0]))."\r\n";
				}
			}
		} 
		#100 Primary Author
		elseif(!empty($field['id'] && $field['id'] == "700")){
			$surname = "";
			$givenname = "";
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$surname=$subfield[0];
				}
				if($subfield['id'] == "b"){
					$givenname=$subfield[0];
				}
			}
			if(!empty($surname) || !empty($givenname)){
				$metadataMRKOutput = $metadataMRKOutput."=100  1\\\$a".sanitizeText($surname)." ".sanitizeText($givenname)."\r\n";
			}
		} 
		
		#041 Language of Item
		elseif(!empty($field['id'] && $field['id'] == "101")){
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$metadataMRKOutput = $metadataMRKOutput."=041  \\\\\$a".sanitizeText($subfield[0])."\r\n";
				}
			}
		}
		
		#245 Title
		elseif(!empty($field['id'] && $field['id'] == "200")){
			$a245 = "";
			$f245 = "";
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$a245="\$a".$subfield[0];
				}
				if($subfield['id'] == "f"){
					$f245="\$f".$subfield[0];
				}
			}
			if(!empty($a245) || !empty($f245)){
				$metadataMRKOutput = $metadataMRKOutput."=245  10".sanitizeText($a245).sanitizeText($f245)."\r\n";
			}
		}

		#260 Publisher Information
		elseif(!empty($field['id'] && $field['id'] == "210")){
			$a260 = "";
			$b260 = "";
			$c260 = "";
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$a260="\$a".$subfield[0];
				}
				if($subfield['id'] == "c"){
					$b260="\$b".$subfield[0];
				}
				if($subfield['id'] == "d"){
					$c260="\$c".$subfield[0];
				}
			}
			if(!empty($a260) || !empty($b260) || !empty($c260)){
				$metadataMRKOutput = $metadataMRKOutput."=260  \\\\".sanitizeText($a260).sanitizeText($b260).sanitizeText($c260)."\r\n";
			}
		}
		
		#300 Physical Characteristics
		elseif(!empty($field['id'] && $field['id'] == "215")){
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$metadataMRKOutput = $metadataMRKOutput."=300  \\\\\$a".sanitizeText($subfield[0])."\r\n";
				}
			}
		}
		
		#520 Annotation
		elseif(!empty($field['id'] && $field['id'] == "330")){
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$metadataMRKOutput = $metadataMRKOutput."=520  \\\\\$aАннотация: ".sanitizeText($subfield[0])."\r\n";
				}
			}
		}
		
		#650 Annotation
		elseif(!empty($field['id'] && $field['id'] == "606")){
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$metadataMRKOutput = $metadataMRKOutput."=650  01\$a".sanitizeText($subfield[0])."\r\n";
				}
			}
		}
		#700 Additional Responsiblity
		elseif(!empty($field['id'] && $field['id'] == "701")){
			$surname = "";
			$givenname = "";
			foreach($field as $subfield){
				if($subfield['id'] == "a"){
					$surname=$subfield[0];
				}
				if($subfield['id'] == "b"){
					$givenname=$subfield[0];
				}
			}
			if(!empty($surname) || !empty($givenname)){
				$metadataMRKOutput = $metadataMRKOutput."=700  1\\\$a".sanitizeText($surname)." ".sanitizeText($givenname)."\r\n";
			}
		} 


		#856 Full Text Link
		elseif(!empty($field['id'] && $field['id'] == "856")){
			foreach($field as $subfield){
				if($subfield['id'] == "2" && $subfield[0] == "1"){
					$linkType = "956";
				}
				elseif($subfield['id'] == "2" && $subfield[0] == " "){
					$linkType = "856";
				}
				if($subfield['id'] = "u"){
					$url = $subfield[0];
				}
			}
			if($linkType == "856"){
				$metadataMRKOutput = $metadataMRKOutput."=856  \\0\$u".$url."\$zПолный текст - ЭБС Айбукс.ру\r\n";
			}
			if($linkType == "956"){
				$metadataMRKOutput = $metadataMRKOutput."=956  \\\\\$u".$url."\$zCover Image\r\n";;
			}
		}

	}
	
	#655 eBook
	$metadataMRKOutput= $metadataMRKOutput."=655  \\0\$aElectronic Books\r\n";	
	#DB Name
	$metadataMRKOutput= $metadataMRKOutput."=903  \\\\\$aАйбукс.ру\r\n";

	
	$metadataMRKOutput= $metadataMRKOutput."\r\n";

	
	return $metadataMRKOutput;
}
function sanitizeText($dirtyText) {
	return trim(strip_tags(str_replace(array("\n", "\r","&quot;"), ' ', $dirtyText)));
} 



        