<?php
//Retrive Metadata from University Library Online Given the client_id of a joint customer

//Load related functions
include 'ubo_book.php';

header('Content-Type: text/html; charset=utf-8');

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



            set_time_limit(100000000);

            # Make sure to keep alive the script when a client disconnect.
            //ignore_user_abort(true);
            //error_reporting(0);


             unlink("../".$custid."/info/status");
               $filestatus="../".$custid."/info/status";
               file_put_contents($filestatus, "getting UBO records...", LOCK_EX);

               $filemrk="../".$custid."/mrk/ubo.mrk";
               

            //Authenticate: We must athenticate on their endpoint which will send a cookie to the cookiejar
            $authenticationURL="https://biblioclub.ru/services/users.php?users_action=auth&users_login=duglas&users_pass=TwNLnCE3";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $authenticationURL);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:\wamp64\www\cookie.txt');
           // curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:\wamp\www\cookie.txt');

            $authenticationResponse = curl_exec($ch);
            //echo $authenticationResponse;

            curl_close($ch);


            //Process Client Data If general authentication authenication if successful we receive '1' and proceed to get the items 
            if ($authenticationResponse == '1') {
                
               

                    //Get client ID from University Library Online
                    //$client_id = '124830';
                    $client_id = $clientId;
                    //UBO has asked us to cache its records for thirty days, so the first step for us is to prepare for caching

                    //Declare two arrays to collection 
                    $unCachedIds = array();
                    $cachedIds = array();

            //Retrieve subscription list.
                    //Set counters for subscritpion retrieval using a recursive loop. We get the total number of items on the first iteration.
                    //We check for IDs 10000 at a time
                    $firstRecord = 0;
                    $recordPageSize = 10000;

                    //Iterate through pages and find the total size on first iteration
                    do {
                            //Form query for collection list
                            $getCollectionURL = "https://biblioclub.ru/services/service.php?page=users&m=FetchSubsStateData&parse&out=json&users_oid=".$client_id."&users_from=".$firstRecord."&users_cnt=".$recordPageSize;

            //Make sure to use cookiejar from the initial login
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $getCollectionURL);
                            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp64\www\cookie.txt');
                            //curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\wamp\www\cookie.txt');
                            $collectionResponse = curl_exec($ch);
                            curl_close($ch);
							
                            //FetchSubsStateData returns a json object of book ids based given a user id.
                            //FetchSubsStateData returns JSON, so we decode it.
							
                            $collectionResponse = json_decode($collectionResponse);

                            //var_dump($collectionResponse);
                            //On first iteration, retrieve total records available
                            if (!isset($totalRecords)) {
                                    $totalRecords = $collectionResponse->info->total_found;
                                    //print($totalRecords);
                            }

                           
                            //Determine cached recoreds
                            //Now, we determine whether the item is cached or not. We might add processing of the cache directly here -- but for now we separate it into a differtint step
                            foreach($collectionResponse->ids as $id) {
                                    //Call checkCache to determine status. If the status is active, we push the ID onto the list of cached items, if the cache is stale or the record is uncached we push it on to the unCachedIds array
                                    
                                   $cacheStatus = checkCache($id);
                                    
                                    if($cacheStatus == "Active"){
                                            array_push($cachedIds, $id);
                                    }
                                    else if ($cacheStatus == "Stale") {
                                            array_push($unCachedIds, $id);
                                    }
                                    else {
                                            array_push($unCachedIds, $id);
                                    }
                            }
							
                            //Set paging increment
                            $firstRecord = $firstRecord + $recordPageSize;
                    } while($firstRecord <= $totalRecords);
            }
            
            
            
            //Process Cached Records
            //Iterate through cached records and load each one from cache. ProcessMetadata works on a returned JSON object, so we can use the same function for both cached and uncached
         
      
            foreach($cachedIds as $cachedId) {
              
               $cacheLocation = 'c:\\wamp64\\www\\russia_catalogs\\ubo\\tmp\\'.$cachedId.'.json';	
                   // $cacheLocation = 'c:\\wamp\\www\\russia_catalogs\\ubo\\tmp\\'.$cachedId.'.json';	
                    $cachedRecord = json_decode(file_get_contents($cacheLocation));
                    // file_put_contents($filemrk, "test", FILE_APPEND | LOCK_EX);

                    file_put_contents($filemrk, processMetadata($cachedRecord), FILE_APPEND | LOCK_EX);

            }

            //Process uncached
            //UBO's method GetFields_S directly queries their DB so, they have asked me to retrive records 1000 at a time. As their method accepts an array of IDs, We pass 500 ids at a time into getMetadata. 
            //getMetadata unlinks stale json, retrieves metadata, builds the cache, and calls processMetadata to produce the MARC output

            //Get number of uncached records for iteration.
            $uncachedSize = count($unCachedIds);

            ////Step through records 500 at time
            for($i=500;$i<=$uncachedSize+500;$i=$i+500) {
            	
            	//slice sections of 500 book ids
            	$result = array_slice($unCachedIds,$i-500,500,true);
            	
            	//cast the array into a string joined by commas in order to build the request URL for getMetadata
            	$id_array_string = join(",", $result);
            	
            	//Get new metadata from UBO, build the cache, and output MARC using an embedded call to ProcessMetadata
            	
                    file_put_contents($filemrk, getMetadata($id_array_string), FILE_APPEND | LOCK_EX);
                    
            }


            //print(count($cachedIds) + count($unCachedIds));

            unlink("../".$custid."/info/status");
               $filestatus="../".$custid."/info/status";
               file_put_contents($filestatus, "getting UBO records was finished.", LOCK_EX);

}
else
{
    unlink("../".$custid."/info/status");
  $filestatus="../".$custid."/info/status";
  file_put_contents($filestatus, "UBO mrc file was created", LOCK_EX);
}