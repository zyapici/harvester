<?php        
error_reporting(1); 
    	set_time_limit(1000000000);
        date_default_timezone_set('Europe/Istanbul');
       
		if(isset($_REQUEST["url"]))
		{
			$url=$_REQUEST["url"];
		}
		if(isset($_REQUEST["custid"]))
		{
			$custid=$_REQUEST["custid"];
		}
		 
                unlink($custid."/info/status");
                $filestatus=$custid."/info/status";
		file_put_contents($filestatus, "getting records...", LOCK_EX);
                
 /////////////////////////////////////////LOOK_AT_TOTAL/////////////////////////////////////////// 

					
		$urltotal="".$url."/webservis.php?islem=sMarcYordamKKN&token=5c947748411d1f36d85039f2ff56a0ab";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_URL, $urltotal);
                curl_setopt($ch, CURLOPT_REFERER, $urltotal);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			
                $returned = curl_exec($ch);
			
                curl_close($ch);
				$returned= str_replace("﻿", "", $returned);
                $json_response_total = json_decode($returned);
      
		$total=$json_response_total->KAYITSAYISI;
		 
		 
                
////////////////////////////////////END_LOOK_AT_TOTAL/////////////////////////////////////
		 
                
                
                $fileallmark=$custid."/marc/allmark(records)_first.mrk";
                
