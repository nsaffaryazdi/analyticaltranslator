<html>
<head>
<title>Search data</title>
</head>

<!--<style>
table#tab1 {
    font-size:10pt;
}

#updated {
    /*color: red;*/
    background-color: pink;
    font-weight:bold;
    font-size:10pt;

}
</style>-->

<!-- stylesheets -->
<link href="http://lingoworld.eu/codeboard/prettify/prettify.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="http://lingoworld.eu/at/public/css/responsive.gs.12col.css">
<link rel="stylesheet" href="http://lingoworld.eu/at/public/css/animate.min.css">
<link rel="stylesheet" href="http://lingoworld.eu/at/public/css/main.css">

<!-- Google fonts -->
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700,400italic&subset=latin-ext">


<body>

<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/table.php"> <td style=\"text-align:right\"> Other categories </td></a></p>
<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/showverbsspanish.php"> <td style=\"text-align:right\"> spanish verbs</td></a></p>
<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/showverbsenglish.php"> <td style=\"text-align:right\"> english verbs</td></a></p>
<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/syntaxmodels.php"> <td style=\"text-align:right\"> syntax models</td></a></p>

<form method="post" action="verbs.php" >
   
<select name="category" id="category">

<option value="verb|reg1">Regular verbs without prefix</option>
<option value="verb|reg2">Regular verbs with inseparable prefix</option>
<option value="verb|reg3">Regular verbs with separable prefix</option>
<option value="verb|reg4">Verbs that ends with -ieren</option>
<option value="verb|irreg5">Irregular verbs without prefix</option>
<option value="verb|irreg6">Irregular verbs with inseparable prefix</option>
<option value="verb|irreg7">Irregular verbs with separable perfix</option>
</select>

<p><input type="submit" /></p>
</form>


<?php

//$language=$_POST['language'];
$category=$_POST['category'];
$categoryarray = explode('|', $category);
//$word=$_POST['word'];
//$meaning=$_POST['meaning'];
if(empty($category)){$categoryarray[0]="verb";$categoryarray[1]="reg1";}


// set database server access variables:
      $username=""; 			//Write the username with writing access privilege of your database here
      $password=""; 			//Write the password for your database here
      $db="";				//Write the name of your database here
      $dbname=""; 			//Write the name of your database table here


// open connection 
mysql_connect('localhost', $username, $password) or die(mysql_error());

// select database
mysql_select_db($db) or die(mysql_error());

// create query 
$query = "SELECT * FROM $dbname WHERE category ='$categoryarray[0]' AND subtype='$categoryarray[1]'"; 

// execute query 
$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error()); 

$rowcount=mysql_num_rows($result);
$updatedfound=0;
echo"$rowcount $categoryarray[0]"."s found. <br>";


// see if any rows were returned 
if (mysql_num_rows($result) > 0) { 
    // yes 
    // print them one after another 
    echo"Present";
    echo "<table cellpadding=5 border=1 id='tab1'>"; 
    echo "<td>"."<center>"."Infinitive/4th&6th p."."</td>";
    while($row = mysql_fetch_row($result) AND $row[5]=="infinitive") { 
    
    echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>";
      } 
      echo "</tr>";
}    
else { 
    // no 
    // print status message 
    echo "No rows found!"; 
}

//1st person Ich
echo "<td>"."<center>"."1st p. (Ich)"."</td> <td> </td>";
 while($row = mysql_fetch_row($result) AND $row[4]=="1" AND $row[5]=="present") { 
    

    echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
    echo "</tr>";
    
    //2nd person Du
echo "<tr><td>"."<center>"."2nd p. (Du)"."</td> <td> </td>";
 while($row = mysql_fetch_row($result) AND $row[4]=="2" AND $row[5]=="present") { 
    
    echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
    echo "</tr>";
    
    
    //3rd person er
echo "<tr><td>"."<center>"."3rd p. (Er/sie/es)"."</td> <td> </td>";
 while($row = mysql_fetch_row($result) AND $row[4]=="3" AND $row[5]=="present") { 
    
    echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
    echo "</tr>";

     
//5th person Du
echo "<tr><td>"."<center>"."5th p. (Ihr)"."</td> <td> </td>";
 while($row = mysql_fetch_row($result) AND $row[4]=="5" AND $row[5]=="present") { 
    
    echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
    echo "</tr>";
    

echo "</table>"; 


    echo"Perfekt";
    echo "<table cellpadding=5 border=1 id='tab1'>"; 
    echo "<td>"."<center>"."Perfekt"."</td>";
    while($row = mysql_fetch_row($result) AND $row[5]=="perfekt") { 
        
        echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
      echo "</tr>";

      
echo "</table>";
    // Preteritum
    echo"Preteritum";
    echo "<table cellpadding=5 border=1 id='tab1'>"; 
    
    //1st Person
    echo "<td>"."<center>"."1st p. (Ich)"."</td>";
     while($row = mysql_fetch_row($result) AND $row[4]=="1" AND $row[5]=="preteritum") { 
        
        echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
      echo "</tr>";

      //2nd Person
    echo "<td>"."<center>"."2nd p. (Du)"."</td>";
     while($row = mysql_fetch_row($result) AND $row[4]=="2" AND $row[5]=="preteritum") { 
        
       echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
      echo "</tr>";
      
      //3rd Person
    echo "<td>"."<center>"."3rd p. (Er/Sie/es)"."</td>";
     while($row = mysql_fetch_row($result) AND $row[4]=="3" AND $row[5]=="preteritum") { 
        
        echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
      echo "</tr>";
      
        //4th 6th Person
    echo "<td>"."<center>"."4th/6th p. (Wir)"."</td>";
     while($row = mysql_fetch_row($result) AND $row[4]=="4" AND $row[5]=="preteritum") { 
        
        echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
      echo "</tr>";
      
        //5th Person
    echo "<td>"."<center>"."5th p. (Ihr)"."</td>";
     while($row = mysql_fetch_row($result) AND $row[4]=="5" AND $row[5]=="preteritum") { 
        
        echo "<center>";
    if($row[8]=='yes'){echo"<td id='updated'>";$updatedfound++;} else {echo"<td>";}
    echo"$row[2]</td>"; 
    } 
      echo "</tr>";
      

      
echo "</table>";

echo"Updated $categoryarray[0]"."s: $updatedfound <br>";
$sum_total=$rowcount-$updatedfound;
echo"$categoryarray[0]"."s left: $sum_total";

// free result set memory 
mysql_free_result($result); 

// close connection 
mysql_close(); 

?>

</body>
</html>
