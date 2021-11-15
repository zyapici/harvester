<?PHP
namespace  Lan\Ebs\Sdk;

//LAN Publishing Ebook Harvester 
//This script harvests client-level ebook metadata from Lan Publishing

//All long requests

set_time_limit(0);

header('Content-Type: text/html; charset=utf-8');

// Package Source: https://github.com/spb-lan/ebs-sdk/
require  'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\Client.php';
require  'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\Common.php';
require  'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\classes\Collection.php';
require  'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\classes\CollectionIterator.php';
require  'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\classes\Model.php';
require 'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\model\Book.php';
require 'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\collection\BookCollection.php';
require 'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\Security.php';
require 'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\helper\Curl.php';
require 'C:\wamp64\www\russia_catalogs\lan\ebs-sdk-master\src\helper\Debuger.php';


use Lan\Ebs\Sdk\Classes\Collection;
use Lan\Ebs\Sdk\Classes\Model;
use Lan\Ebs\Sdk\Collection\ArticleCollection;
use Lan\Ebs\Sdk\Collection\BookCollection;
use Lan\Ebs\Sdk\Collection\IssueCollection;
use Lan\Ebs\Sdk\Collection\JournalCollection;
use Lan\Ebs\Sdk\Collection\UserCollection;
use Lan\Ebs\Sdk\Model\Article;
use Lan\Ebs\Sdk\Model\Book;
use Lan\Ebs\Sdk\Model\Issue;
use Lan\Ebs\Sdk\Model\Journal;
use Lan\Ebs\Sdk\Model\User;

if(isset($_REQUEST["custid"]))
{
        $custid=$_REQUEST["custid"];
}

