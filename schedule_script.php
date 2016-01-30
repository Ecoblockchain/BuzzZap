<?php
$file = fopen("tseting-cron.txt", "w+");
fwrite($file , 'worked '.time().' /n');
fclose($file);
?>