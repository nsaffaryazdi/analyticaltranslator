<?php /*

Analytical translator.

Written by "xpheres" 2014, xpheres@lingoworld.eu

More information on http://www.lingoworld.eu
Repository: https://github.com/xpheres/analyticaltranslator

License:

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see http://www.gnu.org/licenses.
    
    This program comes with ABSOLUTELY NO WARRANTY.

This General Public License does not permit incorporating your program
    into proprietary programs. If your program is a subroutine library, you
    may consider it more useful to permit linking proprietary applications
    with the library. If this is what you want to do, use the GNU Library
    General Public License instead of this License.

By accepting this licence you agree to give:

    the freedom to use the software for any purpose,
    the freedom to change the software to suit your needs,
    the freedom to share the software with your friends and neighbors, and
    the freedom to share the changes you make.

Please read the file "termsandconditions" for more details and make sure you
attach the GNU General Public License, version 3 (GPL-3.0) terms and conditions
to any copy or modification of this software along with this information.
    
*/

//1. database server access variables:

      $username=""; 			//Write the username with writing access privilege of your database here
      $password=""; 			//Write the password for your database here
      $db="";				//Write the name of your database here
      $dbname=""; 			//Write the name of your database table here

//2. The sentence is received and split into words and assigned to an array.

    $string=$_POST['text'];		//We obtain the input

    $array = explode(' ',$string);	//We split it into words

    countwords($numberofwords,$array);	//3. Count the words

    openandselectdatabase($username,$password,$db);		//4. Connect to the database

    checkeveryword($row,$query,$result,$numberofwords,$array,$dbname,$category,$subtype,$word,$declination,$person,$tense,$spanish,$genero,$english);	//5. We check every word in the database

    syntaxmodels($rowsarray,$numberofwords,$category,$funcion,$case,$wordorder,$addedword);	//6. We check the syntax models

    typeassignation($numberofwords,$type,$subtype);			//7. We assign gender to variable type

    typeverb($numberofwords,$type,$subtype,$prefix);				//8. We clasified verbs
    
    errordetection($numberofwords,$word,$case,$type,$cword);			//9. We check declination concordances on articles 
                
    genderconrcondances($numberofwords,$category,$genero,$case,$type,$type2,$neworder,$wordorder,$spanish);		//10. Translate genders to the target language
    
    reordersyntaxtargetlanguage($numberofwords,$neworder,$addedword,$spanish);	//11. We reorder the sentences by the syntax order for the target language
   
    conjugationconcordances($numberofwords,$category,$word,$person);		//12. We check conjugation concordances
      
    jsonencode($spanish);		//13. Function to send results
    
    erasememorycloseconnection($numberofwords,$result);	//14. Erase memory and close connection



    
    function countwords(&$numberofwords,&$array){
//3. The number of words are counted and the values are saved in a variable.

    $numberofwords=count($array);	//We count the number of words

    if($numberofwords >=10) {$numberofwords=10;} // We establish a limit of 10 words
}


