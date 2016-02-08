/*
  			 GOCR (JOCR at SF.net)

GOCR is an optical character recognition program, released under the
GNU General Public License. It reads images in many formats  and outputs
a text file. Possible image formats are pnm, pbm, pgm, ppm, some pcx and
tga image files. Other formats like pnm.gz, pnm.bz2, png, jpg, tiff, gif,
bmp will be automatically converted using the netpbm-progs, gzip and bzip2
via unix pipe.
A simple graphical frontend written in tcl/tk and some
sample files are included.
Gocr is also able to recognize and translate barcodes.
You do not have to train the program or store large font bases.

more info: http://jocr.sourceforge.net/

Authors (in chronological order):

Joerg Schulenburg  <jNOschulen{at}gmx.SPAM.de> (remove NO+SPAM for valid EMAIL address)
	* Original idea and creation, programmer leader

Bruno Barberi Gnecco <brunobg{at}users.sourceforge.net>
	* Programmer


----------------------------------------------------------------------------

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
  This is a collection of OCR functions for the Analytical translator frontend
  ----------------------------------------------------------------------------------------
*/




			var c = document.getElementById('c'),
				o = c.getContext('2d');

			function reset_canvas(){
				o.fillStyle = 'white'
				o.fillRect(0, 0, c.width, c.height)
				o.fillStyle = 'black'	
			}

			// here's a really simple little drawing app so people can try their luck at
			// the lottery that is offline handwriting recognition
			var drag = false, lastX, lastY;
			c.onmousedown = function(e){ drag = true; lastX = 0; lastY = 0; e.preventDefault(); c.onmousemove(e) }
			c.onmouseup   = function(e){ drag = false; e.preventDefault(); runOCR() }
			c.onmousemove = function(e){
				e.preventDefault()
				var rect = c.getBoundingClientRect();
				var r = 5;

				function dot(x, y){
					o.beginPath()
					o.moveTo(x + r, y)
					o.arc(x, y, r, 0, Math.PI * 2)
					o.fill()
				}
				if(drag){
					var x = e.clientX - rect.left, 
						y = e.clientY - rect.top;
					
					if(lastX && lastY){
						var dx = x - lastX, dy = y - lastY;
						var d = Math.sqrt(dx * dx + dy * dy);
						for(var i = 1; i < d; i += 2){
							dot(lastX + dx / d * i, lastY + dy / d * i)
						}
					}
					dot(x, y)

					lastX = x;
					lastY = y;
				}
			}
			
			var lastWorker;

			function runOCR(){
				if(lastWorker) lastWorker.terminate();

				var worker = new Worker('http://lingoworld.eu/lingoworld/translator/assets/jquery/worker.js')
				worker.onmessage = function(e){
					console.log(e.data)
					if('innerText' in document.getElementById("text")){
						document.getElementById("text").innerText = e.data
					}else{
						document.getElementById("text").textContent = e.data	
					}
					document.getElementById('German').innerHTML = e.data;
					document.getElementById('timing').innerHTML = 'recognition took ' + ((Date.now() - start)/1000).toFixed(2) + 's';
					worker.terminate();
				}
				var start = Date.now()
				var image_data = o.getImageData(0, 0, c.width, c.height)
				
				worker.postMessage(image_data)

				lastWorker = worker;
			}

			reset_canvas()


			var quotes = [
				'Please write your sentence by hand here!',
				'Grumpy wizards make toxic brew for the evil Queen and Jack.',
				'The Quick Brown Fox Jumped Over The Lazy Dog.',
				'Everything is linear when plotted log-log with a fat magic marker.',
				'Hello OCR!',
				'This demo better put Engelbart to shame!',
				'Very OCR. Such Recognize. Wow.',
				'Much Text. Wow. So Letters. Very Recognition. Wow.',
				"I don't know what to say.",
				'Here are some words.',
				'Words words words words words words words - Hamlet.',
				'The Very Quick, Much Brown Fox Jumped So Over Such Lazy Doge. Wow.',
				'Is this algorithm better than a fifth grader? (no)',
				'I am Cow. Hear me moo! I weigh twice as much as you.',
				'Nineteen Eighty Four',
				'How many tweets would a twit-chuck tweet if a tweet could tweet.',
				'I shall call him squishy and he shall be my squishy.',
				'Such Text. Very OCR. Much Optical. Wow.',
				"Here's to looking at pixels, kid.",
				"Do or do not, there is no try.",
				"Yo Banana Boy!",
				"This shit is bananas, B-A-N-A-N-A-S.",
				"I have discovered a truly marvelous proof which this box is too small to contain.",
				"Tech-mol-ogy is it good or is it whack?",
				"Say What Again! I dare you! I double-dare you!",
				"D-I-N-O-S-A-YOU ARE A DINOSAUR",
				"Hello my name is dug. I have just met you. I love you.",
				"ABC DEF GHI JKL MNO PQR STU VWX YZ",
				"abc def ghi jkl mno pqr stu vwx yz",
				"0 1 2 3 4 5 6 7 8 9",
				"One Two Three Four Five Six Seven Eight Nine Ten",
				"Your mother was a hamster and your father smelt of elderberries.",
				"Hello World!",
				"Goodnight, cruel world!",
				"Do not go gentle into that good night",
				"To be, or not to be: That is the question. Whether tis nobler in the mind to suffer the slings and arrows of outrageous fortune.",
				"You're not crazy!",
				"Harry Potter should ride a Roomba.",
				"Time flies like an arrow, Fruit flies like a banana.",
				"This message is bludgeoning the deceased equine.",
				"Rawr! I'm a dinosaur!",
				"Hesitation is always easy but rarely useful.",
				"Quis custodiet ipsos custodes?",
				"Tuesday's meeting of the apathy club was canceled due to lack of interest.",
				"We sell your users so you don't have to!",
				"Life is good.",
				"A true magician never unveils his trick.",
				"Ceci n'est pas une pipe."
			];
			var fonts = ['Droid Sans', 'Philosopher', 'Alegreya Sans', 'Chango', 'Coming Soon', 'Allan', 'Cardo', 'Bubbler One', 'Bowlby One SC', 'Prosto One', 'Rufina', 'Cantora One', 'Denk One', 'Play', 'Architects Daughter', 'Nova Square', 'Inder', 'Gloria Hallelujah', 'Telex', 'Comfortaa', 'Merienda', 'Boogaloo', 'Krona One', 'Orienta', 'Sofadi One', 'Source Sans Pro', 'Revalia', 'Overlock', 'Kelly Slab', 'Rye', 'Butcherman', 'Lato', 'Milonga', 'Aladin', 'Princess Sofia', 'Audiowide', 'Italiana', 'Michroma', 'Cabin Condensed', 'Jura', 'Marko One', 'PT Mono', 'Bubblegum Sans', 'Amaranth']
			

			function fisher_yates(a) {
				for (var i = a.length - 1; i > 0; i--) {
					var j = Math.floor(Math.random() * (i + 1));
					var temp = a[i]; a[i] = a[j]; a[j] = temp;
				}
				return a;
			}

			
			fonts = fisher_yates(fonts);
			quotes = fisher_yates(quotes);

			function da_word(){
				reset_canvas()
				
				var font = fonts.shift(); fonts.push(font); // do a rotation

				if(Math.random() > 0.7){
					var phrase = font;
				}else{
					var phrase = quotes.shift() //quotes[Math.floor(quotes.length * Math.random())];
					quotes.push(phrase);
				}
				
				WebFont.load({
					google: {
						families: [font]
					},
					active: function(){
						o.font = '30px "' + font + '"'
						var words = phrase.split(' '), buf = [], n = 70;
						for(var i = 0; i < words.length; i++){
							buf.push(words[i])
							if(buf.join(' ').length > 15 || i == words.length - 1){
								o.fillText(buf.join(' '), 50, n);
								buf = []
								n += 50
							}
						}
						runOCR();
					}
				})
				
			}

			o.font = '30px sans-serif'
			o.fillText("Please write your sentence by hand here!!", 50, 100);
			runOCR();