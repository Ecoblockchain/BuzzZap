
<script type="text/javascript" src = "ext/jquery/jquery.js"></script>
<script type="text/javascript" src = "ext/jquery/jqueryColorAnimation.js"></script>
<script type="text/javascript" src = "ext/jquery/jqueryRotate.js"></script>
<script type="text/javascript" src = "ext/jquery/jqueryShadowAnimation.js"></script>
<script type="text/javascript" src = "ext/jquery/jqueryFloat.js"></script>
<script type="text/javascript" src = "ext/jquery/jqueryf.js"></script>
<script type="text/javascript" src = "ext/jquery/typewriter.js"></script>
<script type="text/javascript" src = "ext/jquery/typed.js"></script>
<script type="text/javascript" src = "ext/jquery/jquery.window.js"></script>
<script src="https://cdn.webrtc-experiment.com/MediaStreamRecorder.js"></script>
<script type="text/javascript" src = "http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="https://cdn.rawgit.com/webrtc/adapter/master/adapter.js"></script>
<link href='ext/styles/styles.css' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Dosis|Poiret+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,100' rel='stylesheet' type='text/css'>
<?php
require("functions.php"); 
require("connect_db.php");
if(require('class.phpmailer.php')){
	
}
if(isset($_GET['page'])){
	require("ext/predtextjs.php");
}
?>