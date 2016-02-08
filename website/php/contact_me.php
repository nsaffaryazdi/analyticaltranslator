<?php 
// check if fields passed are empty 

$name = $_POST["name"]; 
$email = $_POST["email"]; 
$message = $_POST["message"];      

$to = "xpheres@lingoworld.eu"; 
$subject = "Contact form submitted by:  $name"; 
$messagebody = "$message";
$header = "From: $email";     
 
 mail($to,$subject,$messagebody,$header); return true;             
?>