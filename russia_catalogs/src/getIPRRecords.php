<?php

header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);
ignore_user_abort(true);

require '../vendor/autoload.php';
use Iprbooks\Ebs\Sdk\Client;
use Iprbooks\Ebs\Sdk\collections\BooksCollection;
use Iprbooks\Ebs\Sdk\collections\ContentCollection;
use Iprbooks\Ebs\Sdk\models\Content;

$clientId="9458";
$token="!QH&E)q##8s~dfv*etc1SzfLW[G5)2g]";

if(isset($_REQUEST["custid"]))
{
        $custid=$_REQUEST["custid"];
}

if(isset($_REQUEST["clientId"]) && $_REQUEST["clientId"]!="")
{
    

        if(isset($_REQUEST["clientId"]))
        {
                $clientId=$_REQUEST["clientId"];
        }
        if(isset($_REQUEST["token"]))
        {
                $token=$_REQUEST["token"];
        }


           unlink("../".$custid."/info/status");
           $filestatus="../".$custid."/info/status";
           file_put_contents($filestatus, "getting IPR records...", LOCK_EX);



        $filemrk="../".$custid."/mrk/ipr.mrk";


        $client = new Client($clientId, $token);
        $booksCollection = new BooksCollection($client);
        $booksCollection -> get();
        $enumerateBook = 0;
        //$offset =0;
        $offsetMax = $booksCollection->getTotalCount();
        //$offsetMax = 200;
        echo $offsetMax;
        //$breakIndicator = False;




        while ($offset < $offsetMax)
        {

                $booksCollection = new BooksCollection($client);

                $booksCollection

                    ->setLimit(100)

                    ->setOffset($offset);

                $booksCollection -> get();


                foreach ( $booksCollection  as  $book ) 
                {

                     $enumerateBook = $enumerateBook +1;  

                     $Id=$book -> getId();
                     $Title = $book -> getTitle ();
                     $Pubhouse = $book -> getPubhouse();
                     $Pubyear = $book -> getPubyear();
                     $TitleAddititonal = $book -> getTitleAdditional();
                     //$Liability = $book -> getLiability();
                     $Authors = $book -> getAuthors();
                     $Description = $book -> getDescription();
                     $Keywords = $book -> getKeywords();


                     $Pubtype = $book -> getPubtype();
                     $Url=$book -> getUrl();
                     $coverImage = $book -> getImage();




                     file_put_contents($filemrk, "=LDR  00000nam 2200000 a 45000\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=001  iprbooks_".$Id."\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=008        t".$Pubyear."                        rus  \r\n", FILE_APPEND | LOCK_EX);

                       $Authorarray=explode(",",$Authors);
                        foreach($Authorarray as $author)
                          file_put_contents($filemrk, "=100  1\\\$a".trim($author)."\r\n", FILE_APPEND | LOCK_EX);

                     //file_put_contents($filemrk, "=100  1\\\$a$Authors\r\n", FILE_APPEND | LOCK_EX);

                     file_put_contents($filemrk, "=245  14\$a$Title\$b$TitleAddititonal\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=260  \\\\\$b$Pubhouse,\$c$Pubyear\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=520  \\\\\$a$Description\r\n", FILE_APPEND | LOCK_EX);

                     $Keywordarray=explode(",",$Keywords);
                        foreach($Keywordarray as $keyword)
                          file_put_contents($filemrk, "=650  \\0\$a".trim ($keyword)."\r\n", FILE_APPEND | LOCK_EX);



                    // file_put_contents($filemrk, "=650  \\0\$a$Keywords\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=655  \\0\$aElectronic books.\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=655  \\0\$a$Pubtype\r\n", FILE_APPEND | LOCK_EX);
                      file_put_contents($filemrk, "=956  \\\\\$u$coverImage\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=856  \\0\$u$Url\r\n", FILE_APPEND | LOCK_EX);
                     file_put_contents($filemrk, "=903  \\\\\$aIPRBooks\r\n", FILE_APPEND | LOCK_EX);

                   

                     file_put_contents($filemrk, "\r\n", FILE_APPEND | LOCK_EX);
                } 

                $offset = $offset +100;

        }


        fclose($filemrk);

          unlink("../".$custid."/info/status");
          $filestatus="../".$custid."/info/status";
          file_put_contents($filestatus, "getting IPR records was finished.", LOCK_EX);
}

else
{
  unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "IPR mrc file was created", LOCK_EX);
}
 
