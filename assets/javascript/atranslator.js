/*

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
    


  --------------------- Analytical Translator Functions ----------------------------------
  This is a collection of javascript functions for the Analytical translator frontend
  ----------------------------------------------------------------------------------------
*/


// Here starts the main javascript functions
 
 function requestresult(FeldValue)			// This is the main function 
{							// It check what is written from the source language and it send it
if (FeldValue.length == 0) {				// to the php function translatorfunction.php to receive the translation
  
  document.getElementById("spanish").value="";
  return;
  }
  
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function(){
      
      if (xmlhttp.readyState==4 && xmlhttp.status==200)
	{
	document.getElementById("spanish").value=JSON.parse(xmlhttp.responseText).join(" "); //This is to separate words with spaces
	}
      }
    xmlhttp.open("POST","http://lingoworld.eu/lingoworld/translator/website/php/translatorfunction.php",true);		//This send the request to the PHP function translatorfunction.php
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlhttp.send('text=' + encodeURIComponent(FeldValue));
   }
   
    function requestmessage(FeldValue)			// This is a second function
{							// It send what is written from the source language to a php function
if (FeldValue.length == 0) {				// called "messagefunction.php" which chech case concordances and grammar rules
							// it returns grammar and case corractions and explanations to the message text box
  document.getElementById("message").value="";
  return;
  }
  
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function(){
      
      if (xmlhttp.readyState==4 && xmlhttp.status==200)
	{
	document.getElementById("message").value=JSON.parse(xmlhttp.responseText).join(" "); //This is to separate words with spaces
	}
      }
    xmlhttp.open("POST","http://lingoworld.eu/lingoworld/translator/website/php/messagefunction.php",true);
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlhttp.send('text=' + encodeURIComponent(FeldValue));
   }

  
 function Translate (FeldValue) {			//This function call the two functions requestmessage and requestresult
  requestresult(FeldValue);
  requestmessage(FeldValue);
    
    var ger = document.getElementById("spanish");
    if (FeldValue.length == 0) {
       ger.value = "";
    }
    else if (FeldValue.length > 0) {
      var c = FeldValue.charAt(FeldValue.length-1);
      if  (c <= ' ' || c == '.' || c == ',' || c == '?')
      {
        ger.value = FeldValue
                
      }
    }
  }
   
// Bootstrap and sidr functions

    $(document).ready(function() {
    setInterval(showQuote,speed);
    setTimeout(typetext1,10000);
    setTimeout(typetext2,15000);
    setTimeout(typetext3,20000);
});    

      $(document).ready(function() {		//SIDR FUNCTION
  $('#simple-menu').sidr();
});    
    $(document).ready(function() {
    $('#left-menu').sidr({
      name: 'sidr-left',
      side: 'left' // By default
    });
    $('#right-menu').sidr({
      name: 'sidr-right',
      side: 'right'
    });
});
    
$(document).ready(function() {			//Script for menu content: Existing content
    $('#existing-content-menu').sidr({
      name: 'sidr-existing-content',
      source: '#demoheader, #demo-content'
    });
/*
      $('#remote-content-menu').sidr({		//Script for menu content: Load remotelly
      name: 'sidr-remote-content',
      source: 'wiki.html'
    });
*/
    $('#callback-menu').sidr({			//Script for menu content: Callback loaded
      name: 'sidr-callback',
      source: function(name) {
        return '<h1>' + name + ' menu</h1><p>Yes! You can use a callback too ;)</p>';
      }
    });
});

  $('#responsive-menu-button').sidr({		//Responsive menu
      name: 'sidr-main',
      source: '#navigation'
    });
 
      $(window).touchwipe({			//Swipe Menu: Wipe left
        wipeLeft: function() {
          // Close
	  $.sidr('close', 'sidr-left');
          $.sidr('close', 'sidr-right');
          $.sidr('close', 'sidr-remote-content');
        },
        wipeRight: function() {			//Swipe Menu: Wipe right
          // Open
	  $.sidr('open', 'sidr-left');          
        },
        preventDefaultEvents: false
      });
    
    $(function () {		//popover function to show messages
      $('[data-toggle="popover"]').popover('toggle')
  })
    
    function typetext1() {		//popover function to show messages
      $('.bottomtext1').empty();
      $('.bottomtext1').typetype('Analytical translator')
      //setTimeout(typetext1a,30000);
  }
  
    function typetext1a() {		//popover function to show messages
      $('.bottomtext1').empty();
      $('.bottomtext1').typetype('Software under General Public Licence Version 3')
      setTimeout(typetext1,30000);
  }
  
  function typetext2() {		//popover function to show messages
    $('.bottomtext2').empty();  
    $('.bottomtext2').typetype('Developed by Raul on 2014')
      //setTimeout(typetext2a,30000);
  }
  
   function typetext2a() {		//popover function to show messages
      $('.bottomtext2').empty();
      $('.bottomtext2').typetype('Feel free to share it')
      setTimeout(typetext2,30000);
  }
  
  function typetext3() {		//popover function to show messages
      $('.hypervinculo').typetype('https://github.com/xpheres/analyticaltranslator')
  }
  
  //Here starts the variables and functions for quotes
var quote=new Array();
  quote[0]='Do you know that gender and case discordances alerts will be shown on the messages text box above?';    	// add as many quotes as you like!//
  quote[1]='You can help us to improve this tool, check out the vocabulary,syntax models and code and submit your changes!';
  quote[2]='You can help us to improve the design, feel free to check the css and JS code, clone it and submit your code improvements';
  quote[3]='Do you know that you can use the microphone icon to speak sentences as an alternative to the keyboard?';
  quote[4]='Do you know that you can hear the translated sentences by clicking on the speaker icon? Speech synthesis is only supported with chrome and safari browsers';
  quote[5]='You can see some issues and improvements plans on the issues link, feel free to submit your feedback and solutions!';
  quote[6]='Do you know that you can use the OCR icon to write sentences with a pen or your finger? Write naturally with your hand on your tactile device!';
  quote[7]='Do you know that there are gesture events for tactile devices? Swipe your finger and release and hide the left menu on your mobile or tablet.';
  quote[8]='Do you know that this translator translate syntax? Feel free to submit new syntax models for the source and target language to improve this tool!';
  quote[9]='Feel free to submit vocabulary for new languages, as soon as enough vocabulary and syntax are implemented they will be released';
  var speed=10000;    //this is the time in milliseconds adjust to suit//
var q=0;


function showQuote() {
  
     document.getElementById("quotes").setAttribute('data-content',quote[q]);
	$('[data-toggle="popover"]').popover('toggle')
	$('.popover').empty();
	//$('.input-box').typetype('Some text')
	//$('.popover').typetype(quote[q], {e: 0, t: 25, }) // e: error rate. (use e=0 for perfect typing) t: interval between keypresses	    	
	document.getElementById("quotes").innerHTML=quote[q];
	q++;
	
	//$('.input-box').empty();
	
     if(q==quote.length) {
     q=0;
     }
}

function handleLoad(event) {
}

function handleError(event) {
}





