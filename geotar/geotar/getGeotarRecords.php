<?php
#Geotar Harvesting Script
#Uses new Geotar api to get bibliographic metadata for Russian customers
#Douglas Heintz 2022-10

header('Content-Type: text/html; charset=utf-8');

#Do not enforce server session time limits or memory limits 
set_time_limit(0);
//ini_set('memory_limit', '-1');

if(isset($_REQUEST["custid"]))
{
        $custid=$_REQUEST["custid"];
}


if(isset($_REQUEST["collectionId"]) && $_REQUEST["collectionId"]!="")
{
	

        #Retrive Session key based on org_id and agr_id:
        $org_id = "ebsco";
        $agr_id = "ebsco";

        #These are the inputs that are variable per client.
			
	if(isset($_REQUEST["collectionId"]))
	{
                $collectionIds=$_REQUEST["collectionId"];
	}
		
	unlink("../".$custid."/info/status");
        $filestatus="../".$custid."/info/status";
        file_put_contents($filestatus, "getting Geotar records...", LOCK_EX);
		   
		   
		
        $complect_ids=array($collectionIds);

        $filemrk =fopen("../".$custid."/mrk/Geotar.mrk",'w');
        $filelog =fopen("../".$custid."/log/errors.txt",'w');

        #Retrieve and store Session Token
        $sessionToken = retrieveXML("http://gate22.studentlibrary.ru/join?org_id=".$org_id."&agr_id=".$agr_id)->{'code'};

        #Retrieve list of books (first increment)
        $increment_size = 10;
        $bookIDcollector = array();

        #Case for clients with real collection_id
        foreach($complect_ids as $complect_id){
                #Case if retrieving all books for EBSCO KB
                $collection_id = (string)retrieveXML("http://gate22.studentlibrary.ru/db?cmd=sel&guide=sengine&tag=kit_content&kit=".$complect_id."&SSr=".$sessionToken)->{"divisions"}->{"division"}->attributes()->{"id"};
                $bookList = retrieveXML("http://gate22.studentlibrary.ru/db?cmd=sel&guide=sengine&tag=division_books&div=".$collection_id."&paginate=".$increment_size."&SSr=".$sessionToken);
                $res_id = $bookList->{'res_id'};
                $total_records = $bookList->{'total'};
                $bookIDcollector = array_merge($bookIDcollector, (array)$bookList->{'list'}->{'data'});

                #Retrieve list of books (remaining increments)
                for($x = $increment_size; $x<= $total_records; $x=$x +$increment_size){
                        $bookList = retrieveXML("http://gate22.studentlibrary.ru/db?cmd=more&guide=sengine&res_id=0&from=".$x."&SSr=".$sessionToken);
                        $bookIDcollector = array_merge($bookIDcollector, (array)$bookList->{'list'}->{'data'});
                }
        }

			$bookIDcollector = array_unique($bookIDcollector);

			foreach($bookIDcollector as $recordorigin){
				
		    
				$cacheLocation = str_replace("/","_",'c:\\wamp64\\www\\geotar\\tmp\\'.$recordorigin.'.txt');
				#If the checkCache function returns active, use the cached record, if not, download the record
				if(checkCache($recordorigin,$cacheLocation) == "Active"){
					fwrite($filemrk, file_get_contents($cacheLocation));
				} 
				else {
					#Go get the record
					//print_r($recordorigin);
					$record = getRecord($recordorigin,$sessionToken);
					
					$metadataCollector = "";
					#LDR
					#Determine publication type, as many items are journal issues
					//First determine Publication Type Code -- need to look up in look-up table using helper function
					$pubtype="";
					$main_pubtype="";
					
				
				
					if (isset($record->xpath("/book/meta/var[@name='pubtype']/string")[0])){
						$pubtype = (string)$record->xpath("/book/meta/var[@name='pubtype']/string")[0];
						$pubtype = getPubType($pubtype);
					}
					//Next determine general pubtype book or journal. Many bkt1s are actually journals so we check for string match of russian word for journal on pubtype
					if (isset($record->xpath("/book/@type")[0])){
							$main_pubtype = (string)$record->xpath("/book/@type") [0];
							if(isset($pubtype) && strpos($pubtype, "журнал") !==False){
									$main_pubtype = 'Journal';
							}
							elseif ($main_pubtype == 'jrn1') {
									$main_pubtype = 'Journal';
							}
							else {
									$main_pubtype = 'Book';
							}

							if($main_pubtype=="Journal"){
								$metadataCollector = $metadataCollector."=LDR  00000nas 2200000 a 45000\r\n";
							}    
							else{
								$metadataCollector = $metadataCollector."=LDR  00000nam 2200000 a 45000\r\n";
							}
					}
					
					else{
								$metadataCollector = $metadataCollector."=LDR  00000nam 2200000 a 45000\r\n";
					}
					
					#001 Unique Identifier
					
					 if (isset($record->xpath("/book/@id")[0])) {
						$unique_id = (string)$record->xpath("/book/@id") [0];
						$metadataCollector = $metadataCollector."=001  geotar_".$unique_id."\r\n";
					}
					#020 ISBN
					if (isset($record->xpath("/book/meta/var[@name='isbn']/string")[0])){
						$isbn = (string)$record->xpath("/book/meta/var[@name='isbn']/string")[0];
						$isbn = preg_replace( '/[^0-9]/', '', $isbn );
						$metadataCollector = $metadataCollector."=020  \\\\\$a".$isbn."\r\n";
						unset($isbn);
					}
					#022 ISSN -- Try to deduce from ID
					if(isset($unique_id) && $main_pubtype=="Journal"){
						if (preg_match("/[0-9]{4}\-[0-9]{4}/",  $unique_id)) {
							$issn = preg_replace("/[0-9]{4}\-[0-9]{4}/", ''. $unique_id);
							$metadataCollector = $metadataCollector."=022  \\\\\$a".$issn."\r\n";
							unset($issn);
						}
					}
					#041 Language Not indicated so we set all records to Russian (requested)
					$metadataCollector = $metadataCollector."=041  \\\\\$arus\r\n";

					#100/700 Author Prefer authors idx
					if (isset($record->xpath("/book/meta/var[@name='authors_idx']/string")[0])){
						$metadataCollector = $metadataCollector.processAuthors(explode(",", (string)$record->xpath("/book/meta/var[@name='authors_idx']/string")[0]));
					}
					elseif (isset($record->xpath("/book/meta/var[@name='authors']/string")[0])){
						$metadataCollector = $metadataCollector.processAuthors(explode(",", (string)$record->xpath("/book/meta/var[@name='authors']/string")[0]));
					}
					elseif (isset($record->xpath("/book/meta/var[@name='fauthor']/string")[0])){
						$metadataCollector = $metadataCollector.processAuthors(explode(",", (string)$record->xpath("/book/meta/var[@name='fauthor']/string")[0]));
					}
					#245 Title use prototype first as it contains complete journal information and subtitles   
					if (isset($record->xpath("/book/meta/var[@name='prototype']/string")[0])){
						$metadataCollector = $metadataCollector."=245  14\$a".(string)$record->xpath("/book/meta/var[@name='prototype']/string")[0]."\r\n";
					}
					elseif (isset($record->xpath("/book/title/string")[0])){
						$metadataCollector = $metadataCollector."=245  14\$a".sanitizeText((string)$record->xpath("/book/title/string")[0])."\r\n";
					} 
					
					#260 Publication Information
					$publisherInformation = "";
					if (isset($record->xpath("/book/meta/var[@name='publisher_text']/string")[0])){
						$publisherInformation = $publisherInformation."\$b".(string)$record->xpath("/book/meta/var[@name='publisher_text']/string")[0];
					}
					if (isset($record->xpath("/book/meta/var[@name='year']/string")[0])){
						$publisherInformation = $publisherInformation."\$c".(string)$record->xpath("/book/meta/var[@name='year']/string")[0];
					}
					if ($publisherInformation != ""){
						$metadataCollector = $metadataCollector."=260  \\\\".sanitizeText($publisherInformation)."\r\n";
						unset($publisherInformation);
					}
					
					#300 Publisher Information
					$publisherInformation = "";
					if (isset($record->xpath("/book/meta/var[@name='ppages']/string")[0])){
						$publisherInformation = $publisherInformation."\$a".(string)$record->xpath("/book/meta/var[@name='ppages']/string") [0];
					}
					if (isset($record->xpath("/book/meta/var[@name='ratio']/string")[0])){
						$publisherInformation = $publisherInformation."\$c".(string)$record->xpath("/book/meta/var[@name='ratio']/string")[0];
					}
					if ($publisherInformation != ""){
						$metadataCollector = $metadataCollector."=300  \\\\".sanitizeText($publisherInformation)."стр.\r\n";
						unset($publisherInformation);
					}
					
					#520 Annotation
					if (isset($record->xpath("/book/meta/var[@name='annotation']/string")[0])){
						$metadataCollector = $metadataCollector."=520  3\\\$aАннотация: ".sanitizeText((string)$record->xpath("/book/meta/var[@name='annotation']/string")[0])."\r\n";
					}
					#521 "Grif"
					if (isset($record->xpath("/book/meta/var[@name='opinion']/string")[0])){
						$metadataCollector = $metadataCollector."=521  \\\\\$aГриф: ".sanitizeText((string)$record->xpath("/book/meta/var[@name='opinion']/string")[0])."\r\n";
					}
					

					#521 Contents (previously done as 856 links -- but that required too much fussing -- now just text)
                                        $chapterloop=0;
					foreach ($record->xpath("/book/chapters/chapter") as $chapter){
						$chapter_title = (string)$chapter->xpath("string") [0];
						if (strlen($chapter_title) > 0 && $chapterloop<100){
							$metadataCollector = $metadataCollector."=521  \\\\\$a".$chapter_title."\r\n";
                                                        $chapterloop=$chapterloop+1;
						}
					}

					#521 BBK and UDK Codes (stored as notes) 
					foreach ($record->xpath('/book/meta/var[@name="udk"]/string') as $subject_code){
						if (isset($subject_code[0])){
							$metadataCollector = $metadataCollector."=521  \\\\\$aУДК: ".sanitizeText((string)$subject_code[0])."\r\n";
						}
					}
					foreach ($record->xpath('/book/meta/var[@name="bbk"]/string') as $subject_code){
						if (isset($subject_code[0])){
							$metadataCollector = $metadataCollector."=521  \\\\\$a".sanitizeText((string)$subject_code[0])."\r\n";
						}
					}
					
					#521 GOST citation
					if (isset($record->xpath("/book/meta/var[@name='bibliography']/string")[0])){
						$metadataCollector = $metadataCollector."=521  \\\\\$aБиблиографическое описание: ".sanitizeText(str_replace("http://gate22","http://www",(string)$record->xpath("/book/meta/var[@name='bibliography']/string")[0]))."\r\n";
					}
					
					
					#650 Subjects
					$subjects = array();
					foreach ($record->xpath('/book/classifications/area/tag/string') as $subject_code){
						$subjects[] = (string)$subject_code[0];
					}
					//Deduplicate headings array
					$subjects = array_unique($subjects);

					//Process subjects
					foreach ($subjects as $subject){
						$metadataCollector = $metadataCollector."=650  \\0\$a".trim ($subject)."\r\n";
					}

					#655
					$metadataCollector = $metadataCollector."=655  \\0\$a".$pubtype."\r\n";

					if($main_pubtype=="Book"){
						$metadataCollector = $metadataCollector."=655  \\0\$aElectronic books\r\n";
					}
					
					#856 Full Text link
					$metadataCollector = $metadataCollector."=856  \\0\$uhttps://www.studentlibrary.ru/book/" . $unique_id . ".html\$zOnline Access\r\n";
					
					#903 EBS Name
					$metadataCollector = $metadataCollector."=903  \\\\\$aКонсультант студента (Геотар)\r\n";

					#956 Book Cover
					if (isset($record->xpath("/book/meta/attachments/cash/attach[@id='avatar']/@file")[0])){

						$image_name = (string)$record->xpath("/book/meta/attachments/cash/attach[@id='avatar']/@file")[0];

						$book_cover = "https://www.studentlibrary.ru/cache/book/" . $unique_id . "/-1-".$image_name;

						$metadataCollector = $metadataCollector."=956  \\\\\$u".$book_cover."\$zBook Jacket\r\n";
					}
					
					$metadataCollector = $metadataCollector."\r\n";
					file_put_contents($cacheLocation, $metadataCollector);
					fwrite($filemrk, $metadataCollector);
				
					
				}
				
			 }
			
			
		
                        
        fclose($filemrk);		
	unlink("../".$custid."/info/status");
        $filestatus="../".$custid."/info/status";
        file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

}

