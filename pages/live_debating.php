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

		var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
		var peer = new Peer({host: 'www.buzzzap.com', port:9000, path:''});

		peer.on('open', function(id) {
		  console.log('My peer ID is: ' + id);
		});

		$('#start-call').click(function(){
			var conn = peer.connect(destid);
			getUserMedia({video: false, audio: true}, function(stream) {
				var call = peer.call(destid, stream);
				call.on('stream', function(stream) {
				    var audio = document.getElementById('audio');
			   		audio.src = window.URL.createObjectURL(stream);
			    	audio.onloadedmetadata = function(e){
			        	console.log('now playing the audio');
			        	audio.play();
			   		}
				});
			}, function(err) {
			  console.log('Failed to get local stream' ,err);
			});

			conn.on('data', function(data) {
		 		console.log(data);
			});
		});


		peer.on('connection', function(conn){
			conn.send('Hello!');
			console.log("rec: "+conn);
			peer.on('call', function(call) {
				getUserMedia({video: false, audio: true}, function(stream) {
			   		call.answer(stream);
			    	call.on('stream', function(stream) {
			     		var audio = document.getElementById('audio');
			   			audio.src = window.URL.createObjectURL(stream);
			    		audio.onloadedmetadata = function(e){
			        	console.log('now playing the audio');
			        	audio.play();
			   		}
			    	});
			  	}, function(err) {
			    	console.log('Failed to get local stream' ,err);
			  	});
			});
		});

		
		


		


	});
</script>
<div id = "start-call"> Start Call </div>
<audio id = "audio"></audio>