function openandselectdatabase($username,$password,$db){
//4.A connection to the database is made to collect all the values from every row where
    
    // open connection 
	mysql_connect('localhost', $username, $password) or die(mysql_error());

	//$pdodb= new PDo('mysql:host=localhost;dbname=a3683_highlighterDB;charset=utf8',$username,$password);
    
    // select database
	mysql_select_db($db) or die(mysql_error());
}

    
 function checkeveryword(&$row,&$query,&$result,&$numberofwords,&$array,&$dbname,&$category,&$subtype,&$word,&$declination,&$person,&$tense,&$spanish,&$genero,&$english)   
{
    //5.We open a loop and we check first if the words are found in the database
    
    for ($i=0; $i<=$numberofwords-1; $i++) 
    {

	// create query 
	$query[$i] = "SELECT * FROM $dbname where word ='$array[$i]'";
    //foreach($db->query('SELECT * FROM '$dbname' where word=$array[$i]);
	
	// execute query 
	$result[$i] = mysql_query($query[$i]) or die ("Error in query: $query. ".mysql_error());
	

	// see if any rows were returned for word 1 Language
	if (mysql_num_rows($result[$i]) > 0) 
	{ 
	// yes 
	// print them one after another 
	while($row = mysql_fetch_row($result[$i])) {	
	
	
//We assign the values of every columns of the word row
	
	//Every rows correspond to one single word.
	
	$category[$i]=$row[0];		//Categories: Verb,substantive etc.
	$subtype[$i]=$row[1];		//Subtype:Gender for substantives, type of verbs, etc
	$word[$i]=$row[2];		//The word itself
	$declination[$i]=$row[3];	//The case in wich the word is written or possible cases
	$person[$i]=$row[4];		//The person for every verb if applies
	$tense[$i]=$row[5];		//The tense of the verb (present,perfect,preteritum)
	$spanish[$i]=$row[6];		//The spanish translation
	$genero[$i]=$row[7];		//The gender for the spanish word
	//$updated[$i]=$row[8];		//Updated field
	$english[$i]=$row[9];		//English translation
	
	}
	} else { 
	// no 
	// print status message 
	//echo "No rows found!";
	
	}
    }
     
}  


   
    function syntaxmodels (&$rowsarray,&$numberofwords,&$category,&$funcion,&$case,&$wordorder,&$addedword)
{
//6. Syntactical analisis
    
    //Every category of every word will be compared to the syntax models.
    //If the sentence fit on any model, the correspondent values for function and case will be assigned
    
    /*
	  Syntax models:
	  1	2 words		Art + S				Der Mann
	  2	2 words		Pp + V				Ich sehe
	  3	2 words		V  + Pp				sehe ich
	  4	3 words		PP + V + PP			Ich sehe dich
	  5	3 words		Art+ S + V			der Mann ist
	  6	4 words		PP + S + V + Adj		Der Mann kommt schnell
	  7	4 words: 	Pp + V + Art + S		wir sehen den Park
	  8	5 words		PP + V + Art + S + S		Ich gebe den Kindern Geld
	  9	5 words: 	PP + V + Pr + Art + S		ich spreche mit der Frau
	  10	6 words		Ppos + S + V + Pr + Art + S	Mein geschenk liegt auf dem tisch
	  11	6 words 	Art  + S + V + Pr + Art + S	das buch liegt auf dem tisch
	  12	7 words		Pp   + V + Art + S + Art + S	Ich lege das buch auf den tisch	
    */
    
    for ($i=0; $i<=$numberofwords-1; $i++) 
    {
    //We initialize the variable word order with the default order
    $wordorder[$i]=$i;
    }
    
    
    //Model 1:	two words: article & verb z.b: "Der Mann"		NOMINATIVE
	if($category[1]=="substantive" && $numberofwords =="2" && ($category[0]=="artdef" || $category[0]=="artindef" || $category[0]=="artneg" || $category[0]=="artpos")) {
	    $funcion[1]="complement";
	    $funcion[2]="Subject";
	    $case[0]="nominative";
	    $case[1]="nominative";
	
	
    //Model 2:	two words: pronpersonal & verb z. b:"Ich sehe"		NOMINATIVE ACCUSATIVE
	} else if($category[0] == "propersnom" && $category[1] == "verb" && $numberofwords =="2") {
	    $funcion[1]="Subject";
	    $funcion[2]="Predicate";
	    $case[0]="nominative";
	    $case[1]="accusative";
	
	
    //Model 3	two words: veb & proonpersonal z. b:"sehe ich"		ACCUSATIVE NOMINATIVE
	} else if($category[0] == "verb" && $category[1] == "propersnom" && $numberofwords =="2") {
	    $funcion[1]="Predicate";
	    $funcion[2]="Subject";
	    $case[0]="accusative";
	    $case[1]="nominative";
		
	
    //Model 4	three words: pronpersonal & verb & pronpersonal z,b,"Ich sehe dich"	NOMINATIVE ACCUSATIVE
	} else if($category[0] == "propersnom" && $category[1] == "verb" && $category[2] === "propersnom" && $numberofwords =="3") {
	    $funcion[1]="Subject";
	    $funcion[2]="Verb";
	    $funcion[3]="Direct Object";
	    $case[0]="nominative";
	    $case[1]="accusative";
	    $case[2]="accusative";
	
    //Model 5	three words: article & substantive & verb z,b,"der mann ist"		NOMINATIVE ACCUSATIVE
	} else if($category[0] == "artdef" && $category[1] == "substantive" && $category[2] === "verb" && $numberofwords =="3") {
	    $funcion[1]="complement";
	    $funcion[2]="subject";
	    $funcion[3]="predicate";
	    $case[0]="nominative";
	    $case[1]="nominative";
	    $case[2]="accusative";
	
	    
    //Model 6	four words: article & substantive & verb & adverbien z.b:"der mann kommt schnell	NOMINATIVE
    } else if($category[0] == "artdef" && $category[1] == "substantive" && $category[2] == "verb" && $category[3] == "adjective" && $numberofwords =="4") {
    
	$funcion[1]="article";
	$funcion[2]="subject";
	$funcion[3]="predicate";
	$funcion[4]="complement";
	$case[0]="nominative";
	$case[1]="nominative";
	$case[2]="nominative";
	$case[3]="nominative";
	
	
    //Model 7	five words: ich gebe den kindern Geld		NOMINATIVE DATIVE
    } else if($category[0] == "propersnom" && $category[1] == "verb" && $category[2] == "artdef" && $category[3] == "substantive" && $category[4] == "substantive" && $numberofwords =="5") {
    
	$funcion[1]="personal pronome subject";
	$funcion[2]="predicate";
	$funcion[3]="complement direct object";
	$funcion[4]="direct object";
	$funcion[5]="Indirect object";
	$case[0]="nominative";
	$case[1]="dative";
	$case[2]="dative";
	$case[3]="dative";
	$case[4]="accusative";
	
	$addedword[0]=" le";
	
	$wordorder[0]="0";
	$wordorder[1]="1";
	$wordorder[2]="4";
	$wordorder[3]="2";
	$wordorder[4]="3";
	
	
    //Model 8	five words: ich spreche mit der Frau		NOMINATIVE DATIVE
    } else if($category[0] == "propersnom" && $category[1] == "verb" && $category[2] == "preposition" && $category[3] == "artdef" && $category[4] == "substantive" && $numberofwords =="5") {
    
	$funcion[1]="personal pronome subject";
	$funcion[2]="predicate";
	$funcion[3]="preposition";
	$funcion[4]="direct object";
	$funcion[5]="direct object";
	$case[0]="nominative";
	$case[1]="accusative";
	$case[2]="dative";
	$case[3]="dative";
	$case[4]="dative";
	
	
    //Model 9:	four words: wir sehen den Park		NOMINATIVE ACCUSATIVE
    } else if($category[0] == "propersnom" && $category[1] == "verb" && $category[3] == "substantive" && $numberofwords =="4" && ($category[2] == "artdef" || $category[2] == "artindef" || $category[2] == "artneg") || $category[2] == "artpos") {
    
	$funcion[1]="personal pronome subject";
	$funcion[2]="predicate";
	$funcion[3]="complement direct object";
	$funcion[4]="direct object";
	$funcion[5]="Indirect object";
	$case[0]="nominative";
	$case[1]="nominative";
	$case[2]="accusative";
	$case[3]="accusative";
	$case[4]="accusative";
	   
        
    //Model 10:	six words: mein geschenk liegt auf dem tisch		NOMINATIVE ACCUSATIVE
    } else if($category[0] == "artpos" && $category[1] == "substantive" && $category[2] == "verb" && $category[3] == "preposition" && $category[4] == "artdef" && $category[5] == "substantive" && $numberofwords =="6") {
    
	$funcion[1]="posseisive pronomen";
	$funcion[2]="subject";
	$funcion[3]="predicate";
	$funcion[4]="direct object";
	$funcion[5]="direct object";
	$funcion[6]="direct object";
	$case[0]="nominative";
	$case[1]="nominative";
	$case[2]="dative";
	$case[3]="dative";
	$case[4]="dative";
	$case[5]="dative";
	
	
    //Model 11:	six words: das buch liegt auf dem tisch		NOMINATIVE ACCUSATIVE
    } else if($category[0] == "artdef" && $category[1] == "substantive" && $category[2] == "verb" && $category[3] == "preposition" && $category[4] == "artdef" && $category[5] == "substantive" && $numberofwords =="6") {
    
	$funcion[1]="posseisive pronomen";
	$funcion[2]="subject";
	$funcion[3]="predicate";
	$funcion[4]="direct object";
	$funcion[5]="direct object";
	$funcion[6]="direct object";
	$case[0]="nominative";
	$case[1]="nominative";
	$case[2]="dative";
	$case[3]="dative";
	$case[4]="dative";
	$case[5]="dative";
	   
    //Model 12;	seven words: Ich lege das buch auf den tisch		NOMINATIVE ACCUSATIVE
    } else if($category[0] == "propersnom" && $category[1] == "verb" && $category[2] == "artdef" && $category[3] == "substantive" && $category[4] == "preposition" && $category[5] == "artdef" && $category[6] == "substantive"&& $numberofwords =="7") {
    
	$funcion[1]="personal pronoun";
	$funcion[2]="predicate";
	$funcion[3]="direct object";
	$funcion[4]="direct object";
	$funcion[5]="indirect object";
	$funcion[6]="indirect object";
	$funcion[7]="indirect object";
	$case[0]="nominative";
	$case[1]="accusative";
	$case[2]="accusative";
	$case[3]="accusative";
	$case[4]="accusative";
	$case[5]="accusative";
	$case[6]="accusative";
	   
    }
 
}
    
 
 
 function typeassignation (&$numberofwords,&$type,&$subtype) {
//7. type asignation
      
      //In this step we assign the gender to the variable "type"
    
	for ($i=0; $i<=$numberofwords; $i++)      
	//Substantives
	    if($subtype[$i] == "masculine") {$type[$i]="masculine";
	    } else if($subtype[$i] == "femenine") {$type[$i]="femenine";
	    } else if($subtype[$i] == "neutral") {$type[$i]="neutral";
	} 
}
    
    
    
