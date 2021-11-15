<?php
header('Content-Type: text/html; charset=utf-8');
header("refresh: 3600;");
loadPage();

//////////////////////////START_LOAD_PAGE//////////////////////
		function loadPage()
		{
			print("<table class='hovertable'>
			         <tr><th>Publisher ID</th>
			             <th>Publisher Name Name</th>
			             <th>Publisher Link</th>
			             <th>Ftp Username</th>
			             <th>Ftp Password</th>
			             <th>Data Files</th>
			             <th>Get Records</th>
			             <th>Update Interval (Days)</th>
			             <th>Last Upload Date</th>
			             <th>Upload</th>
			             <th>Status</th>
                                     <th>Extra Files</th>
			         </tr>
			");
			$handle = fopen("publishers.txt", "r");
			
			if ($handle) {
			    while (($line = fgets($handle)) !== false) {
			    	$column = explode("|", $line);
			        print("<tr onmouseover=\"this.style.backgroundColor='#ffff66'\" onmouseout=\"this.style.backgroundColor='#d4e3e5'\">
			                 <td>$column[0]</td>
			                 <td>$column[1]</td>
			                 <td><a target='_blank' href='$column[2]'>$column[2]</a></td>
			                 <td>$column[3]</td>
			                 <td>$column[4]</td>
			                 <td>
                                             <a href='details.php?file=data&custid=$column[0]'>
                                                 <img src=images/database.jpg width='40px' height='40px'/>
                                             </a>
                                             <a class='deletedata' onclick='return command(\"delete.php?file=data&custid=$column[0]\")' href='#'>
                                                 <img src=images/delete.png width='40px' height='40px'/>
                                             </a>
                                         </td>");
			                 //$url=str_replace('http://','',$column[2]);
                                         $url=$column[2];
                                         $extra=$column[6];
                                         $extra=  str_replace("\r", "", $extra);
                                         $extra=  str_replace("\n", "", $extra);
			                 print("
			                 <td>
                                         <a class='getrecords' onclick='return command(\"getRecords.php?custid=$column[0]\")' href='#'>
                                              <img src=images/download.png width='80px' height='40px'/>
                                         </a>
                                         </td>
			                 <td class='updateinterval'>$column[5]</td>
			                 <td class='lastuploaddate'>".lastUpdateDate($column[0])."</td>
			                 <td>
                                         <a class='uploaddata' onclick='return command(\"upload.php?username=$column[3]&password=$column[4]&custid=$column[0]&extra=$extra\")' href='#'>
                                             <img src=images/upload.png width='80px' height='40px'/>
                                         </a>
                                         </td>
			                 <td class='status'>".status($column[0])."</td>
                                         <td class='extra'>$column[6]</td>
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
        
    print("<script type=\"text/javascript\" src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js\"></script>
        <script>
    
   
    var deletedataarray = document.getElementsByClassName('deletedata');
    var getrecordsarray = document.getElementsByClassName('getrecords');
    var uploaddataarray = document.getElementsByClassName('uploaddata');
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
              
              if(stat.indexOf('data file was uploaded')>-1) 
              {
                  deletedataarray[i].click();
              }
              if(stat.indexOf('data files were deleted.')>-1 && today.getHours()>16) 
              {
                  getrecordsarray[i].click();
              }
              if(stat.indexOf('getting records was finished.')>-1) 
              {
                  uploaddataarray[i].click();
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

