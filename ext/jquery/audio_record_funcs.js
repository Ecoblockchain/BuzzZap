$(function(){
	function recAudio(mediaTypes, mediaSuccess, mediaError){
		navigator.mediaDevices.getUserMedia(mediaTypes).then(mediaSuccess).catch(mediaError);
	}
	function mediaError(e) {
		console.error('media error', e);
	}

	var mediaRecorder; 
	var index = 1;

	function bytesToSize(bytes) {
	    var k = 1000;
	    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
	    if (bytes === 0) return '0 Bytes';
	    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(k)), 10);
	    return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
	}
	function getTimeLength(milliseconds) {
	    var data = new Date(milliseconds);
	    return data.getUTCHours() + " hours, " + data.getUTCMinutes() + " minutes and " + data.getUTCSeconds() + " second(s)";
	}
	var formData = new FormData();
	function accRecord(stream){
	    
			mediaRecorder = new MediaStreamRecorder(stream);
	    mediaRecorder.mimeType = 'audio/wav';
	    mediaRecorder.type = StereoAudioRecorder;
	    mediaRecorder.audioChannels = 2;
	    mediaRecorder.ondataavailable = function(blob) {
	        var fileType = "audio";
	        formData.append(fileType + '-filename', fileName);
			formData.append(fileType + '-blob', blob);

	    };
	    var timeInterval = 100000000;
	    mediaRecorder.start(timeInterval);
	    return formData;
	}
});