<html>
<head>
<title>Search data</title>
</head>

<!--<style>
table#tab1 {
    font-size:10pt;
}

#updated {
    color: red;
    //background-color: grey;
    font-weight:bold;
    font-size:10pt;

}
</style>
-->

<!-- stylesheets -->
<link href="http://lingoworld.eu/codeboard/prettify/prettify.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="http://lingoworld.eu/at/public/css/responsive.gs.12col.css">
<link rel="stylesheet" href="http://lingoworld.eu/at/public/css/animate.min.css">
<link rel="stylesheet" href="http://lingoworld.eu/at/public/css/main.css">

<!-- Google fonts -->
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700,400italic&subset=latin-ext">

<body>

<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/verbs.php"> <td style=\"text-align:right\"> german verbs </td></a></p>
<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/showverbsspanish.php"> <td style=\"text-align:right\"> spanish verbs </td></a></p>
<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/showverbsenglish.php"> <td style=\"text-align:right\"> english verbs </td></a></p>
<p><a href="http://lingoworld.eu/lingoworld/translator/website/php/syntaxmodels.php"> <td style=\"text-align:right\"> Syntax models </td></a></p>

<form method="GET" action="table.php?category=<? echo"$category";?>" >
   
<select name="category" id="category">

<option value="substantive">Substantive</option>
<option value="adjective">adjective</option>
<option value="adverbien">Adverbs</option>
<option value="propersnom">Personal pronoun</option>
<option value="prondemons">demostrative pronoun</option>
<option value="preposition">prepositions</option>
<option value="conjuction">conjuctions</option>
<option value="artdef">definite articles</option>
<option value="artindef">indefinite articles</option>
<option value="artneg">negative articles</option>
<option value="artpos">posesive articles</option>
</select>

<p><input type="submit" /></p>
</form>


<?php

//$language=$_POST['language'];
$category=$_GET['category'];
//$word=$_POST['word'];
//$meaning=$_POST['meaning'];

if(empty($category)){$category="substantive";}


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
$query = "SELECT * FROM $dbname WHERE category ='$category'"; 

// execute query 
$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error()); 


$rowcount=mysql_num_rows($result);
$updatedfound=0;
echo"$rowcount $category"."s found. <br>";

// see if any rows were returned 
if (mysql_num_rows($result) > 0) { 
    // yes 
    // print them one after another 
    echo "<table cellpadding=4 border=1 id='tab1'>"; 
echo "<td>"."<center>"."category"."</td> <td>"."gender"."</td> <td>"."word"."</td> <td>"."case"."</td> <td>"."spanish gender"."</td><td>"."spanish"."</td> <td>"."updated"."</td> <td>"."english"."</td> </tr> <tr>";
    while($row = mysql_fetch_row($result)) { 
     
        if($row[8]=='yes'){echo"<tr id='updated'>";$updatedfound++;}else{echo"<tr>";}

        
    echo "<td>"."<center>".$row[0]."</td> <td>".$row[1]."</td> <td> $row[2]</a> </td> <td>".$row[3]."</td> <td>".$row[7]."</td> <td>".$row[6]."</td> <td>".$row[8]."</td> <td>".$row[9]."</td></tr> <tr>";
    }
    echo "</tr>";
    echo "</table>"; 
} 
else { 
    // no 
    // print status message 
    echo "No rows found!"; 
} 

echo"Updated $category"."s: $updatedfound <br>";
$sum_total=$rowcount-$updatedfound;
echo"$category"."s left: $sum_total";

// free result set memory 
mysql_free_result($result); 

// close connection 
mysql_close(); 

?>

</body>
</html>
