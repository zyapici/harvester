<?php
header('Content-Type: text/html; charset=utf-8');
header("refresh: 150;");
loadPage();

//////////////////////////START_LOAD_PAGE//////////////////////
		function loadPage()
		{
			print("<table class='hovertable'>
			         <tr><th>Customer ID</th>
			             <th>Customer Name</th>
			             <th>Dummy ftp Username</th>
                                     <th>Dummy ftp Password</th>
                                     <th>Ftp Username</th>
			             <th>Ftp Password</th>
			             <th>Log Files</th>
			             <th>Downloaded Dummy Marc Files</th>
                                     <th>Created Marc Files with FolioID</th>
			             <th>Get Dummy Marc Files</th>
                                     <th>Add Folio ID</th>
                                     <th>Update Interval (Days)</th>
			             <th>Last Upload Date</th>
			             <th>Upload</th>
			             <th>Status</th>
                                     <th>Extra Files</th>
			         </tr>
			");
			$handle = fopen("customers.txt", "r");
			
			if ($handle) {
			    while (($line = fgets($handle)) !== false) {
			    	$column = explode(";", $line);
			        print("<tr onmouseover=\"this.style.backgroundColor='#ffff66'\" onmouseout=\"this.style.backgroundColor='#d4e3e5'\">
			                 <td>$column[0]</td>
			                 <td>$column[1]</td>
			                 <td>$column[2]</td>
			                 <td>$column[3]</td>
                                         <td>$column[4]</td>
			                 <td>$column[5]</td>
                                        
			                 <td>
                                         <a href='details.php?file=log1&custid=$column[0]'>
                                           <img src=images/log.png width='40px' height='40px'/>
                                         </a>
                                         <a class='deletelog' onclick='return command(\"delete.php?file=log1&custid=$column[0]\")' href='#'>
                                            <img src=images/delete.png width='40px' height='40px'/>                                             
                                         </a>
                                         </td>
			                 <td>
                                             <a href='details.php?file=download&custid=$column[0]'>
                                                 <img src=images/marc21.gif width='40px' height='40px'/>
                                             </a>
                                             <a class='deletedownloadmarc' onclick='return command(\"delete.php?file=download&custid=$column[0]\")' href='#'>
                                                 <img src=images/delete.png width='40px' height='40px'/>
                                             </a>
                                         </td>
                                         <td>
                                             <a href='details.php?file=upload&custid=$column[0]'>
                                                 <img src=images/marc21.gif width='40px' height='40px'/>
                                             </a>
                                             <a class='deleteuploadmarc' onclick='return command(\"delete.php?file=upload&custid=$column[0]\")' href='#'>
                                                 <img src=images/delete.png width='40px' height='40px'/>
                                             </a>
                                         </td>");
			                 //$url=str_replace('http://','',$column[2]);
                                         $url=$column[2];
                                         $extra=$column[7];
                                         $extra=  str_replace("\r", "", $extra);
                                         $extra=  str_replace("\n", "", $extra);
			                 print("
			                 <td>
                                         <a class='getdummymarcfiles' onclick='return command(\"getDummyMarcFiles.php?dummyftp=$column[2]&password=$column[3]&custid=$column[0]\")' href='#'>
                                              <img src=images/download.png width='80px' height='40px'/>
                                         </a>
                                          <a class='createmrk' target='_blank' onclick='return command(\"createmrk.php?custid=$column[0]\")' href='#'>
                                                 <img src=images/mrk.png width='80px' height='40px'/>
                                         </a>
                                         </td>
			                 
                                        <td>
                                         <a class='addfolioid' onclick='return command(\"addfolioid.php?&custid=$column[0]\")' href='#'>
                                              <img src=images/addfolioid.png width='80px' height='40px'/>
                                         </a>
                                          <a class='createmrc' target='_blank' onclick='return command(\"createmrc.php?custid=$column[0]\")' href='#'>
                                                 <img src=images/mrc.png width='80px' height='40px'/>
                                         </a>
                                         </td>


                                         <td class='updateinterval'>$column[6]</td>
			                 <td class='lastuploaddate'>".lastUpdateDate($column[0])."</td>
			                 <td>
                                         <a class='uploadmrc' onclick='return command(\"upload.php?username=$column[4]&password=$column[5]&custid=$column[0]&extra=$extra\")' href='#'>
                                             <img src=images/upload.png width='80px' height='40px'/>
                                         </a>
                                         </td>
			                 <td class='status'>".status($column[0])."</td>
                                         <td class='extra'>$column[7]</td>
                                         
			               </tr>
			        ");
			    }
			} else {
			    // error opening the file.
			}
			print("</table>");
			fclose($handle);
                       
                     
		}
//////////////////////////END_LOAD_PAGE/////////////////////////

        function lastNumberofRecordILS($custid)
        {
            if(!file_exists($custid."/info/last_number_of_records_ILS"))
		     return "No file";
		  else
		     $lastnumberILS = fopen($custid."/info/last_number_of_records_ILS", "r");
		  if ($lastnumberILS) 
		  {
	         while (($line = fgets($lastnumberILS)) !== false) 
		    {
		      return $line; 
		    }
		  }
		  else
		     return "null";
			 
			 fclose($lastnumberILS);
        }
        function lastNumberofRecordLoaded($custid)
        {
            if(!file_exists($custid."/info/last_number_of_records_Loaded"))
		     return "No file";
            else
		    $lastnumberLoaded = fopen($custid."/info/last_number_of_records_Loaded", "r");
		  if ($lastnumberLoaded) 
		  {
	         while (($line = fgets($lastnumberLoaded)) !== false) 
		    {
		      return $line; 
		    }
		  }
		  else
		     return "null";
			 
			 fclose($lastnumberLoaded);
        }
        function NumberofRecordILS($custid)
        {
            if(!file_exists($custid."/log/number_of_records_ILS"))
		     return "No file";
		  else
		     $numberILS = fopen($custid."/log/number_of_records_ILS", "r");
		  if ($numberILS) 
		  {
	         while (($line = fgets($numberILS)) !== false) 
		    {
		      return $line; 
		    }
		  }
		  else
		     return "null";
			 
			 fclose($numberILS);
        }
        function NumberofRecordLoaded($custid)
        {
             if(!file_exists($custid."/log/number_of_records_Loaded"))
		     return "No file";
		  else
		     $numberLoaded = fopen($custid."/log/number_of_records_Loaded", "r");
		  if ($numberLoaded) 
		  {
	         while (($line = fgets($numberLoaded)) !== false) 
		    {
		      return $line; 
		    }
		  }
		  else
		     return "null";
			 
			 fclose($numberLoaded);
        }
        function lastUpdateDate($custid)
        {
		  if(!file_exists($custid."/info/last_upload_date"))
		     return "No date file";
		  else
		     $lastUpdate = fopen($custid."/info/last_upload_date", "r");
		  if ($lastUpdate) 
		  {
	         while (($line = fgets($lastUpdate)) !== false) 
		    {
		      return $line; 
		    }
		  }
		  else
		     return "null";
			 
			 fclose($lastUpdate);
        }
		
		
		 function status($custid)
        {
		  if(!file_exists($custid."/info/status"))
		     return "No status file";
		  else
		     $status = fopen($custid."/info/status", "r");
		  if ($status) 
		  {
	         while (($line = fgets($status)) !== false) 
		    {
		      return $line; 
		    }
		  }
		  else
		     return "null";
			 
			 fclose($status);
        }
        
    print("<script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js\"></script>
        <script>
    
    var deletelogarray = document.getElementsByClassName('deletelog');
    var deletedownloadmarcarray = document.getElementsByClassName('deletedownloadmarc');
    var deleteuploadmarcarray = document.getElementsByClassName('deleteuploadmarc');
    var getdummymarcfilesarray = document.getElementsByClassName('getdummymarcfiles');
    var createmrkarray = document.getElementsByClassName('createmrk');   
    var addfolioidarray = document.getElementsByClassName('addfolioid'); 
    var createmrcarray = document.getElementsByClassName('createmrc'); 
    var uploadmrcarray = document.getElementsByClassName('uploadmrc');
    var statusarray = document.getElementsByClassName('status');
    var updateintervalarray = document.getElementsByClassName('updateinterval');
    var lastuploaddatearray = document.getElementsByClassName('lastuploaddate');
    var today=new Date();      

    var i;
   
     for (i = 0; i < statusarray.length; i++) 
     {
      var stat = statusarray[i].innerHTML;
      var updateinterval = updateintervalarray[i].innerHTML;
      var lastuploaddate = lastuploaddatearray[i].innerHTML;
      
       date2=new Date(lastuploaddate);
      diffc = today.getTime() - date2.getTime();
      days = Math.round(Math.abs(diffc/(1000*60*60*24)));
     
      
          if(days > updateinterval)
          {
              statusarray[i].bgColor=\"yellow\";
              
              if(stat.indexOf('mrc file was uploaded.')>-1) 
              {
                  deletelogarray[i].click();
              }
              
              if(stat.indexOf('log1 files were deleted.')>-1) 
              {
                  deletedownloadmarcarray[i].click();
              }
              if(stat.indexOf('download files were deleted.')>-1) 
              {
                  deleteuploadmarcarray[i].click();
              }
             
              if(stat.indexOf('upload files were deleted.')>-1) 
              {
                  getdummymarcfilesarray[i].click();
              }
              
              if(stat.indexOf('getting marc records from dummy ftp is completed.')>-1) 
              {
                   createmrkarray[i].click();
              }
              
              if(stat.indexOf('mrk file was created.')>-1) 
              {
                  addfolioidarray[i].click();
              }
              
              if(stat.indexOf('adding folio ids is completed.')>-1) 
              {
                  createmrcarray[i].click();
              }
               if(stat.indexOf('mrc file was created.')>-1) 
              {
                  uploadmrcarray[i].click();
              }
              
          }
         else 
           statusarray[i].bgColor=\"green\";
     }
     
   function command(url)
   {
  
    $.get(url);
    return false;
   }
</script>");
   
?>
<html>
    <head>
     
<!-- Table goes in the document BODY -->
<!-- CSS goes in the document HEAD or added to your external stylesheet -->
<style type="text/css">
table.hovertable {
	font-family: verdana,arial,sans-serif;
	font-size:11px;
	color:#333333;
	border-width: 1px;
	border-color: #999999;
	border-collapse: collapse;
}
table.hovertable th {
	background-color:#c3dde0;
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #a9c6c9;
}
table.hovertable tr {
	background-color:#d4e3e5;
}
table.hovertable td {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #a9c6c9;
}
</style>

    </head>
</html>

