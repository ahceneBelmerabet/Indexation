<P><B>Début d'indexation:  </B>  <?php echo " ", date ("h:i:s"); ?></P>

<?php

include 'resources/bibliotheque10.inc.php';

//Augmentation du temps d'exécution de ce script
set_time_limit (500);
$path= "ccm";

// Appel à la fonction d'indexation d'un repertoire 
indexerRepertoire($path);

?>

<P><B>Fin d'indexation :   </B> <?php echo " ", date ("h:i:s"); ?></P>
