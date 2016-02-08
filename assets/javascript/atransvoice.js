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
    
  --------------------- Analytical Translator Functions ------------------------------------------------
  This is a collection of javascript functions for voice recognition in the Analytical translator frontend
  ------------------------------------------------------------------------------------------------------

  */

//atranslator voice

//Voice recognition functions
  
    // Test browser support
      window.SpeechRecognition = window.SpeechRecognition       ||
                                 window.webkitSpeechRecognition ||
                                 null;
 
      if (window.SpeechRecognition === null) {
        document.getElementById('ws-unsupported').classList.remove('hidden');
        document.getElementById('button-play-ws').setAttribute('disabled', 'disabled');
        document.getElementById('button-stop-ws').setAttribute('disabled', 'disabled');
      } else {
        
        var recognizer = new window.SpeechRecognition();
        var transcription = document.getElementById('German');
        var log = document.getElementById('log');
 
        // Recogniser doesn't stop listening even if the user pauses
        recognizer.continuous = true;
        recognizer.lang="de-DE";
      }
        // Start recognising
        recognizer.onresult = function(event) {
          transcription.textContent = '';
 
          for (var i = event.resultIndex; i < event.results.length; i++) {
            if (event.results[i].isFinal) {
              transcription.textContent = event.results[i][0].transcript;
              confidence= 'Confidence: ' + event.results[i][0].confidence;
              log.innerHTML = confidence;
              
            Translate(transcription.textContent);
            
            } else {
              transcription.textContent += event.results[i][0].transcript;
            }
          }
        };
 
        // Listen for errors
        recognizer.onerror = function(event) {
          //log.innerHTML = 'Recognition error: ' + event.message + '<br />' + log.innerHTML;
          log.innerHTML = 'Recognition error: ' + event.message + log.innerHTML;
        };
 
        document.getElementById('button-play-ws').addEventListener('click', function() {
          // Set if we need interim results
          recognizer.interimResults = document.querySelector('input[name="recognition-type"][value="interim"]').checked;
 
          try {
            recognizer.start();
            //log.innerHTML = 'Recognition started' + '<br />' + log.innerHTML;
            log.innerHTML = 'Recognition started' + log.innerHTML;
          } catch(ex) {
            //log.innerHTML = 'Recognition error: ' + ex.message + '<br />' + log.innerHTML;
            log.innerHTML = 'Recognition error: ' + ex.message + log.innerHTML;
          }
        });
 
        document.getElementById('button-stop-ws').addEventListener('click', function() {
          recognizer.stop();
          //log.innerHTML = 'Recognition stopped' + '<br />' + log.innerHTML;
          log.innerHTML = 'Recognition stopped' + log.innerHTML;
        });
 
        document.getElementById('clear-all').addEventListener('click', function() {
          transcription.textContent = '';
          log.textContent = '';
        });