else
{
  unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "Geotar mrc file was created", LOCK_EX);
}

####Helper Functions
function retrieveXML($url_scoped){
#Sends Curl request and retrieves simplexml, running it through a cleaning routine---> Geotar has lots of bad things in data :-(
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_scoped);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
	$returnedCollection_scoped = curl_exec($ch);
	curl_close($ch);
       return simplexml_load_string(stripInvalidXml($returnedCollection_scoped));
         
       
}

function stripInvalidXml($value)
//Remove illegal xml values Deveoped by trial and error
{
    $ret = "";
    $current;
    if (empty($value)) 
    {
        return $ret;
    }

    $length = strlen($value);
    for ($i=0; $i < $length; $i++)
    {
        $current = ord($value{$i});
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF)))
        {
            $ret .= chr($current);
        }
        else
        {
            $ret .= " ";
        }
    }
	$ret = html_entity_decode($ret);
        $ret=preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $ret);
	$ret = str_replace('<br>', ' ', $ret);
//	$ret = str_replace('<br />', ' ', $ret);
//	$ret = str_replace('</br>', ' ', $ret);
//	$ret = str_replace('<p>', ' ', $ret);
//	$ret = str_replace('</p>', '', $ret);
//        
//	$ret = str_replace('&CURREN;', '¤', $ret);
//	$ret = str_replace('&NBSP;', ' ', $ret);
//	$ret = str_replace('&LAQUO;', '"', $ret);
//	$ret = str_replace('&RAQUO;', '"', $ret);
//	$ret = str_replace('&CCEDIL;', 'Ç', $ret);
//	$ret = str_replace('&amp;', '&', $ret);
	
	$ret = trim($ret);
	$ret = iconv("UTF-8", "UTF-8//IGNORE", $ret); // drop all non utf-8 characters

	$ret = preg_replace('/\s+/', ' ', $ret); // reduce all multiple whitespace to a single space
	$ret= preg_replace('/[\x00-\x1F\x7F]/u', '', $ret);
	
    return $ret;
}

