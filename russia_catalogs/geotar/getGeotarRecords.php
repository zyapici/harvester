<?php

header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);
ignore_user_abort(true);


if(isset($_REQUEST["custid"]))
{
        $custid=$_REQUEST["custid"];
}

if(isset($_REQUEST["collectionId"]) && $_REQUEST["collectionId"]!="")
{

        if(isset($_REQUEST["collectionId"]))
        {
                $collectionIds=$_REQUEST["collectionId"];
        }



           unlink("../".$custid."/info/status");
           $filestatus="../".$custid."/info/status";
           file_put_contents($filestatus, "getting Geotar records...", LOCK_EX);


           $filemrk="../".$custid."/mrk/geotar.mrk";

           $collectionIdsArray=explode("|", $collectionIds);
           foreach($collectionIdsArray as $collectionId)
           {

           $collectionURL="https://www.studentlibrary.ru/cgi-bin/mb4x?usr_data=gdaccessdata(book,select-all(".$collectionId."))";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $collectionURL);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
            $returnedCollection = curl_exec($ch);
            curl_close($ch);

            //Parse BookIDs 
            $book_ids_parsed = simplexml_load_string($returnedCollection);


            foreach($book_ids_parsed->xpath('/document/varlist//var') as $var){ 

                $bookId = $var['id'];
                $book_metadata = getBook($bookId);
                if ($book_metadata === false)
                {
                        echo "Failed loading XML: \n";
                }  
                else
                {


                    //Main Publication Class (Book or Journal)


                    $year="";

                     //Year of Publication
                        if (isset($book_metadata->xpath("/book/meta/var[@name='year']/string")[0]))
                        {
                                $year = (string)$book_metadata->xpath("/book/meta/var[@name='year']/string")[0];

                        }

                        //First determine Publication Type Code -- need to look up in look-up table using helper function
                        $pubtype="";
                        $main_pubtype="";
                        if (isset($book_metadata->xpath("/book/meta/var[@name='pubtype']/string")[0]))
                        {
                                $pubtype = (string)$book_metadata->xpath("/book/meta/var[@name='pubtype']/string")[0];
                                $pubtype = getPubType($pubtype);
                        }

                        //Next determine general pubtype book or journal. Many bkt1s are actually journals so we check for string match of russian word for journal on pubtype
                        if (isset($book_metadata->xpath("/book/@type")[0]))
                        {
                                $main_pubtype = (string)$book_metadata->xpath("/book/@type") [0];
                                if(isset($pubtype) && strpos($pubtype, "журнал") !==False){
                                        $main_pubtype = 'Journal';
                                }
                                elseif ($main_pubtype == 'jrn1') {
                                        $main_pubtype = 'Journal';
                                }
                                else {
                                        $main_pubtype = 'Book';
                                }

                                if($main_pubtype=="Journal")
                                {
                                    file_put_contents($filemrk, "=LDR  00000nas 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
                                    file_put_contents($filemrk, "=008        t".$year."                        rus  \r\n", FILE_APPEND | LOCK_EX);
                                }    
                                else
                                {
                                    file_put_contents($filemrk, "=LDR  00000nam 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
                                    file_put_contents($filemrk, "=008        t".$year."                        rus  \r\n", FILE_APPEND | LOCK_EX);
                                }


                        }
						
						else
						{
							        file_put_contents($filemrk, "=LDR  00000nam 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
                                    file_put_contents($filemrk, "=008        t".$year."                        rus  \r\n", FILE_APPEND | LOCK_EX);
						}




                    //Unique ID
                    if (isset($book_metadata->xpath("/book/@id")[0]))
                        {
                                $unique_id = (string)$book_metadata->xpath("/book/@id") [0];
                                file_put_contents($filemrk, "=001  geotar_".$unique_id."\r\n", FILE_APPEND | LOCK_EX);
                        }

                     //ISBN     
                    if (isset($book_metadata->xpath("/book/meta/var[@name='isbn']/string")[0]))
                        {
                                $isbn = (string)$book_metadata->xpath("/book/meta/var[@name='isbn']/string")[0];
                                file_put_contents($filemrk, "=020  \\\\\$a".$isbn."\r\n", FILE_APPEND | LOCK_EX);
                        }


                        //Then we try to deduce the existence of an ISSN
                       if(isset($unique_id) && $main_pubtype=="Journal"){
                                        if (preg_match("/[0-9]{4}\-[0-9]{4}/",  $unique_id)) {
                                                $issn = preg_replace("/[0-9]{4}\-[0-9]{4}/", ''. $unique_id);
                                                file_put_contents($filemrk, "=022  \\\\\$a".$issn."\r\n", FILE_APPEND | LOCK_EX);
                                        }
                                }

                    //Classification Codes
                    foreach ($book_metadata->xpath('/book/meta/var[@name="udk"]/string') as $subject_code)
                    {
                            if (isset($subject_code[0]))
                            {
                                    $classification_number = "УДК " . (string)$subject_code[0];
                                    file_put_contents($filemrk, "=084  \\\\\$a".trim($classification_number)."\r\n", FILE_APPEND | LOCK_EX);
                            }
                    }
                    foreach ($book_metadata->xpath('/book/meta/var[@name="bbk"]/string') as $subject_code)
                    {
                            if (isset($subject_code[0]))
                            {
                                    $classification_number = "ББК " . (string)$subject_code[0];
                                    file_put_contents($filemrk, "=084  \\\\\$a".trim($classification_number)."\r\n", FILE_APPEND | LOCK_EX);
                            }
                    }


                   //Authors - Preferred source is authors_idx
                    if (isset($book_metadata->xpath("/book/meta/var[@name='authors_idx']/string")[0]))
                        {
                                $authors = explode(",", (string)$book_metadata->xpath("/book/meta/var[@name='authors_idx']/string")[0]);
                                $increment_author = 1;
                                foreach ($authors as $an_author)
                                {
                                        $author = $an_author;
                                        if (strlen($author) > 0)
                                        {
                                                file_put_contents($filemrk, "=100  1\\\$a".trim($author)."\r\n", FILE_APPEND | LOCK_EX);
                                                $increment_author = $increment_author + 1;
                                        }
                                }
                        }
                    elseif (isset($book_metadata->xpath("/book/meta/var[@name='authors']/string")[0]))
                        {
                                $authors = explode(",", (string)$book_metadata->xpath("/book/meta/var[@name='authors']/string")[0]);
                                $increment_author = 1;
                                foreach ($authors as $an_author)
                                {
                                        $author = $an_author;
                                        if (strlen($author) > 0)
                                        {
                                                file_put_contents($filemrk, "=100  1\\\$a".trim($author)."\r\n", FILE_APPEND | LOCK_EX);
                                                $increment_author = $increment_author + 1;
                                        }
                                }
                        }
                    elseif (isset($book_metadata->xpath("/book/meta/var[@name='fauthor']/string")[0]))
                        {
                                $authors = explode(",", (string)$book_metadata->xpath("/book/meta/var[@name='fauthor']/string")[0]);
                                $increment_author = 1;
                                foreach ($authors as $an_author)
                                {
                                        $author = $an_author;
                                        if (strlen($author) > 0)
                                        {
                                                file_put_contents($filemrk, "=100  1\\\$a".trim($author)."\r\n", FILE_APPEND | LOCK_EX);
                                                $increment_author = $increment_author + 1;
                                        }
                                }
                        }

                     //Title use bibliography first as it contains complete journal information and subtitles   
                    if (isset($book_metadata->xpath("/book/meta/var[@name='bibliography']/string")[0]))
                        {
                                $title = (string)$book_metadata->xpath("/book/meta/var[@name='bibliography']/string")[0];
                                file_put_contents($filemrk, "=245  14\$a$title\r\n", FILE_APPEND | LOCK_EX);
                        }
                    else if (isset($book_metadata->xpath("/book/title/string")[0]))
                        {
                                $title = (string)$book_metadata->xpath("/book/title/string")[0];
                                file_put_contents($filemrk, "=245  14\$a$title\r\n", FILE_APPEND | LOCK_EX);
                        } 


                         $publisher="";
                        //Publisher
                        if (isset($book_metadata->xpath("/book/meta/var[@name='publisher_text']/string")[0]))
                        {
                                $publisher = (string)$book_metadata->xpath("/book/meta/var[@name='publisher_text']/string")[0];

                        }
                        file_put_contents($filemrk, "=260  \\\\\$b$publisher,\$c$year\r\n", FILE_APPEND | LOCK_EX);

                       //Number of Pages
                        $pages="";
                        if (isset($book_metadata->xpath("/book/meta/var[@name='ppages']/string")[0]))
                        {
                                $pages = (string)$book_metadata->xpath("/book/meta/var[@name='ppages']/string") [0];

                        }

                        //Physical Description
                        $physical_desc="";
                        if (isset($book_metadata->xpath("/book/meta/var[@name='ratio']/string")[0]))
                        {
                                $physical_desc = (string)$book_metadata->xpath("/book/meta/var[@name='ratio']/string")[0];


                        }
                         file_put_contents($filemrk, "=300  \\\\\$a$pages\$c$physical_desc\r\n", FILE_APPEND | LOCK_EX);






                        //Abstract
                        if (isset($book_metadata->xpath("/book/meta/var[@name='annotation']/string")[0]))
                        {
                                $abstract = (string)$book_metadata->xpath("/book/meta/var[@name='annotation']/string")[0];
                                file_put_contents($filemrk, "=520  3\\\$a$abstract\r\n", FILE_APPEND | LOCK_EX);
                        }


                    //Subject Headings

                        //Create Headings array
                        $subjects = array();
                        foreach ($book_metadata->xpath('/book/classifications/area/tag/string') as $subject_code){
                                $subjects[] = (string)$subject_code[0];
                        }

                        //Deduplicate headings array
                        $subjects = array_unique($subjects);

                        //Process subjects
                        foreach ($subjects as $subject){
                                file_put_contents($filemrk, "=650  \\0\$a".trim ($subject)."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        //pubtype in 655
                        file_put_contents($filemrk, "=655  \\0\$a$pubtype\r\n", FILE_APPEND | LOCK_EX);

                        if($main_pubtype==="Book")
                            file_put_contents($filemrk, "=655  \\0\$aElectronic books.\r\n", FILE_APPEND | LOCK_EX);

                        
                           //URL to Cover and check to see if it is a real image
                    if (isset($book_metadata->xpath("/book/meta/attachments/cash/attach[@id='avatar']/@file")[0])){

                                $image_name = (string)$book_metadata->xpath("/book/meta/attachments/cash/attach[@id='avatar']/@file")[0];

                                $book_cover = "https://www.studentlibrary.ru/cache/book/" . $bookId . "/-1-".$image_name;

                                if (@GetImageSize($book_cover)) {
                                         file_put_contents($filemrk, "=956  \\\\\$u$book_cover\r\n", FILE_APPEND | LOCK_EX);
                                } 
                        }
                        

                    //URL To Full Text
                    $full_text_url = "https://www.studentlibrary.ru/book/" . $bookId . ".html";
                    file_put_contents($filemrk, "=856  \\0\$u$full_text_url\$zOnline Access\r\n", FILE_APPEND | LOCK_EX);


                      //Get Book Chapters and Links to Book Chapters
                        foreach ($book_metadata->xpath("/book/chapters/chapter") as $chapter)
                        {
                                $chapter_title = (string)$chapter->xpath("string") [0];
                                $chapter_Id = (string)$chapter['id'][0];
                                $chapter_link = "https://www.studentlibrary.ru/doc/" . $chapter_Id . ".html";
                                if (strlen($chapter_title) > 0 && strlen($chapter_link) > 0)
                                {

                                        file_put_contents($filemrk, "=856  \\0\$z$chapter_title\$u$chapter_link\r\n", FILE_APPEND | LOCK_EX);
                                }
                        }


                    file_put_contents($filemrk, "=903  \\\\\$aКонсультант студента (Геотар)\r\n", FILE_APPEND | LOCK_EX);

                  





                    file_put_contents($filemrk, "\r\n", FILE_APPEND | LOCK_EX);



                }


          }

        }

         fclose($filemrk);




          unlink("../".$custid."/info/status");
          $filestatus="../".$custid."/info/status";
          file_put_contents($filestatus, "getting Geotar records was finished.", LOCK_EX);


}

else
{
  unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "Geotar mrc file was created", LOCK_EX);
}

function getBook($bookId)
{
    $bookURL = "https://www.studentlibrary.ru/cgi-bin/mb4x?usr_data=gdaccessdata(book,book-xml(,," . $bookId . "))";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $bookURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
	$returnedBook = curl_exec($ch);
	curl_close($ch);
	
	//Clean illegal things from Getoar XML
	$returnedBook = html_entity_decode($returnedBook);
	$returnedBook = str_replace('<br>', ' ', $returnedBook);
	$returnedBook = str_replace('<br />', ' ', $returnedBook);
	$returnedBook = str_replace('</br>', ' ', $returnedBook);
	$returnedBook = str_replace('<p>', ' ', $returnedBook);
	$returnedBook = str_replace('</p>', '', $returnedBook);
	$returnedBook = str_replace('&CURREN;', '¤', $returnedBook);
	$returnedBook = str_replace('&NBSP;', ' ', $returnedBook);
	$returnedBook = str_replace('&LAQUO;', '"', $returnedBook);
	$returnedBook = str_replace('&RAQUO;', '"', $returnedBook);
	$returnedBook = str_replace('&CCEDIL;', 'Ç', $returnedBook);
	$returnedBook = str_replace('&amp;', '&', $returnedBook);


	$returnedBook = stripInvalidXml($returnedBook);
        $returnedBook = str_replace('&', 'и', $returnedBook);
        $returnedBook  = iconv("UTF-8","UTF-8//IGNORE",$returnedBook);
	//Check if it is legal XML now
        echo $bookId."\r\n<br>";
            
	return $book_metadata = simplexml_load_string($returnedBook);
        
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



function stripInvalidXml($value)
//Remove illegal xml values
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
    return $ret;
}