<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
?>	
<script>
	$(function(){

		
		function getParameterByName(name){
		    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		        results = regex.exec(location.search);
		    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		}
		var destid = getParameterByName('destid');

		/*function getAudio(successCallback, errorCallback){
		    navigator.getUserMedia({
		        audio: true,
		        video: false
		    }, successCallback, errorCallback);
		}

		//request call
		var from = getParameterByName('from');
		var to = getParameterByName('to');
		var sss = getParameterByName('s');

		$('#start-call').click(function(){

		    console.log('starting call...');

		    getAudio(
		        function(MediaStream){
		        	var conn = peer.connect(sss);
		            console.log('now calling ' + to);
		            var call = peer.call(to, MediaStream);
		            call.on('stream', onReceiveStream);
		        },
		        function(err){
		            console.log('an error occured while getting the audio');
		            console.log(err);
		        }
		    );

		});
		//recieve call
		function onReceiveCall(call){

		    console.log('peer is calling...');
		    console.log(call);
		    peer.on('connection', function(conn) {
		    	console.log("con recieved");
		    });
		    getAudio(
		        function(MediaStream){
		            call.answer(MediaStream);
		            console.log('answering call started...');
		        },
		        function(err){
		            console.log('an error occured while getting the audio');
		            console.log(err);
		        }
		    );

		    call.on('stream', onReceiveStream);
		}

		function onReceiveStream(stream){
		    var audio = document.querySelector('audio');
		    audio.src = window.URL.createObjectURL(stream);
		    audio.onloadedmetadata = function(e){
		        console.log('now playing the audio');
		        audio.play();
		    }
		}*/


		var peer = new Peer({host: 'www.buzzzap.com', port:9000, path:''});

		peer.on('open', function(id) {
		  console.log('My peer ID is: ' + id);
		});

		$('#start-call').click(function(){
			var conn = peer.connect(destid);
				
			peer.on('connection', function(conn){
				conn.send('Hello!');
				console.log("rec: "+conn);
			});

			conn.on('data', function(data) {
			 	console.log(data);
			});
		
		});
		


		


	});
</script>
<div id = "start-call"> Start Call </div>
<audio controls></audio>