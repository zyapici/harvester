<?php
if(isset($_REQUEST["file"]))
{
	$file=$_REQUEST["file"];
}
if(isset($_REQUEST["custid"]))
{
	$custid=$_REQUEST["custid"];
}

$mydirectory = opendir("$custid/$file");
while($entryname = readdir($mydirectory))
{
	$dirArray[]=$entryname;
}
$indexCount=count($dirArray);
sort($dirArray);

print("<table border=1 cellpadding=5 cellspacing=0>
        <tr><th>FileName</th>
	        <th>FileSize</th>
	        <th>Date</th>
	   </tr>
");
for($index=0; $index < $indexCount; $index++)
{
	if(substr("$dirArray[$index]",0,1)!=".")
	{
	 print("<tr><td><a href=".$custid."/".$file."/".$dirArray[$index].">$dirArray[$index]</a></td>");
	           print("<td>"); print(filesize($custid."/".$file."/".$dirArray[$index])." bytes"); print("</td>");
	           
	           
		       print("<td>"); print(date ("F d Y H:i:s", filemtime($custid."/".$file."/".$dirArray[$index])+3600)); print("</td>");
     print("</tr>");
   
    }  	   

}


?>