function processAuthors($authors){
#Processes authors arrays
	$first_author = True;
	$authorData = "";
	foreach ($authors as $author){
		if (strlen($author) > 0 && $first_author == True ){
			$authorData = $authorData."=100  1\\\$a".sanitizeText($author)."\r\n";
			$first_author = False;
		}
		else{
			$authorData = $authorData."=700  1\\\$a".sanitizeText($author)."\r\n";

		}
	}
	return $authorData;
}


function sanitizeText($dirtyText) {
#final clean-up of text
    return trim(strip_tags(str_replace(array("\n", "\r"), ' ', $dirtyText)));
	
} 


function getRecord($record_id_scoped,$sessionToken_scoped){
	#gets a single record and returns it
	$record_id_scoped = explode("/", $record_id_scoped);
	$record = retrieveXML("http://gate22.studentlibrary.ru/db?cmd=data&guide=".$record_id_scoped[0]."&id=".$record_id_scoped[1]."&SSr=".$sessionToken_scoped);
	return $record;
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
	

function getPubType($scoped_pubcode) {
            //JSON dictionary of pubtype and 008 values

            $pubtypes = '{
              "3.2.4.3.1.7": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "автореферат диссертации",
                "18-21": "|",
                "24-27": "m",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "адресная/телефонная книга",
                "18-21": "|",
                "24-27": "r",
                "35-37": "rus"
              },
              "3.2.4.3.6.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "антология",
                "18-21": "|",
                "24-27": "n",
                "35-37": "rus"
              },
              "3.2.4.3.5.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "афиша",
                "18-21": "|",
                "24-27": 5,
                "35-37": "rus"
              },
              "3.2.4.3.5.3.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "биобиблиографический справочник/словарь",
                "18-21": "|",
                "24-27": "b",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.6": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "биографический справочник/словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.4.1.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "букварь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.6.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "документально-художественное издание",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.5.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "журнал",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.3.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "задачник",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "идеографический словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.3.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "инструктивно-методическое издание",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.2.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "инструкция",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "каталог",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.2.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "каталог аукциона",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.2.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "каталог библиотеки",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "каталог выставки",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.2.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "каталог товаров и услуг",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.1.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 1,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "материалы конференции (съезда, симпозиума)",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.1.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "монография",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.2.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "музейный каталог",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.6.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "научно-художественное издание",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.5.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "научный журнал",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.3.5.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "номенклатурный&#160;каталог",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "орфографический словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "орфоэпический словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.3.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "памятка",
                "18-21": "|",
                "24-27": "n",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "переводной словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.6.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "песенник",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "практикум",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.3.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "практическое пособие",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.3.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "практическое руководство",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.2.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "прейскурант",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.1.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "препринт",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.1.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "пролегомены, введение",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.3.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "промышленный каталог",
                "18-21": "|",
                "24-27": "c",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "проспект",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.5.3.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "путеводитель",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "рабочая тетрадь",
                "18-21": "|",
                "24-27": "n",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.4.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "разговорник",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.4.2.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "самоучитель",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.1.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "сборник научных трудов",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.5.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.5.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "справочник",
                "18-21": "|",
                "24-27": "h",
                "35-37": "rus"
              },
              "3.2.4.3.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "стандарт",
                "18-21": "|",
                "24-27": "u",
                "35-37": "rus"
              },
              "3.2.4.3.1.6": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "тезисы докладов/сообщений научной конференции (съезда, симпозиума)",
                "18-21": "|",
                "24-27": "m",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "терминологический словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "толковый словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.2.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "уставное издание",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.4": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "учебная программа",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "учебник",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.2.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "учебно-методическое пособие",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.2.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "учебное наглядное пособие",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "учебное пособие",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "учебный комплект",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.4.2.5": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": "m",
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "хрестоматия",
                "18-21": "|",
                "24-27": "#",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.6": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "частотный словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "энциклопедический словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.5.1": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "энциклопедия",
                "18-21": "|",
                "24-27": "e",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2.7": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "этимологический словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
              "3.2.4.3.5.2.2": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "языковой словарь",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              },
			  "3.2.5.2.3": {
                "22": "|",
                "23": "o",
                "28": "|",
                "29": 0,
                "30": "|",
                "31": "|",
                "32": "",
                "33": 0,
                "34": "|",
                "38": "#",
                "39": "|",
                "pub_label": "научный журнал",
                "18-21": "|",
                "24-27": "d",
                "35-37": "rus"
              }
            }';

            $pubtypes = json_decode($pubtypes);

            if(isset($pubtypes->$scoped_pubcode->pub_label)) {
                    return $pubtypes->$scoped_pubcode->pub_label;
            }
            else {
                    return $scoped_pubcode;
            }
}