if(isset($_REQUEST["token"]) && $_REQUEST["token"]!="")
{
    
        unlink("../".$custid."/info/status");
        $filestatus="../".$custid."/info/status";
        file_put_contents($filestatus, "getting lan records...", LOCK_EX);
           
        //Open a file-handle for writing
        //$filemrk =fopen("lan.mrk",'w');
        $filemrk="../".$custid."/mrk/lan.mrk";

        //Token for client authentication: Token can be obtained by contacting Stanislav Tikhonov at Lan Publishing
        $token = $_REQUEST["token"]; // 

        //Initialize client on server
        $client = new Client($token); // инициализация клиента



        ######Book Metadata
        //These are all of the fields currently available for download. If a field does not return data, we receive "" not Null
        $bookFields = [Book::FIELD_NAME, Book::FIELD_AUTHORS, Book::FIELD_ISBN, Book::FIELD_ISBN,Book::FIELD_YEAR, Book::FIELD_PUBLISHER, Book::FIELD_EDITION, Book::FIELD_SPECIAL_MARKS, Book::FIELD_CLASSIFICATION, Book::FIELD_PAGES, Book::FIELD_DESCRIPTION, Book::FIELD_DESCRIPTION,Book::FIELD_AUTHOR_ADDITIONS, Book::FIELD_LANG,Book::FIELD_THUMB,Book::FIELD_URL,Book::FIELD_BIBLIOGRAPHIC_RECORD]; // поля для выборки
        /**
         * List of accessible fields and their meanings
         Доступные поля:
         *      Book::FIELD_NAME = 'name' - Наименование книги Title
         *      Book::FIELD_DESCRIPTION = 'description' - Описание книги
         *      Book::FIELD_ISBN = 'isbn' - ISBN книги
         *      Book::FIELD_YEAR = 'year' - Год издания книги Publication Year
         *      Book::FIELD_EDITION = 'edition' - Издание Edition
         *      Book::FIELD_PAGES = 'pages' - Объем книги Physical Description
         *      Book::FIELD_SPECIAL_MARKS = 'specialMarks' - Специальные отметки Not really useful to us
         *      Book::FIELD_CLASSIFICATION = 'classification' - Гриф Notes on inclusion on government lists for disciplines mapped as 520
         *      Book::FIELD_AUTHORS = 'authors' - Авторы Authors
         *      Book::FIELD_AUTHOR_ADDITIONS = 'authorAdditions' - Дополнительные авторы Additional authors mapped as 245
         *      Book::FIELD_BIBLIOGRAPHIC_RECORD = 'bibliographicRecord' - Библиографическая запись Gost format bibliographic record -- mapped to 520
         *      Book::FIELD_PUBLISHER = 'publisher' - Издательство
         *      Book::FIELD_URL = 'url' - Ссылка на карточку книги URL to book
         *      Book::FIELD_THUMB = 'thumb' - Ссылка на обложку книги URL of book cover
         *      Book::FIELD_LANG = 'lang' - Language of publication ( only available from about 2017 otherwise we are defaulting everything to Russian
         */



        //The API returns a maximum of 1000 records at a time, but has a method to get full count. Here we set the per cycle limit and the offset
        $bookLimit = 500; // Ограничение на выборку данных (максимально 1000)
        $bookOffset = 0; // Смещение выборки данных


        //Because we do not know the size of a customer's entitlement until we open our first book object, we use do while loop that is primed on the first pass by getting the full count of the customer's entitlement with getFullCount method
        do {
                //Open an object that returns the first n items and also gives us the total count
                $bookCollection = new BookCollection($client, $bookFields, $bookLimit, $bookOffset); // коллекция моделей книг

                //On the first pass, prime the variable that will close the while loop by getting the full count of the client's entitlement
                if($bookOffset == 0){
                        $bookCollection_size = $bookCollection->getFullCount();
                }

                #To minimize write, we set an aggregator that holds the output of the entire pass and writes once per pass (so we aggregate 1000 records and write once)
                $mrkOut = "";

                #Cycle through items in the returned BookCollection object
                foreach ($bookCollection as $book) {
                        #LDR
                        //$mrkOut = $mrkOut . "=LDR  00000nam 2200000 a 45000\r\n";
                        file_put_contents($filemrk, "=LDR  00000nam 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
                        #001 Unique ID
                        if($book->id != ""){
                                $id = $book->id;
                                //$mrkOut = $mrkOut . "=001  lan_book_".$id."\r\n";
                                file_put_contents($filemrk,  "=001  lan_book_".$id."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #008 Language and year

                        if($book->year != "" && strlen($book->year)== 4){
                                $bookYear = "t".$book->year;
                        }
                        else {
                                $bookYear = "     ";
                        }

                        if($book->lang != ""){
                                #language codes are returned as 2 digit codes, the helper function translateLanguageCode takes this and returns a three digit code using LC mappings, or false if the code is not found in our dictionary.
                                $languageCode = translateLanguageCode($book->lang);
                                if($languageCode == False){
                                        $languageCode = "   ";
                                }
                        }
                        else{
                                $languageCode = "rus";
                        }

                        //$mrkOut = $mrkOut . "=008        ".$bookYear."                        ".$languageCode."  \r\n";
                        file_put_contents($filemrk,  "=008        ".$bookYear."                        ".$languageCode."  \r\n", FILE_APPEND | LOCK_EX);

                        #020 ISBN
                        if($book->isbn != ""){
                                //$mrkOut = $mrkOut . "=020  \\\\\$a".$book->isbn."\r\n";
                                file_put_contents($filemrk,  "=020  \\\\\$a".$book->isbn."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #041 Language reuse code from 008
                        if($languageCode != "   "){
                                //$mrkOut = $mrkOut . "=041  \\\\\$a".$languageCode."\r\n";
                                 file_put_contents($filemrk,  "=041  \\\\\$a".$languageCode."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #100 Authors
                        if($book->authors != ""){
                                $authors = explode(",",$book->authors);
                                foreach($authors as $author){
                                        if($author != ""){
                                                //sanitizeText trims, removes endline characters and removes html nonsense from strings. We use it when dealing with all string fields 
                                                //$mrkOut = $mrkOut . "=100  1\\\$a".sanitizeText($author)."\r\n";
                                                file_put_contents($filemrk,  "=100  1\\\$a".sanitizeText($author)."\r\n", FILE_APPEND | LOCK_EX);
                                        }
                                }
                        }

                        #245 Title

                        $title_out = "";
                        if($book->name != ""){
                                $title_out = $title_out."\$a".sanitizeText($book->name);
                        }
                        if($book->authorAdditions != ""){
                                $title_out = $title_out."\$c". $book->authorAdditions;
                        }
                        if($title_out != ""){
                                //$mrkOut = $mrkOut . "=245  10".sanitizeText($title_out)."\r\n";
                                file_put_contents($filemrk, "=245  10".sanitizeText($title_out)."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #250 Edition
                        if($book->edition != ""){
                                //$mrkOut = $mrkOut . "=250  \\\\\$a".sanitizeText($book->edition)."\r\n";
                                file_put_contents($filemrk, "=250  \\\\\$a".sanitizeText($book->edition)."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #260 Publication Information
                        $publication_information = "";

                        if($book->publisher != ""){
                                $publication_information = $publication_information."\$b".sanitizeText($book->publisher);
                        }
                        if($book->year != ""){
                                $publication_information = $publication_information."\$c".$book->year;
                        }

                        if($publication_information != ""){
                                //$mrkOut = $mrkOut . "=260  \\\\".sanitizeText($publication_information)."\r\n";
                                file_put_contents($filemrk, "=260  \\\\".sanitizeText($publication_information)."\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #300 Physical Description
                        if($book->pages != ""){
                               // $mrkOut = $mrkOut . "=300  \\\\\$a".$book->pages." стр.\r\n";
                                 file_put_contents($filemrk, "=300  \\\\\$a".$book->pages." стр.\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #520 Annotation
                        $annotationOut = "";
                        if($book->description != ""){
                                        //$mrkOut = $mrkOut . "=520  \\\\\$aАннотация: ".sanitizeText($book->description)."\r\n";
                                        file_put_contents($filemrk, "=520  \\\\\$aАннотация: ".sanitizeText($book->description)."\r\n", FILE_APPEND | LOCK_EX);
                        }
                        if($book->classification != ""){
                                                                                        $mrkOut = $mrkOut . "=520  \\\\\$aГриф: ".sanitizeText($book->classification)."\r\n";
                        }
                        if($book->bibliographicRecord != ""){
                                        //This field returns the GOST citation to this, but it also includes a an access date which always is 00.00.0000 we filter this out, as it does not make sense for our application
                                        //$mrkOut = $mrkOut . "=520  \\\\\$aБиблиографичекское описание: ".sanitizeText(str_replace(" (дата обращения: 00.00.0000). — Режим доступа: для авториз. пользователей.","",$book->bibliographicRecord))."\r\n";
                                        file_put_contents($filemrk, "=520  \\\\\$aБиблиографичекское описание: ".sanitizeText(str_replace(" (дата обращения: 00.00.0000). — Режим доступа: для авториз. пользователей.","",$book->bibliographicRecord))."\r\n", FILE_APPEND | LOCK_EX);
                        }


                        #655 Subjects
                        //Currently we are not getting subject headings. We make in the future.
                        //$mrkOut = $mrkOut . "=655  \\0\$aElectronic books.\r\n";
                         file_put_contents($filemrk, "=655  \\0\$aElectronic books.\r\n", FILE_APPEND | LOCK_EX);

                        #903 Location Code
                        //This is already included in the standard Look-up table we sent to the catalog team
                        //$mrkOut = $mrkOut . "=903  \\\\\$aЛань\r\n";
                        file_put_contents($filemrk, "=903  \\\\\$aЛань\r\n", FILE_APPEND | LOCK_EX);

                        #956 Book cover
                        if($book->thumb != ""){
                                //$mrkOut = $mrkOut . "=956  \\\\\$u".$book->thumb."\$zCover Image\r\n";
                                file_put_contents($filemrk, "=956  \\\\\$u".$book->thumb."\$zCover Image\r\n", FILE_APPEND | LOCK_EX);
                        }

                        #856 Full Text URL

                        if($book->url != ""){
                                //$mrkOut = $mrkOut . "=856  \\0\$u".$book->url."\r\n";
                                  file_put_contents($filemrk, "=856  \\0\$u".$book->url."\r\n", FILE_APPEND | LOCK_EX);
                        }
                        #Empty line
                        //$mrkOut = $mrkOut . "\r\n";
                         file_put_contents($filemrk, "\r\n", FILE_APPEND | LOCK_EX);

                }
                //Write to disk on end of pass
               // fwrite($filemrk, );
                //file_put_contents($filemrk, $mrkOut, FILE_APPEND | LOCK_EX);
                //Increment offset at end of pass
                $bookOffset = $bookOffset + $bookLimit;
        } while($bookOffset <= $bookCollection_size);
        fclose($filemrk);


                unlink("../".$custid."/info/status");
                $filestatus="../".$custid."/info/status";
                file_put_contents($filestatus, "getting lan records was finished.", LOCK_EX);

}

else
{
  unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "lan mrc file was created", LOCK_EX);
}
          

function translateLanguageCode($languageCode){
	$codeBook = json_decode('{"ThreeDigit":["aar","zul","chi","zha","yor","yid","xho","wol","wln","vol","vie","ven","uzb","urd","ukr","uig","tah","twi","tat","tso","tur","ton","tsn","tgl","tuk","tir","tha","tgk","tel","tam","swa","swe","sun","sot","ssw","srp","alb","som","sna","smo","slv","slo","sin","sag","sme","snd","srd","san","kin","rus","rum","run","roh","que","por","pus","pol","pli","pan","oss","ori","orm","oji","oci","nya","nav","nbl","nor","nno","dut","ndo","nep","nde","nob","nau","bur","mlt","may","mar","mon","mal","mac","mao","mah","mlg","lav","lub","lit","lao","lin","lim","lug","ltz","lat","kir","cor","kom","kur","kas","kau","kor","kan","khm","kal","kaz","kua","kik","kon","geo","jav","jpn","iku","ita","ice","ido","ipk","iii","ibo","ile","ind","ina","her","arm","hun","hat","hrv","hmo","hin","heb","hau","glv","guj","grn","glg","gla","gle","fry","fre","fao","fij","fin","ful","per","baq","est","spa","epo","eng","gre","ewe","dzo","div","ger","dan","wel","chv","chu","cze","cre","cos","cha","che","cat","bos","bre","tib","ben","bam","bis","bih","bul","bel","bak","aze","aym","ava","asm","ara","arg","amh","aka","afr","ave","abk"],"TwoDigit":["aa","zu","zh","za","yo","yi","xh","wo","wa","vo","vi","ve","uz","ur","uk","ug","ty","tw","tt","ts","tr","to","tn","tl","tk","ti","th","tg","te","ta","sw","sv","su","st","ss","sr","sq","so","sn","sm","sl","sk","si","sg","se","sd","sc","sa","rw","ru","ro","rn","rm","qu","pt","ps","pl","pi","pa","os","or","om","oj","oc","ny","nv","nr","no","nn","nl","ng","ne","nd","nb","na","my","mt","ms","mr","mn","ml","mk","mi","mh","mg","lv","lu","lt","lo","ln","li","lg","lb","la","ky","kw","kv","ku","ks","kr","ko","kn","km","kl","kk","kj","ki","kg","ka","jv","ja","iu","it","is","io","ik","ii","ig","ie","id","ia","hz","hy","hu","ht","hr","ho","hi","he","ha","gv","gu","gn","gl","gd","ga","fy","fr","fo","fj","fi","ff","fa","eu","et","es","eo","en","el","ee","dz","dv","de","da","cy","cv","cu","cs","cr","co","ch","ce","ca","bs","br","bo","bn","bm","bi","bh","bg","be","ba","az","ay","av","as","ar","an","am","ak","af","ae","ab"]}');
	
	$languageCode = strtolower($languageCode);
	
	$languageIndex = array_search($languageCode, $codeBook->TwoDigit);
	if($languageIndex != False){
		return $codeBook->ThreeDigit[$languageIndex];
	}
	else {
		return False;
	}
}

function sanitizeText($dirtyText) {
	return trim(strip_tags(str_replace(array("\n", "\r"), ' ', $dirtyText)));
} 