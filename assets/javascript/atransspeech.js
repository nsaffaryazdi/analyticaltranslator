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
  This is a collection of javascript speech functions for the Analytical translator frontend
  ----------------------------------------------------------------------------------------
*/


// Code for Speech synthesis starts here  ______________________________________
   
      // Test browser support
      if (window.SpeechSynthesisUtterance === undefined) {
        document.getElementById('ss-unsupported').classList.remove('hidden');
        ['button-speak-ss', 'button-stop-ss', 'button-pause-ss', 'button-resume-ss'].forEach(function(elementId) {
          document.getElementById(elementId).setAttribute('disabled', 'disabled');
        });
      } else {
        var text = document.getElementById('spanish');
        var voices = document.getElementById('voice');
        var rate = document.getElementById('rate');
        var pitch = document.getElementById('pitch');
        var log = document.getElementById('log');
 
        // Workaround for a Chrome issue (#340160 - https://code.google.com/p/chromium/issues/detail?id=340160)
        var watch = setInterval(function() {
          // Load all voices available
          var voicesAvailable = speechSynthesis.getVoices();
 
          if (voicesAvailable.length !== 0) {
            for(var i = 0; i < voicesAvailable.length; i++) {
              voices.innerHTML += '<option value="' + voicesAvailable[i].lang + '"' +
                                  'data-voice-uri="' + voicesAvailable[i].voiceURI + '">' +
                                  voicesAvailable[i].name +
                                  (voicesAvailable[i].default ? ' (default)' : '') + '</option>';
            }
 
            clearInterval(watch);
          }
        }, 1);
 
        document.getElementById('button-speak-ss').addEventListener('click', function(event) {
          event.preventDefault();
 
          var selectedVoice = voices.options[voices.selectedIndex];
 
          // Create the utterance object setting the chosen parameters
          var utterance = new SpeechSynthesisUtterance();
 
          utterance.text = text.value;
          utterance.voice = selectedVoice.getAttribute('data-voice-uri');
          utterance.lang = selectedVoice.value;
          utterance.rate = rate.value;
          utterance.pitch = pitch.value;
 
          utterance.onstart = function() {
            log.innerHTML = 'Speaker started' + '<br />' + log.innerHTML;
          };
 
          utterance.onend = function() {
            log.innerHTML = 'Speaker finished' + '<br />' + log.innerHTML;
          };
 
          window.speechSynthesis.speak(utterance);
        });
 
        document.getElementById('button-stop-ss').addEventListener('click', function(event) {
          event.preventDefault();
 
          window.speechSynthesis.cancel();
          log.innerHTML = 'Speaker stopped' + '<br />' + log.innerHTML;
        });
 
        document.getElementById('button-pause-ss').addEventListener('click', function(event) {
          event.preventDefault();
 
          window.speechSynthesis.pause();
          log.innerHTML = 'Speaker paused' + '<br />' + log.innerHTML;
        });
 
        document.getElementById('button-resume-ss').addEventListener('click', function(event) {
          event.preventDefault();
 
          if (window.speechSynthesis.paused === true) {
            window.speechSynthesis.resume();
            log.innerHTML = 'Speaker resumed' + '<br />' + log.innerHTML;
          } else {
            log.innerHTML = 'Unable to resume. Speaker is not paused.' + '<br />' + log.innerHTML;
          }
        });
 
        document.getElementById('clear-all').addEventListener('click', function() {
          log.textContent = '';
        });
      }