///////////////////////////////DOWNLOAD_RECORDS_5000_BY_5000/////////////////////////////////
                //$total=500;
		
                for($i=0;$i<$total;$i=$i+50)
		{
		        ///////// get KKN ///////////
                                $curlURL="".$url."/webservis.php?islem=sMarcYordamKKN&token=5c947748411d1f36d85039f2ff56a0ab&start=".$i."&rows=50";
                                $curl = curl_init();
                                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($curl, CURLOPT_URL, $curlURL);
                                curl_setopt($curl, CURLOPT_REFERER, $curlURL);
                                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);

                                $returnedjson5000 = curl_exec($curl);
                                curl_close($curl);
								$returnedjson5000 = str_replace("﻿", "", $returnedjson5000);
                                $array5000 = json_decode($returnedjson5000,true);
                              
                                $array5000KKN=$array5000["VERI"]["yordamKKN"];
                               
                                $array5000KKNurl="";
                                for($kkn=0;$kkn<count($array5000KKN);$kkn=$kkn+1)
                                {
                                    $array5000KKNurl=$array5000KKNurl.$array5000KKN[$kkn]."|";
                                }
                                $array5000KKNurl=substr($array5000KKNurl, 0, -1);
                                
                               // echo $array5000KKNurl;
                                
                                /////////get mrk records with KKN
                                $curlURLmrk="".$url."/webservis.php?islem=sMarcKaydiGonder&yordamKKN=".$array5000KKNurl."&token=5c947748411d1f36d85039f2ff56a0ab";
                                $curlmrk = curl_init();
                                curl_setopt($curlmrk, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($curlmrk, CURLOPT_RETURNTRANSFER, TRUE);
                                curl_setopt($curlmrk, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($curlmrk, CURLOPT_URL, $curlURLmrk);
                                curl_setopt($curlmrk, CURLOPT_REFERER, $curlURLmrk);
                                curl_setopt($curlmrk, CURLOPT_CONNECTTIMEOUT, 60);

                                $returnedjson5000mrk = curl_exec($curlmrk);
                                curl_close($curlmrk);
								$returnedjson5000mrk= str_replace("﻿", "", $returnedjson5000mrk);
                                $array5000mrk = json_decode($returnedjson5000mrk,true);
                                
                                for($mrk=0;$mrk<count($array5000mrk["VERI"]["MarcMrk"]);$mrk=$mrk+1)
                                {
                                    $mrkfix= str_replace("=LDR    ","=LDR  ",$array5000mrk["VERI"]["MarcMrk"][$mrk]);                                    
                                    $mrkfix= str_replace("=001    ","=001  ",$mrkfix); 
                                    $mrkfix= str_replace("=003    ","=003  ",$mrkfix);
                                    $mrkfix= str_replace("=005    ","=005  ",$mrkfix);
                                    $mrkfix= str_replace("=007    ","=007  ",$mrkfix);
                                    $mrkfix= str_replace("=008    ","=008  ",$mrkfix);
                                    $mrkfix= str_replace(" 0_ $","  0_$",$mrkfix);
                                    $mrkfix= str_replace(" _0 $","  _0$",$mrkfix);
                                    $mrkfix= str_replace(" 00 $","  00$",$mrkfix);
                                    $mrkfix= str_replace(" 04 $","  04$",$mrkfix);
                                    $mrkfix= str_replace(" __ $","  __$",$mrkfix);
                                    $mrkfix= str_replace(" 1_ $","  1_$",$mrkfix);
                                    $mrkfix= str_replace(" _1 $","  _1$",$mrkfix);
                                    
                                    $mrkfix= str_replace(" 10 $","  10$",$mrkfix);
                                    $mrkfix= str_replace(" 40 $","  40$",$mrkfix);
                                    $mrkfix= str_replace(" 14 $","  14$",$mrkfix);
                                        
                                   
                                    
                                 file_put_contents($fileallmark, $mrkfix."\r\n\r\n", FILE_APPEND | LOCK_EX);
                                   
                                }
                                
    	
                }
                
                $handlecatalog=fopen($custid."/marc/allmark(records)_first.mrk","r");
                $fileallmarkload=fopen($custid."/marc/allmark(records).mrk","w");
   
                if($handlecatalog)
                {
                    $countline=0;
					$countline856=0;
                    while(($line=fgets($handlecatalog))!==FALSE)
                   {
                        if(strlen($line) > 9 || $line=="=007  t\n")
                        {
                            if(strpos($line,"=LDR  ")>-1 && $countline>0)
							{
                                 fwrite($fileallmarkload,"\r\n");
								 $countline856=0;
							}
                            if(strpos($line,"=001  ")>-1)
                            {
                                 fwrite($fileallmarkload,str_replace("=001  ",'=902  __$a',$line));
                                 continue;
                            }
                            if(strpos($line,"=902  __$")>-1)
                            {
                                 fwrite($fileallmarkload,str_replace('=902  __$a','=001  ',$line));
                                 continue;
                            }
                            if(strpos($line,"=856  40")>-1 && strpos($line,"sayfa=sayfaArama")>-1)
                                 continue;
							
							if(strpos($line,"=856  40")>-1 && strpos($line,"udata:image")>-1)
                                 continue;
							
                            if(strpos($line,"=856 41 ")>-1)
							{
								$countline856=$countline856+1;
								if($countline856>20)
									continue;
								else
								{
									fwrite($fileallmarkload,str_replace('=856 41 ','=856  41',$line));
									continue;
								}
							}
							
							if(strpos($line,"=505  ")>-1)
							{
							
							    $lastewrite=str_replace("........",' ',$line);
								$lastewrite=str_replace("……",' ',$lastewrite);
								$lastewrite=str_replace("$x",' ',$lastewrite);
								
								fwrite($fileallmarkload, substr($lastewrite, 0, 9200));
								continue;
							}
							
							$lastwrite=str_replace("“",'',$line);
							$lastwrite=str_replace("”",'',$lastwrite);
							$lastwrite=str_replace("Ã¶",'',$lastwrite);
							$lastwrite=str_replace("Ã¤",'',$lastwrite);
							$lastwrite=str_replace("ÃŸ",'',$lastwrite);
							$lastwrite=str_replace("æ",'',$lastwrite);
							$lastwrite=str_replace("........",' ',$lastwrite);
							$lastwrite=str_replace("……",' ',$lastwrite);
							$lastwrite=str_replace("Ã³",'',$lastwrite);
							
                            fwrite($fileallmarkload, substr($lastwrite, 0, 9200));
                        }
                       $countline=$countline+1; 
                   }
                }
 
                fclose($handlecatalog);
                fclose($fileallmarkload);
       
    unlink($custid."/info/status");
    $filestatus=$custid."/info/status";
    file_put_contents($filestatus, "getting records was finished.", LOCK_EX);

   
