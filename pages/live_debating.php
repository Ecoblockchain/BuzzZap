<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
?>	
<script>
	$(function(){

		//navigator.mediaDevices.getUserMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
		var mediaTypes= {video: false, audio: true};
		function mediaSuccess(){

		}
		function mediaError(){
			
		}
		navigator.mediaDevices.getUserMedia(mediaTypes).then(mediaSuccess).catch(mediaError);

		var peer = new Peer({key: 'clw4u42wmqjjor'});
		peer.on('open', function(id) {
		  console.log('My peer ID is: ' + id);
		});
	});
</script>