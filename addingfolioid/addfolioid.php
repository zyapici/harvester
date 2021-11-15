<?php
       
if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "adding folio ids...", LOCK_EX);
    

        set_time_limit(0);
       
        $datalogin='{"password":"u!pobQ@F7tHRRqUw#7mut98ku!9suU","username":"folio-libris-rtac"}';
            $headers = [];
            $chtoken = curl_init();
            $tokenurl="https://okapi-chalmers.folio.ebsco.com/authn/login";
            curl_setopt($chtoken, CURLOPT_URL, $tokenurl);
            curl_setopt($chtoken, CURLOPT_POSTFIELDS, $datalogin); 
            curl_setopt($chtoken, CURLOPT_FAILONERROR, 1);
            curl_setopt($chtoken, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($chtoken, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($chtoken, CURLOPT_TIMEOUT, 1500);
            curl_setopt($chtoken, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chtoken, CURLOPT_HEADER, 1);
            curl_setopt($chtoken, CURLOPT_HEADERFUNCTION,
                  function($curl, $header) use (&$headers)
                  {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) // ignore invalid headers
                      return $len;

                    $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                    return $len;
                  }
            );
            curl_setopt($chtoken, CURLOPT_HTTPHEADER, array(
            "x-okapi-tenant: fs00001000",
            "Content-Type: application/json"  
            ));
            
            $returnedtoken = curl_exec($chtoken);
            curl_close($chtoken);
             //echo $headers["x-okapi-token"][0];
        
       
$myDirectory = opendir($custid."/download_mrk/");
while($entryName = readdir($myDirectory)) {
        $dirArray[] = $entryName;
}
closedir($myDirectory);
$indexCount = count($dirArray);
        
        

for($i=0;$i<$indexCount;$i=$i+1)
{
        $handlecatalog=fopen($custid."/download_mrk/".$dirArray[$i],"r");
        $writecatalog=fopen($custid."/upload_mrk/".$dirArray[$i],"w");
        $errorscatalog=fopen($custid."/log/errors".$dirArray[$i].date("Y.m.d").".mrk","w");
        fwrite($writecatalog, "");
        
        if($handlecatalog)
        {
            $catalogrecord="";
            while(($line=fgets($handlecatalog))!==FALSE)
            {
                if(strpos($line,"=LDR  ")!== false)
                {
                    if(strpos($catalogrecord,"=999  ff")== false)
                        fwrite($errorscatalog, $catalogrecord);
                    else
                        fwrite($writecatalog, $catalogrecord);
                    $catalogrecord="";
                    $catalogrecord=$catalogrecord.$line;
                }
                else if(strpos($line,"=001  ")!== false)
                {
                    $catalogrecord=$catalogrecord.$line;
                    $librisid=str_replace("\r\n", "", $line);
                    $librisid=str_replace("=001  ", "", $librisid);
                    $folioid=getfolioid($librisid,$headers["x-okapi-token"][0]);
                    if($folioid!=="")
                        $catalogrecord=$catalogrecord."=999  ff\$i".$folioid."\r\n";
                }
                else
                    $catalogrecord=$catalogrecord.$line;
            }
            
            if(strpos($catalogrecord,"=999  ff")== false)
                fwrite($errorscatalog, $catalogrecord);
            else
                fwrite($writecatalog, $catalogrecord);
        }
}
        
        
        
        
        
        
        
        //echo getfolioid($_REQUEST["id"]);
        function getfolioid($librisid,$xokapitoken)
        {
             
            $ch = curl_init();
            //$urlfolioid="https://okapi-fse-eu-central-1.folio.ebsco.com/inventory/instances?limit=1&query=identifiers=%22value%22:%20%22".$librisid."%22,%20%22identifierTypeId%22:%20%22925c7fb9-0b87-4e16-8713-7f4ea71d854b%22";
			
	    //$urlfolioid='https://okapi-fse-eu-central-1.folio.ebsco.com/inventory/instances?limit=1&query=(identifiers%20%3D%2F%40value%2F%40identifierTypeId%3D%2228c170c6-3194-4cff-bfb2-ee9525205cf7%22,%22'.$librisid.'%22)';
            //inventory/instances?limit=1&query=(identifiers =/@value/@identifierTypeId="28c170c6-3194-4cff-bfb2-ee9525205cf7","'.$librisid.'")';
            $urlfolioid='https://okapi-chalmers.folio.ebsco.com/inventory/instances?limit=30&query=(identifiers%20%3D%2F%40value%2F%40identifierTypeId%3D%224f3c4c2c-8b04-4b54-9129-f732f1eb3e14%22%20%22'.$librisid.'%22%20or%20identifiers%20%3D%2F%40value%2F%40identifierTypeId%3D%2228c170c6-3194-4cff-bfb2-ee9525205cf7%22%20%22'.$librisid.'%22)'; 
	     
   //inventory/instances?limit=30&query=(identifiers =/@value/@identifierTypeId="4f3c4c2c-8b04-4b54-9129-f732f1eb3e14" "RECORD_ID" or identifiers =/@value/@identifierTypeId="28c170c6-3194-4cff-bfb2-ee9525205cf7" "RECORD_ID")
//https://okapi-fse-eu-central-1.folio.ebsco.com/inventory/instances?limit=30&query=(identifiers =/@value/@identifierTypeId="28c170c6-3194-4cff-bfb2-ee9525205cf7" "22444887") 
			
            curl_setopt($ch, CURLOPT_URL, $urlfolioid);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1500);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-okapi-tenant: fs00001000",
            "x-okapi-token: ". $xokapitoken
          ));
            $returned = curl_exec($ch);
            curl_close($ch);
            //print_r($returned);
            
            $data = json_decode($returned,true);
            $ID="";
            if(isset($data['instances'][0]['id']))
            {
               $ID = $data['instances'][0]['id'];
            			//print_r($data);
            }
            return $ID; 
            
     
            
        }
        
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "adding folio ids is completed.", LOCK_EX);

   