function typeverb (&$numberofwords,&$type,&$subtype,&$prefix) {
//8. Verb type description
	
	//We assign the value "regular" or "irregular" to the variable "type" 
	//and we add value to variable "prefix"
	
    for ($i=0; $i<=$numberofwords; $i++) 
    {  
    $imasuno=$i+1;
 
      if($subtype[$i] == "reg1") { $type[$i]="Regular";$prefix[$i]="No prefix";  
	} else if($subtype[$i] == "reg2") {$type[$i]="Regular";$prefix[$i]="Inseparable prefix";  
	} else if($subtype[$i] == "reg3") {$type[$i]="Regular";$prefix[$i]="Separable prefix";
	} else if($subtype[$i] == "reg4") {$type[$i]="Regular";$prefix[$i]="No prefix.Sufix:-ieren";
        } else if($subtype[$i] == "irreg5") {$type[$i]="Irregular";$prefix[$i]="No prefix";
    	} else if($subtype[$i] == "irreg6") {$reulgar[$i]="Irregular";$prefix[$i]="Inseparable prefix";
    	} else if($subtype[$i] == "reg7") {$type[$i]="Irregular";$prefix[$i]="Separable prefix";
	} else if($subtype[$i] == "modal") {$type[$i]="modal";
	}
}
}
     

 function errordetection (&$numberofwords,&$word,&$case,&$type,&$cword)
{
//12. Error detection.

//12.1 Declination concordances

    // We check declination concordances on articles 
   
    
        //Check declinations
	//articles
        /*
	
	Definiter Artikel	Masculine	Femenine	Neutral		Plural
	Nominative		Der		Die		Das		die
	Dative			dem		der		dem		den
	Accusative		den		die		das
	Genitive		des		der		des
	
	Indefinite article	Masculine	Femenine	Neutral		Plural
	Nominative		ein		eine		ein
	Dative			einen		einer		ein
	Accusative		eines		eine		ein
	Genitive		eines		einer		eines
	
	Negative		Masculine	Femenine	Neutral		Plural
	Nominative		kein		keine		kein		keine
	Dative			keinen		keiner		kein		keinen
	Accusative		keines		keine		kein
	Genitive		keines		keiner		keines

	*/
        
        for ($i=0; $i<=$numberofwords; $i++) 
	{  
       $imasuno=$i+1;
       
	
       
        //definite articles DER 
    
	// DER Nominative
	//Si el articulo es der el nombre debe ser 
	//masculino nominativo 
	//femenino dativo 
	//genitivo femenino
	
	
	//Definite article DER
	//Nominativo Masculino	Der Mann
	if ($word[$i]=="der" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Nominativo femenino Der Frau
	}else if ($word[$i]=="der" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use die instead.";$cword[$i]="die";
	//Nominativo neutro Der Kind
	}else if ($word[$i]=="der" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use das instead";$cword[$i]="das";
	
	//Dativo Masculino Ich gebe der mann Geld
	}else if ($word[$i]=="der" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	//Dativo femenino Ich gebe der frau Geld
	}else if ($word[$i]=="der" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Dativo neutral Ich gebe der kind Geld
	}else if ($word[$i]=="der" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	
	//Acusativo Masculino Wir sehen der Park
	}else if ($word[$i]=="der" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use den instead";$cword[$i]="den";
	//Acusativo femenino Wir sehen der Frau
	}else if ($word[$i]=="der" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use die instead";$cword[$i]="die";
	//Acusativo neutral Wir sehen der kind
	}else if ($word[$i]=="der" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use das instead";$cword[$i]="das";
	
	//Genitivo Masculino Der Mann der Hauses
	}else if ($word[$i]=="der" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use des instead";$cword[$i]="des";
	//Genitivo femenino Ich gebe der frau
	}else if ($word[$i]=="der" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use der instead";$cword[$i]="der";
	//Genitivo neutral Ich gebe der kind
	}else if ($word[$i]=="der" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use des instead";$cword[$i]="des";
		
	
	
	
	//definite articles DIE
        
        //Nominative masculine die Mann
        }else if ($word[$i]=="die" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use der instead.";$cword[$i]="der";
	//Nominativo femenino die Frau
	}else if ($word[$i]=="die" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="femenine.";
	//Nominativo neutro Die Kind
	}else if ($word[$i]=="die" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use das instead";$cword[$i]="das";
	
	//Dativo Masculino Ich gebe die mann Geld
	}else if ($word[$i]=="die" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	//Dativo femenino Ich gebe die frau Geld
	}else if ($word[$i]=="die" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Ude der instead";$cword[$i]="der";
	//Dativo neutral Ich gebe die kind Geld
	}else if ($word[$i]=="die" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	
	//Acusativo Masculino Wir sehen die Park
	}else if ($word[$i]=="die" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use den instead";$cword[$i]="den";
	//Acusativo femenino Wir sehen die Frau
	}else if ($word[$i]=="die" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Acusativo neutral Wir sehen die kind
	}else if ($word[$i]=="die" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use das instead";$cword[$i]="das";
	
	//Genitivo Masculino Der Mann die Hauses
	}else if ($word[$i]=="die" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use des instead";$cword[$i]="des";
	//Genitivo femenino Ich gebe die frau
	}else if ($word[$i]=="die" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use der instead";$cword[$i]="der";
	//Genitivo neutral Ich gebe die kind
	}else if ($word[$i]=="die" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use des instead";$cword[$i]="des";
               
               
               
        //definite articles DAS
         //Nominative masculine das Mann
        }else if ($word[$i]=="das" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use der instead.";$cword[$i]="der";
	//Nominativo femenino as Frau
	}else if ($word[$i]=="das" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead.";$cword[$i]="die";
	//Nominativo neutro das Kind
	}else if ($word[$i]=="das" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Dativo Masculino Ich gebe das mann Geld
	}else if ($word[$i]=="das" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	//Dativo femenino Ich gebe das frau Geld
	}else if ($word[$i]=="das" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Ude der instead";$cword[$i]="der";
	//Dativo neutral Ich gebe das kind Geld
	}else if ($word[$i]=="das" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	
	//Acusativo Masculino Wir sehen das Park
	}else if ($word[$i]=="das" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use den instead";$cword[$i]="den";
	//Acusativo femenino Wir sehen das Frau
	}else if ($word[$i]=="das" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead";$cword[$i]="die";
	//Acusativo neutral Wir sehen das kind
	}else if ($word[$i]=="das" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Genitivo Masculino Der Mann das Hauses
	}else if ($word[$i]=="das" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use des instead";$cword[$i]="des";
	//Genitivo femenino Ich gebe das frau
	}else if ($word[$i]=="das" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use der instead";$cword[$i]="der";
	//Genitivo neutral Ich gebe das kind
	}else if ($word[$i]=="das" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use des instead";$cword[$i]="des";
       
        
        
        
        //definite articles DES
        
        //Nominative masculine des Mann
        }else if ($word[$i]=="des" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use der instead.";$cword[$i]="der";
	//Nominativo femenino des Frau
	}else if ($word[$i]=="des" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead.";$cword[$i]="der";
	//Nominativo neutro des Kind
	}else if ($word[$i]=="des" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use das instead";$cword[$i]="das";
	
	//Dativo Masculino Ich gebe des mann Geld
	}else if ($word[$i]=="des" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	//Dativo femenino Ich gebe des frau Geld
	}else if ($word[$i]=="des" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Ude der instead";$cword[$i]="der";
	//Dativo neutral Ich gebe des kind Geld
	}else if ($word[$i]=="des" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	
	//Acusativo Masculino Wir sehen des Park
	}else if ($word[$i]=="des" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use den instead";$cword[$i]="den";
	//Acusativo femenino Wir sehen des Frau
	}else if ($word[$i]=="des" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead";$cword[$i]="die";
	//Acusativo neutral Wir sehen des kind
	}else if ($word[$i]=="des" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use das instead";$cword[$i]="das";
	
	//Genitivo Masculino Der Mann des Hauses
	}else if ($word[$i]=="des" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Genitivo femenino Ich gebe des frau
	}else if ($word[$i]=="des" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use der instead";$cword[$i]="der";
	//Genitivo neutral Ich gebe des kind
	}else if ($word[$i]=="des" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
        
              
        //definite articles DEN
        
          //Nominative masculine den Mann
        }else if ($word[$i]=="den" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use der instead.";$cword[$i]="der";
	//Nominativo femenino den Frau
	}else if ($word[$i]=="den" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead.";$cword[$i]="die";
	//Nominativo neutro den Kind
	}else if ($word[$i]=="den" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use das instead";$wcord[$i]="das";
	
	//Dativo Masculino Ich gebe den mann Geld
	}else if ($word[$i]=="den" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	//Dativo femenino Ich gebe des frau Geld
	}else if ($word[$i]=="den" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Ude der instead";$cword[$i]="der";
	//Dativo neutral Ich gebe des kind Geld
	}else if ($word[$i]=="den" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong.Use dem instead";$cword[$i]="dem";
	
	//Acusativo Masculino Wir sehen den Park
	}else if ($word[$i]=="den" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Acusativo femenino Wir sehen den Frau
	}else if ($word[$i]=="den" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead";$cword[$i]="die";
	//Acusativo neutral Wir sehen den kind
	}else if ($word[$i]=="den" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use das instead";$cword[$i]="das";
	
	//Genitivo Masculino Der Mann den Hauses
	}else if ($word[$i]=="den" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Genitivo femenino Ich gebe des frau
	}else if ($word[$i]=="den" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use der instead";$cword[$i]="der";
	//Genitivo neutral Ich gebe den kind
	}else if ($word[$i]=="den" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
        
        
        
        //definite articles DEM
        //Nominative masculine dem Mann
        }else if ($word[$i]=="dem" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use der instead.";$cword[$i]="der";
	//Nominativo femenino dem Frau
	}else if ($word[$i]=="dem" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead.";$cword[$i]="die";
	//Nominativo neutro dem Kind
	}else if ($word[$i]=="dem" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use das instead";$cword[$i]="das";
	
	//Dativo Masculino Ich gebe dem mann Geld
	}else if ($word[$i]=="dem" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Dativo femenino Ich gebe dem frau Geld
	}else if ($word[$i]=="dem" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Ude der instead";$cword[$i]="der";
	//Dativo neutral Ich gebe dem kind Geld
	}else if ($word[$i]=="dem" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Acusativo Masculino Wir sehen dem Park
	}else if ($word[$i]=="dem" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use den instead";$cword[$i]="den";
	//Acusativo femenino Wir sehen dem Frau
	}else if ($word[$i]=="dem" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use die instead";$cword[$i]="die";
	//Acusativo neutral Wir sehen dem kind
	}else if ($word[$i]=="dem" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use das instead";$cword[$i]="das";
	
	//Genitivo Masculino Der Mann dem Hauses
	}else if ($word[$i]=="dem" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use des instead";$cord[$i]="des";
	//Genitivo femenino Ich gebe dem frau
	}else if ($word[$i]=="dem" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use der instead";$cword[$i]="der";
	//Genitivo neutral Ich gebe dem kind
	}else if ($word[$i]=="dem" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use des instead";$wcord[$i]="des";
        
        
        
        //indefinite articles EIN EINEM
        //Nominative masculine ein Mann
        }else if ($word[$i]=="ein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Nominativo femenino ein Frau
	}else if ($word[$i]=="ein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use eine instead.";$cword[$i]="eine";
	//Nominativo neutro ein Kind
	}else if ($word[$i]=="ein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Dativo Masculino Ich gebe ein mann Geld
	}else if ($word[$i]=="ein" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use einem instead";$cword[$i]="einem";
	//Dativo femenino Ich gebe ein frau Geld
	}else if ($word[$i]=="ein" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use einer instead";$cword[$i]="einer";
	//Dativo neutral Ich gebe ein kind Geld
	}else if ($word[$i]=="ein" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use einem instead";$cword[$i]="einem";
	
	//Acusativo Masculino Wir sehen ein Park
	}else if ($word[$i]=="ein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use einen instead";$cword[$i]="einen";
	//Acusativo femenino Wir sehen ein Frau
	}else if ($word[$i]=="ein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use eine instead";$cword[$i]="eine";
	//Acusativo neutral Wir sehen ein kind
	}else if ($word[$i]=="ein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Genitivo Masculino Der Mann ein Hauses
	}else if ($word[$i]=="ein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use eines instead";$cword[$i]="eines";
	//Genitivo femenino Ich gebe ein frau
	}else if ($word[$i]=="ein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use einer instead";$cword[$i]="einer";
	//Genitivo neutral Ich gebe ein kind
	}else if ($word[$i]=="ein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use eines instead";$cword[$i]="eines";
        
        
         //indefinite articles EINE
        //Nominative masculine eine Mann
        }else if ($word[$i]=="eine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use ein instead";$cword[$i]="ein";
	//Nominativo femenino eine Frau
	}else if ($word[$i]=="eine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Nominativo neutro eine Kind
	}else if ($word[$i]=="eine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use ein instead";$cword[$i]="ein";
	
	//Dativo Masculino Ich gebe eine mann Geld
	}else if ($word[$i]=="eine" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use einem instead";$cword[$i]="einem";
	//Dativo femenino Ich gebe eine frau Geld
	}else if ($word[$i]=="eine" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use einer instead";$cword[$i]="einer";
	//Dativo neutral Ich gebe eine kind Geld
	}else if ($word[$i]=="eine" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use einem instead";$cword[$i]="einem";
	
	//Acusativo Masculino Wir sehen eine Park
	}else if ($word[$i]=="eine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use einen instead";$cword[$i]="einen";
	//Acusativo femenino Wir sehen eine Frau
	}else if ($word[$i]=="eine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Acusativo neutral Wir sehen eine kind
	}else if ($word[$i]=="eine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use ein instead";$cword[$i]="ein";
	
	//Genitivo Masculino Der Mann eine Hauses
	}else if ($word[$i]=="eine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use eines instead";$cword[$i]="eines";
	//Genitivo femenino Ich gebe eine frau
	}else if ($word[$i]=="eine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use einer instead";$cword[$i]="einer";
	//Genitivo neutral Ich gebe eine kind
	}else if ($word[$i]=="eine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use eines instead";$cword[$i]="eines";
        
        
        
        //indefinite articles EINER
        //Nominative masculine einer Mann
        }else if ($word[$i]=="einer" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use ein instead";$cword[$i]="ein";
	//Nominativo femenino einer Frau
	}else if ($word[$i]=="einer" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use eine instead";$cword[$i]="eine";
	//Nominativo neutro einer Kind
	}else if ($word[$i]=="einer" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use ein instead";$cword[$i]="ein";
	
	//Dativo Masculino Ich gebe einer mann Geld
	}else if ($word[$i]=="einer" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use einem instead";$cword[$i]="";
	//Dativo femenino Ich gebe einer frau Geld
	}else if ($word[$i]=="einer" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Dativo neutral Ich gebe einer kind Geld
	}else if ($word[$i]=="einer" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use einem instead";$cword[$i]="einem";
	
	//Acusativo Masculino Wir sehen einer Park
	}else if ($word[$i]=="einer" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use einen instead";$cword[$i]="einen";
	//Acusativo femenino Wir sehen einer Frau
	}else if ($word[$i]=="einer" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use eine instead";$cword[$i]="eine";
	//Acusativo neutral Wir sehen einer kind
	}else if ($word[$i]=="einer" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use ein instead";$cword[$i]="ein";
	
	//Genitivo Masculino Der Mann einer Hauses
	}else if ($word[$i]=="einer" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use eines instead";$cword[$i]="eines";
	//Genitivo femenino Ich gebe einer frau
	}else if ($word[$i]=="einer" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Genitivo neutral Ich gebe einer kind
	}else if ($word[$i]=="einer" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use eines instead";$cword[$i]="eines";
        
        
        //------------------------------------------------------------------------------------------------------------------------------------------
        //Negative articles KEIN
        
        //indefinite articles EIN EINEM
        //Nominative masculine kein Mann
        }else if ($word[$i]=="kein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Nominativo femenino kein Frau
	}else if ($word[$i]=="kein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use keine instead.";$cword[$i]="keine";
	//Nominativo neutro kein Kind
	}else if ($word[$i]=="kein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Dativo Masculino Ich gebe kein mann Geld
	}else if ($word[$i]=="kein" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use keinem instead";$cword[$i]="keinem";
	//Dativo femenino Ich gebe kein frau Geld
	}else if ($word[$i]=="kein" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use keiner instead";$cword[$i]="keiner";
	//Dativo neutral Ich gebe kein kind Geld
	}else if ($word[$i]=="kein" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use keinem instead";$cword[$i]="keinem";
	
	//Acusativo Masculino Wir sehen kein Park
	}else if ($word[$i]=="kein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use keinen instead";$cword[$i]="keinen";
	//Acusativo femenino Wir sehen kein Frau
	}else if ($word[$i]=="kein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use keine instead";$cword[$i]="keine";
	//Acusativo neutral Wir sehen kein kind
	}else if ($word[$i]=="kein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Genitivo Masculino Der Mann kein Hauses
	}else if ($word[$i]=="kein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use keines instead";$cword[$i]="keines";
	//Genitivo femenino Ich gebe kein frau
	}else if ($word[$i]=="kein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use keiner instead";$cword[$i]="keiner";
	//Genitivo neutral Ich gebe kein kind
	}else if ($word[$i]=="kein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use keines instead";$cword[$i]="keines";
        
        
         //indefinite articles KEINE
        //Nominative masculine keine Mann
        }else if ($word[$i]=="keine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use kein instead";$cword[$i]="kein";
	//Nominativo femenino keine Frau
	}else if ($word[$i]=="keine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Nominativo neutro keine Kind
	}else if ($word[$i]=="keine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use kein instead";$cword[$i]="kein";
	
	//Dativo Masculino Ich gebe keine mann Geld
	}else if ($word[$i]=="keine" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use keinem instead";$cword[$i]="keinem";
	//Dativo femenino Ich gebe keine frau Geld
	}else if ($word[$i]=="keine" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use keiner instead";$cword[$i]="keiner";
	//Dativo neutral Ich gebe keine kind Geld
	}else if ($word[$i]=="keine" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use keinem instead";$cword[$i]="keinem";
	
	//Acusativo Masculino Wir sehen keine Park
	}else if ($word[$i]=="keine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use keinen instead";$cword[$i]="keinen";
	//Acusativo femenino Wir sehen keine Frau
	}else if ($word[$i]=="keine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Acusativo neutral Wir sehen keine kind
	}else if ($word[$i]=="keine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use kein instead";$cword[$i]="kein";
	
	//Genitivo Masculino Der Mann keine Hauses
	}else if ($word[$i]=="keine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use keines instead";$cword[$i]="keines";
	//Genitivo femenino Ich gebe keine frau
	}else if ($word[$i]=="keine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use keiner instead";$cword[$i]="keiner";
	//Genitivo neutral Ich gebe keine kind
	}else if ($word[$i]=="keine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use keines instead";$cword[$i]="keines";
        
        
        
        //indefinite articles KEINER
        //Nominative masculine keiner Mann
        }else if ($word[$i]=="keiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use kein instead";$cword[$i]="kein";
	//Nominativo femenino keiner Frau
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use keine instead";$cword[$i]="keine";
	//Nominativo neutro keiner Kind
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use kein instead";$cword[$i]="kein";
	
	//Dativo Masculino Ich gebe keiner mann Geld
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use keinem instead";$cword[$i]="keinem";
	//Dativo femenino Ich gebe keiner frau Geld
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Dativo neutral Ich gebe keiner kind Geld
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use keinem instead";$cword[$i]="keinem";
	
	//Acusativo Masculino Wir sehen keiner Park
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use keinen instead";$cword[$i]="keinen";
	//Acusativo femenino Wir sehen keiner Frau
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use keine instead";$cword[$i]="keine";
	//Acusativo neutral Wir sehen keiner kind
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use kein instead";$cword[$i]="kein";
	
	//Genitivo Masculino Der Mann keiner Hauses
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use keines instead";$cword[$i]="keines";
	//Genitivo femenino Ich gebe keiner frau
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Genitivo neutral Ich gebe keiner kind
	}else if ($word[$i]=="keiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use keines instead";$cword[$i]="keines";
        
        //Posesive articles MEIN
        
        //------------------------------------------------------------------------------------------------------------------------------------------
        
        
             
        
        //indefinite articles MEIN
        //Nominative masculine mein Mann
        }else if ($word[$i]=="mein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Nominativo femenino mein Frau
	}else if ($word[$i]=="mein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use meine instead.";$cword[$i]="meine";
	//Nominativo neutro mein Kind
	}else if ($word[$i]=="mein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Dativo Masculino Ich gebe mein mann Geld
	}else if ($word[$i]=="mein" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use meinem instead";$cword[$i]="meinem";
	//Dativo femenino Ich gebe mein frau Geld
	}else if ($word[$i]=="mein" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use meiner instead";$cword[$i]="meiner";
	//Dativo neutral Ich gebe mein kind Geld
	}else if ($word[$i]=="mein" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use meinem instead";$cword[$i]="meinem";
	
	//Acusativo Masculino Wir sehen mein Park
	}else if ($word[$i]=="mein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use meinen instead";$cword[$i]="meinen";
	//Acusativo femenino Wir sehen mein Frau
	}else if ($word[$i]=="mein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use meine instead";$cword[$i]="meine";
	//Acusativo neutral Wir sehen mein kind
	}else if ($word[$i]=="mein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Genitivo Masculino Der Mann mein Hauses
	}else if ($word[$i]=="mein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use meines instead";$cword[$i]="meines";
	//Genitivo femenino Ich gebe mein frau
	}else if ($word[$i]=="mein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use meiner instead";$cword[$i]="meiner";
	//Genitivo neutral Ich gebe mein kind
	}else if ($word[$i]=="mein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use meines instead";$cword[$i]="meines";
        
        
         //indefinite articles NEINE
        //Nominative masculine meine Mann
        }else if ($word[$i]=="meine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use mein instead";$cword[$i]="";
	//Nominativo femenino meine Frau
	}else if ($word[$i]=="meine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Nominativo neutro meine Kind
	}else if ($word[$i]=="meine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use mein instead";$cword[$i]="";
	
	//Dativo Masculino Ich gebe meine mann Geld
	}else if ($word[$i]=="meine" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use meinem instead";$cword[$i]="meinem";
	//Dativo femenino Ich gebe meine frau Geld
	}else if ($word[$i]=="meine" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use meiner instead";$cword[$i]="meiner";
	//Dativo neutral Ich gebe meine kind Geld
	}else if ($word[$i]=="meine" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use meinem instead";$cword[$i]="meinem";
	
	//Acusativo Masculino Wir sehen meine Park
	}else if ($word[$i]=="meine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use meinen instead";$cword[$i]="meinen";
	//Acusativo femenino Wir sehen meine Frau
	}else if ($word[$i]=="meine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Acusativo neutral Wir sehen meine kind
	}else if ($word[$i]=="meine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use mein instead";$cword[$i]="mein";
	
	//Genitivo Masculino Der Mann meine Hauses
	}else if ($word[$i]=="meine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use meines instead";$cword[$i]="meines";
	//Genitivo femenino Ich gebe meine frau
	}else if ($word[$i]=="meine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use meiner instead";$cword[$i]="meiner";
	//Genitivo neutral Ich gebe meine kind
	}else if ($word[$i]=="meine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use meines instead";$cword[$i]="meines";
        
        
        
        //indefinite articles MEINER
        //Nominative masculine meiner Mann
        }else if ($word[$i]=="meiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use mein instead";$cword[$i]="mein";
	//Nominativo femenino meiner Frau
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use meine instead";$cword[$i]="meine";
	//Nominativo neutro meiner Kind
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use mein instead";$cword[$i]="mein";
	
	//Dativo Masculino Ich gebe meiner mann Geld
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use meinem instead";$cword[$i]="meinem";
	//Dativo femenino Ich gebe meiner frau Geld
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Dativo neutral Ich gebe meiner kind Geld
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use meinem instead";$cword[$i]="meinem";
	
	//Acusativo Masculino Wir sehen meiner Park
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use meinen instead";$cword[$i]="meinen";
	//Acusativo femenino Wir sehen meiner Frau
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use meine instead";$cword[$i]="meine";
	//Acusativo neutral Wir sehen meiner kind
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use mein instead";$cword[$i]="mein";
	
	//Genitivo Masculino Der Mann meiner Hauses
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use meines instead";$cword[$i]="meines";
	//Genitivo femenino Ich gebe meiner frau
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Genitivo neutral Ich gebe meiner kind
	}else if ($word[$i]=="meiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use meines instead";$cword[$i]="meines";
        
        
        
        
        //Posesive articles DEIN
        
        //Nominative masculine dein Mann
        }else if ($word[$i]=="dein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="masculine";
	//Nominativo femenino dein Frau
	}else if ($word[$i]=="dein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use deine instead.";$cword[$i]="deine";
	//Nominativo neutro dein Kind
	}else if ($word[$i]=="dein" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Dativo Masculino Ich gebe dein mann Geld
	}else if ($word[$i]=="dein" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use deinem instead";$cword[$i]="deinem";
	//Dativo femenino Ich gebe dein frau Geld
	}else if ($word[$i]=="dein" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use deiner instead";$cword[$i]="deiner";
	//Dativo neutral Ich gebe dein kind Geld
	}else if ($word[$i]=="dein" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use deinem instead";$cword[$i]="deinem";
	
	//Acusativo Masculino Wir sehen dein Park
	}else if ($word[$i]=="dein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use deinen instead";$cword[$i]="deinen";
	//Acusativo femenino Wir sehen dein Frau
	}else if ($word[$i]=="dein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use deine instead";$cword[$i]="deine";
	//Acusativo neutral Wir sehen dein kind
	}else if ($word[$i]=="dein" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="neutral";
	
	//Genitivo Masculino Der Mann dein Hauses
	}else if ($word[$i]=="dein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use deines instead";$cword[$i]="deines";
	//Genitivo femenino Ich gebe dein frau
	}else if ($word[$i]=="dein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use deiner instead";$cword[$i]="deiner";
	//Genitivo neutral Ich gebe dein kind
	}else if ($word[$i]=="dein" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use deines instead";$cword[$i]="deines";
        
        
        
        //Nominative masculine deine Mann
        }else if ($word[$i]=="deine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use dein instead";$cword[$i]="dein";
	//Nominativo femenino deine Frau
	}else if ($word[$i]=="deine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Nominativo neutro deine Kind
	}else if ($word[$i]=="deine" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use dein instead";$cword[$i]="dein";
	
	//Dativo Masculino Ich gebe deine mann Geld
	}else if ($word[$i]=="deine" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use deinem instead";$cword[$i]="deinem";
	//Dativo femenino Ich gebe deine frau Geld
	}else if ($word[$i]=="deine" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="wrong.use deiner instead";$cword[$i]="deiner";
	//Dativo neutral Ich gebe deine kind Geld
	}else if ($word[$i]=="deine" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use deinem instead";$cword[$i]="deinem";
	
	//Acusativo Masculino Wir sehen deine Park
	}else if ($word[$i]=="deine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use deinen instead";$cword[$i]="deinen";
	//Acusativo femenino Wir sehen deine Frau
	}else if ($word[$i]=="deine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Acusativo neutral Wir sehen deine kind
	}else if ($word[$i]=="deine" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use dein instead";$cword[$i]="dein";
	
	//Genitivo Masculino Der Mann deine Hauses
	}else if ($word[$i]=="deine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use deines instead";$cword[$i]="deines";
	//Genitivo femenino Ich gebe deine frau
	}else if ($word[$i]=="deine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="wrong.Use deiner instead";$cword[$i]="deiner";
	//Genitivo neutral Ich gebe deine kind
	}else if ($word[$i]=="deine" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use deines instead";$cword[$i]="deines";
        
        
        
        //indefinite articles KEINER
        //Nominative masculine deiner Mann
        }else if ($word[$i]=="deiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use dein instead";$cword[$i]="dein";
	//Nominativo femenino deiner Frau
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use deine instead";$cword[$i]="deine";
	//Nominativo neutro deiner Kind
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use dein instead";$cword[$i]="dein";
	
	//Dativo Masculino Ich gebe deiner mann Geld
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine") {$type[$i]="wrong,use deinem instead";$cword[$i]="deinem";
	//Dativo femenino Ich gebe deiner frau Geld
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Dativo neutral Ich gebe deiner kind Geld
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use deinem instead";$cword[$i]="deinem";
	
	//Acusativo Masculino Wir sehen deiner Park
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use deinen instead";$cword[$i]="deinen";
	//Acusativo femenino Wir sehen deiner Frau
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine") {$type[$i]="wrong, use deine instead";$cword[$i]="deine";
	//Acusativo neutral Wir sehen deiner kind
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral") {$type[$i]="wrong, use dein instead";$cword[$i]="dein";
	
	//Genitivo Masculino Der Mann deiner Hauses
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine") {$type[$i]="wrong, use deines instead";$cword[$i]="deines";
	//Genitivo femenino Ich gebe deiner frau
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine") {$type[$i]="femenine";
	//Genitivo neutral Ich gebe deiner kind
	}else if ($word[$i]=="deiner" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral") {$type[$i]="wrong use deines instead";$cword[$i]="deines";
        
    }

 }
}  
     

     function genderconrcondances (&$numberofwords,&$category,&$genero,&$case,&$type,&$type2,&$neworder,&$wordorder,&$spanish)
{

   //We write grammar correction to write the errors messages
    
    
	for ($i=0; $i<=$numberofwords; $i++) 
	{
    $imasuno=$i+1;
    $imasdos=$i+2;
   
//12.2. Gender concordances
    //Genders are not correct "Der Mann"
    if($category[$i] == "artdef" && $category[$imasuno] == "substantive" && ($type[$i]!=$type[$imasuno] && $type2[$i]!=$type[$imasuno])) {
	
	$word[$i]=$cword[$i];
      }
 
    if ($genero[$imasuno] =="masc" && $category[$i]=="artdef" && $case[$imasuno]=="nominative"){
    $spanish[$i]="el";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artdef" && $case[$imasuno]=="nominative"){
    $spanish[$i]="la";
    }else if ($genero[$imasuno] =="masc" && $category[$i]=="artdef" && $case[$imasuno]=="accusative"){
    $spanish[$i]="el";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artdef" && $case[$imasuno]=="accusative"){
    $spanish[$i]="la";  
    }else if($genero[$imasuno] =="masc" && $category[$i]=="artdef" && $case[$imasuno]=="dative"){
    $spanish[$i]="al";
    }else if($genero[$imasuno] =="fem"  && $category[$i]=="artdef" && $case[$imasuno]=="dative"){
    $spanish[$i]="a la";
    }else if($genero[$imasuno]=="masc" && $category[$i]=="artindef"){
    $spanish[$i]="uno";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artindef"){
    $spanish[$i]="una";
    }else if($genero[$imasuno]=="masc" && $category[$i]=="artneg"){
    $spanish[$i]="ningún";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artneg"){
    $spanish[$i]="ninguna";
    /*}else if($genero[$imasuno]=="masc" && $category[$i]=="artpos"){
    $spanish[$i]="mi";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artpos"){
    $spanish[$i]="mi";*/
    }
    
   


    //We reorder the sentences by the syntax order for the target language
    $neworder[$i]=$spanish[$wordorder[$i]];
    }
}

	
function reordersyntaxtargetlanguage (&$numberofwords,&$neworder,&$addedword,&$spanish)   
{
    for ($i=0; $i<=$numberofwords; $i++) 
	{
	$neworder[$i].=$addedword[$i];
	}
    
    
    //We assign the new word order to the target language
    $spanish=$neworder;
}	
      
      
    function conjugationconcordances (&$numberofwords,&$category,&$word,&$person)
{
          for ($i=0; $i<=$numberofwords; $i++) 
	  {
    $imasuno=$i+1;
    $imasdos=$i+2;
      
//12.3 Conjugation concordances
    
    //Conjugation is not correct 1st person "Ich komme"
      if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno] && $person[$i]=="1"){
		
	//Conjugation is not correct 2nd person "du kommst"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno] && $person[$i]=="2"){
	
	//Conjugation sie "sie kommt, sie kommen"
	} else if ($category[$i] =="propersnom" && $word[$i]=="sie" && $category[$imasuno] == "verb" && $person[$imasuno]=="3" || $person[$imasuno]=="4" ){
	
	//Conjugation is not correct 3rd person "er kommt"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$imasuno]=="3" || $person[$imasuno]=="5"){
		
	
	//Conjugation is not correct 3rd person "er kommt"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno]){
	
	
	//Conjugation is not correct 4rd person "wir kommen sie kommen"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno] && $person[$i]=="4" || $person[$i]=="6"){
		
	
	
    //Conjugation is not correct "komme ich"
      } else if ($category[$i] == "verb" && $category[$imasuno] == "propersnom" && $person[$i]!=$person[$imasuno] && $person[$i]!="4" && $person[$i]!="6" && $person[$i]!="3" && $case[$i]=="nominative") {
	

            
    //Conjugation is not correct "der mann kommt"
	}else if ($category[$i] == "artdef" && $category[$imasuno] == "substantive" && $category[$imasdos] == "verb" && $person[$imasdos]!="3") {
	 

	}
  }
}

    function jsonencode (&$spanish)
{
      echo json_encode($spanish);
}   



    function erasememorycloseconnection (&$numberofwords,&$result)
{
//15 Erase variable memory, results and close connection
    
    for ($i=0; $i<=numberofwords; $i++) {
    // free result set memory 
    mysql_free_result($result[$i]);
    }
    
    // close connection 
    mysql_close(); 
}   


?>

