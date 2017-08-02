<?php require_once('api.php'); ?>

<?php

   $api = new goosegrade('duanestorey','e3b9a1e97951b577b1ae6820a440a5b8');
   $corrections = $api->getCorrections();

?>
