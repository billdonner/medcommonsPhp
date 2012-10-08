<?php
header("Location: https://" . $_SERVER['HTTP_HOST']
                     . rtrim(dirname($_SERVER['PHP_SELF']), '/\\')
                     . "/" . "php-ca/start.htm");
?> 
