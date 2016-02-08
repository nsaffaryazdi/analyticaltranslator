<?php /*
Analytical translator.

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
    
Written by "xpheres" 2014, 
xpheres@lingoworld.eu

More information on http://www.lingoworld.eu
*/


//2. The sentence is received and split into words and assigned to an array.

    $string=$_POST['text'];		//We obtain the input

    $array = explode(' ',$string);		//We split it into words

//3. The number of words are counted and the values are saved in a variable.

    $numberofwords=count($array);	//We count the number of words

    if($numberofwords >= "12") {$numberofwords=10;} // We establish a limit of 10 words

    // set database server access variables:
      $username=""; 			//Write the username with writing access privilege of your database here
      $password=""; 			//Write the password for your database here
      $db="";				//Write the name of your database here
      $dbname=""; 			//Write the name of your database table here

//4.A connection to the database is made to collect all the values from every row where
    
    // open connection 
	mysql_connect('localhost', $username, $password) or die(mysql_error());

    // select database
	mysql_select_db($db) or die(mysql_error());

    //Open a table to print the results
    
    
    //We open a loop and we check first if the words are found in the database
    
    for ($i=0; $i<=$numberofwords-1; $i++) 
    {

	// create query 
	$query[$i] = "SELECT * FROM $dbname where spanish ='$array[$i]'";

	// execute query 
	$result[$i] = mysql_query($query[$i]) or die ("Error in query: $query. ".mysql_error());


	// see if any rows were returned for word 1 Language
	if (mysql_num_rows($result[$i]) > 0) 
	{ 
	// yes 
	// print them one after another 
	while($row = mysql_fetch_row($result[$i])) {	
	
	
//5.We assign the values of every columns of the word row
	
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
     
  
   
//6. Syntactical analisis
    
    //Every category of every word will be compared to the syntax models.
    //If the sentence fit on any model, the correspondent values for function and case will be assigned
    
    /*
	  Syntax models:
	  1	2 words		Ad + S 				Der Mann
	  2	2 words		PP + V				Ich sehe
	  3	2 words		V + PP				sehe ich
	  4	3 words		PP + V + PP			Ich sehe dich
	  5	3 words		Ad + S + V			der Mann ist
	  6	4 words		PP + S				Der Mann kommt schnell
	  7	4 words: 	Pp + V + Ad + S			wir sehen den Park
	  8	5 words		PP + V + Ad + S + S		Ich gebe den Kindern Geld
	  9	5 words: 	PP + V +Pr + Ad + S		ich spreche mit der Frau
	  10	6 words		Ppos + S + V + Pr + Ad + S	Mein geschenk liegt auf dem tisch
	  11	6 words 	Ad + S + V + Pr + Ad + S	das buch liegt auf dem tisch
	  12	7 words		Pp + V + Ad + S + Ad + S	Ich lege das buch auf den tisch	
    */
    
    
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
	$case[1]="nominative";
	$case[2]="accusative";
	$case[3]="accusative";
	$case[4]="accusative";
	$case[5]="accusative";
	$case[6]="accusative";
	   
    }
    
        
    //7. type asignation
      
      //In this step we assign the gender to the variable "type"
    
	for ($i=0; $i<=$numberofwords; $i++)      
	//Substantives
	    if($subtype[$i] == "masculine") {$type[$i]="masculine";
	    } else if($subtype[$i] == "femenine") {$type[$i]="femenine";
	    } else if($subtype[$i] == "neutral") {$type[$i]="neutral";
	} 
    
   //We write grammar correction to write the errors messages
    
    
	for ($i=0; $i<=$numberofwords; $i++) 
	{
    $imasuno=$i+1;
    $imasdos=$i+2;
   
//12.2. Gender concordances
    //Genders are not correct "Der Mann"
    if($category[$i] == "artdef" && $category[$imasuno] == "substantive" && ($type[$i]!=$type[$imasuno] && $type2[$i]!=$type[$imasuno])) {
	
	$spanish[$i]=$cspanish[$i];
      }
    // Art definite
    if ($category[$i]=="artdef" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine") {    $word[$i]="der";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine"){ $word[$i]="die";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral"){  $word[$i]="das";
    
    }else if($category[$i]=="artdef" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine"){  $word[$i]="den";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine"){  $word[$i]="die";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral"){  $word[$i]="das";
    
    }else if($category[$i]=="artdef" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine"){  $word[$i]="dem";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine"){  $word[$i]="der";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral"){  $word[$i]="dem";
    
    }else if($category[$i]=="artdef" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine"){  $word[$i]="dem";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine"){  $word[$i]="der";
    }else if($category[$i]=="artdef" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral"){  $word[$i]="dem";
    
    
    // Art indefinite
    
    }else if($category[$i]=="artindef" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine"){ $word[$i]="ein";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine"){ $word[$i]="eine";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral"){  $word[$i]="ein";
    
    }else if($category[$i]=="artindef" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine"){  $word[$i]="eines";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine"){  $word[$i]="eine";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral"){  $word[$i]="ein";
    
    }else if($category[$i]=="artindef" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine"){  $word[$i]="einen";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine"){  $word[$i]="einer";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral"){  $word[$i]="ein";
    
    }else if($category[$i]=="artindef" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine"){  $word[$i]="eines";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine"){  $word[$i]="einer";
    }else if($category[$i]=="artindef" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral"){  $word[$i]="eines";
    
        // Art negative
    
    }else if($category[$i]=="artneg" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine"){ $word[$i]="kein";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine"){ $word[$i]="keine";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral"){  $word[$i]="kein";
    
    }else if($category[$i]=="artneg" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine"){  $word[$i]="keines";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine"){  $word[$i]="keine";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral"){  $word[$i]="kein";
    
    }else if($category[$i]=="artneg" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine"){  $word[$i]="keinen";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine"){  $word[$i]="keiner";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral"){  $word[$i]="kein";
    
    }else if($category[$i]=="artneg" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine"){  $word[$i]="keines";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine"){  $word[$i]="keiner";
    }else if($category[$i]=="artneg" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral"){  $word[$i]="keines";
    
            // Art negative
    
    }else if($category[$i]=="artpos" && $case[$imasuno]=="nominative" && $type[$imasuno]=="masculine"){ $word[$i]="mein";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="nominative" && $type[$imasuno]=="femenine"){ $word[$i]="meine";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="nominative" && $type[$imasuno]=="neutral"){  $word[$i]="mein";
    
    }else if($category[$i]=="artpos" && $case[$imasuno]=="accusative" && $type[$imasuno]=="masculine"){  $word[$i]="meines";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="accusative" && $type[$imasuno]=="femenine"){  $word[$i]="meine";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="accusative" && $type[$imasuno]=="neutral"){  $word[$i]="mein";
    
    }else if($category[$i]=="artpos" && $case[$imasuno]=="dative" && $type[$imasuno]=="masculine"){  $word[$i]="meinen";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="dative" && $type[$imasuno]=="femenine"){  $word[$i]="meiner";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="dative" && $type[$imasuno]=="neutral"){  $word[$i]="mein";
    
    }else if($category[$i]=="artpos" && $case[$imasuno]=="genitive" && $type[$imasuno]=="masculine"){  $word[$i]="meines";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="genitive" && $type[$imasuno]=="femenine"){  $word[$i]="meiner";
    }else if($category[$i]=="artpos" && $case[$imasuno]=="genitive" && $type[$imasuno]=="neutral"){  $word[$i]="meines";
    
     
 } 
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
	//Masculino
	if ($spanish[$i]=="el" $genero[$imasuno]=="masculine") {$type[$i]="masculine";
	//Masculino
	}else if ($spanish[$i]=="el" $genero[$imasuno]=="femenine") {$type[$i]="wrong. Use la instead";$cspanish[$i]="la";
	//Femenino
	}else if ($spanish[$i]=="la" $genero[$imasuno]=="masculine") {$type[$i]="wrong. Use el instead";$cspanish[$i]="el";
	//Femenino
	}else if ($spanish[$i]=="la" $genero[$imasuno]=="femenine") {$type[$i]="femenine";
	
	//Indefinite article
	//Masculino
	}else if ($spanish[$i]=="un" $genero[$imasuno]=="masculine") {$type[$i]="masculine";
	//Masculino
	}else if ($spanish[$i]=="un" $genero[$imasuno]=="femenine") {$type[$i]="wrong. Use una instead";$cspanish[$i]="una";
	//Femenino
	}else if ($spanish[$i]=="una" $genero[$imasuno]=="masculine") {$type[$i]="wrong. Use un instead";$cspanish[$i]="un";
	//Femenino
	}else if ($spanish[$i]=="una" $genero[$imasuno]=="femenine") {$type[$i]="femenine";
	
	//Negative article
	//Masculino
	}else if ($spanish[$i]=="ningún" $genero[$imasuno]=="masculine") {$type[$i]="masculine";
	//Masculino
	}else if ($spanish[$i]=="ningún" $genero[$imasuno]=="femenine") {$type[$i]="wrong. Use ninguna instead";$cspanish[$i]="ninguna";
	//Femenino
	}else if ($spanish[$i]=="ninguna" $genero[$imasuno]=="masculine") {$type[$i]="wrong. Use ningún instead";$cspanish[$i]="ningún";
	//Femenino
	}else if ($spanish[$i]=="ninguna" $genero[$imasuno]=="femenine") {$type[$i]="femenine";
	}
}
    
    
    
    
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
   
     
   
        
  
    
    //We write grammar correction to write the errors messages
    
    
	for ($i=0; $i<=$numberofwords; $i++) 
	{
    $imasuno=$i+1;
    $imasdos=$i+2;
   
//12.2. Gender concordances
    //Genders are not correct "Der Mann"
    if($category[$i] == "artdef" && $category[$imasuno] == "substantive" && isset($type[$i]) && isset($type[$imasuno]) && isset($case[$imasuno]) &&($type[$i]!=$type[$imasuno] && $type2[$i]!=$type[$imasuno])) {
	
	
	
	$message[0]="Error! Genders are not correct! the article $word[$i] is $type[$i] $type2[$i] because the substantive $word[$imasuno] is $type[$imasuno] and in $case[$imasuno] case.";
		
	$word[$i]=$cword[$i];
      
      $message[1]="
      
      The correct sentence is: $word[0] $word[1] $word[2] $word[3] $word[4] $word[5] $word[6] $word[7] $word[8] $word[9] $word[10]";
      
      /*$message[2]="
      
      Please check the table below:
      
	Definiter Artikel	Masculine	Femenine	Neutral		Plural
	Nominative		Der		Die		Das		die
	Dative			dem		der		dem		den
	Accusative		den		die		das
	Genitive		des		der		des			";			
      */
      }
      
      //We show a message if there are no errors
      if(!$message[0]){
      $message[0]="";
      }
      
 
    if ($genero[$imasuno] =="masc" && $category[$i]=="artdef"){
    $spanish[$i]="el";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artdef"){
    $spanish[$i]="la";
    }else if($genero[$imasuno]=="masc" && $category[$i]=="artindef"){
    $spanish[$i]="uno";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artindef"){
    $spanish[$i]="una";
    }else if($genero[$imasuno]=="masc" && $category[$i]=="artneg"){
    $spanish[$i]="ningún";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artneg"){
    $spanish[$i]="ninguna";
    }else if($genero[$imasuno]=="masc" && $category[$i]=="artpos"){
    $spanish[$i]="mi";
    }else if($genero[$imasuno]=="fem" && $category[$i]=="artpos"){
    $spanish[$i]="mi";
    }
    
    
    }
     
      
      
          for ($i=0; $i<=$numberofwords; $i++) 
	  {
    $imasuno=$i+1;
    $imasdos=$i+2;
      
//12.3 Conjugation concordances
    
    //Conjugation is not correct 1st person "Ich komme"
      if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno] && $person[$i]=="1"){
		
	//$message[0]="Error! Conjugation is not correct! The personal pronoun $word[$i] is $person[$i] person while the verb $word[$imasuno] is $person[$imasuno] person.";
		
		
	//Conjugation is not correct 2nd person "du kommst"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno] && $person[$i]=="2"){
	
	//$message[0]="Error! Conjugation is not correct! The verb $word[$i] is $person[$i] person while the personal pronoun $word[$imasuno] is $person[$imasuno] person.";
		
	//Conjugation sie "sie kommt, sie kommen"
	} else if ($category[$i] =="propersnom" && $word[$i]=="sie" && $category[$imasuno] == "verb" && $person[$imasuno]=="3" || $person[$imasuno]=="4" ){
	
	
		
	//Conjugation is not correct 3rd person "er kommt"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$imasuno]=="3" || $person[$imasuno]=="5"){
	
	
	
	//Conjugation is not correct 3rd person "er kommt"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno]){
	
	//$message[0]="Error! Conjugation is not correct! The personal pronoun $word[$i] is $person[$i] person while the verb $word[$imasuno] is $person[$imasuno] person.";
	
	//Conjugation is not correct 4rd person "wir kommen sie kommen"
	} else if ($category[$i] =="propersnom" && $category[$imasuno] == "verb" && $person[$i]!=$person[$imasuno] && $person[$i]=="4" || $person[$i]=="6"){
		
	//$message[0]="Error! Conjugation is not correct test! The verb $word[$i] is $person[$i] person while the personal pronoun $word[$imasuno] is $person[$imasuno] person.";
	
    //Conjugation is not correct "komme ich"
      } else if ($category[$i] == "verb" && $category[$imasuno] == "propersnom" && $person[$i]!=$person[$imasuno] && $person[$i]!="4" && $person[$i]!="6" && $person[$i]!="3" && $case[$i]=="nominative") {
	
	//$message[0]="Error! Conjugation is not correct test! The verb $word[$i] is $person[$i] person while the personal pronoun $word[$imasuno] is $person[$imasuno] person.";
            
    //Conjugation is not correct "der mann kommt"
	}else if ($category[$i] == "artdef" && $category[$imasuno] == "substantive" && $category[$imasdos] == "verb" && $person[$imasdos]!="3") {
	 
	//$message[0]="Error! Conjugation is not correct! The verb $word[$imasdos] should be in the third person";
	 
	}
 }
      
 
      echo json_encode($message);
   
   
//15 Erase variable memory, results and close connection
    
    for ($i=0; $i<=numberofwords; $i++) {
    // free result set memory 
    mysql_free_result($result[$i]);
    }
    
    // close connection 
mysql_close(); 

?>

