
<!DOCTYPE html>
<html>
  <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
      	<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
      	<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
      	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      	<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" /> 

        <title>Indexation HTML</title>

        <link rel="stylesheet" type="text/css" href="resources/style.css">

  </head>
 
  <body >

<!-- Image Logo -->     
    <div >
        <img class="img" src="resources/logo.png">
    </div>

<!-- Barre de recherche -->  
    <div class="container h-17">
      <div class="d-flex justify-content-center h-100">
        <div class="searchbar">
        <form action="rechercher.php" method="post" id="form-id">
          <input class="search_input" type="text" name="query" placeholder="Search...">
          <a class="search_icon" onclick="document.getElementById('form-id').submit();">
          <i class="fas fa-search"></i></a>
        </form>
        </div>
      </div>
    </div>
  

<!-- Container des résultats de recherche -->  
<div class="container">
  <div class="row">
  	<div class="col-md-2"></div>
  	 <div class="col-md-8">
    
<?php 

//Inclure la bibliothèque contenant nos fonctions  
include 'resources/bibliotheque10.inc.php';
  
    //Récupérer le mot saisi dans la barre de recherche
      if (isset($_POST["query"]))
        $query = $_POST["query"];

    //Récupérer le query du lien de la page actuelle
      if (isset($_GET['query'])) 
        $query = $_GET['query'];

    //Initialisation d'un message d'erreur si aucun mot n'est saisi 
      $error="";

    //Vérification si y'a eu un submit de la recherche
    if (isset($query)){

      //Vérification si query n'est pas juste un espace vide
      if (trim($query) == "") {
        $error = "Veuillez svp saisir un mot pour la recherche !!!";
      }

      //Si query est valide la recherche commence
      else{

      //Etablir une connexion avec la BDD
      $connexion = mysqli_connect("localhost","root","","tiw");

      //Récupération du numéro de page actuelle du lien en haut 
      if(isset($_GET['page']))    $page=$_GET['page'];
      //Sinon c'est la première page
      else    $page=1; 

      //Fixer le nombre de résultats qu'on veut par page
      $limit = 1;
      //Calcul du début des résultats à afficher pour chaque page
      $debut = ($page * $limit) - $limit;
      		
      //Requête de récupération du nombre de résultats défini à partir du début calculé pour chaque page
      	$sql = "SELECT document.id, document.source, document.titre, document.descriptif, mot_document.poids  FROM ((document INNER JOIN mot_document ON document.id = mot_document.idSource) INNER JOIN mot ON mot_document.idMot = mot.id) where mot.mot = '$query' ORDER BY poids DESC LIMIT $limit OFFSET $debut";

      //Requête de récupération de tous les résultats de la recherche pour query
        $sql_count = "SELECT * FROM ((document INNER JOIN mot_document ON document.id = mot_document.idSource) INNER JOIN mot ON mot_document.idMot = mot.id) where mot.mot = '$query' ";

      //Résultats à afficher dans une page
      	$resultat = mysqli_query($connexion,$sql);

      //Nombre total des résultats --> utilisé pour calculer le nombre des pages nécessaires 
      	$nbr_resultats = mysqli_num_rows(mysqli_query($connexion,$sql_count));
        $nbr_pages = ceil($nbr_resultats/$limit);

      //Numéro de pages précédente et suivante à la page actuelle
        $prev_page = $page - 1;
        $next_page = $page + 1;

      //Affichage du nombre des résultats trouvés pour le mot recherché
      	echo "<br><b>$nbr_resultats</b> Résultats trouvés pour <b>$query</b> :<br><br>";
      	
      //Afficher des attributs nécessaires pour chaque résultat 
      	while ($ligne = mysqli_fetch_row($resultat)) {

          //Affichage du titre du document et poids du query dans ce document  
      	 	echo "<a href='$ligne[1]' target='_blank'><font color="."navy"."><b>$ligne[2]</b></font></a>"."($ligne[4])";

          //Affichage de la source du docmument + le bouton pour afficher e cacher le nuage 
      	 	echo "<br><font color="."green".">".$ligne[1]."</font>".'<button class="btn btn-link" onclick="myFunction(this,'.$ligne[0].')">nuage(+)</button><br>';

          //Affichage du descriptif du document 
      	 	echo $ligne[3]."<br>";

      //Requête de récupération d'une liste de 35 mots aléatoires du document pour le nuage des mots clés
        $sql_nuage = "SELECT mot.mot, mot_document.poids  FROM ((document INNER JOIN mot_document ON document.id = mot_document.idSource) INNER JOIN mot ON mot_document.idMot = mot.id) where document.id = '$ligne[0]' ORDER BY rand() LIMIT 35";

        //Résultats des mots pour le nuage
        $resultat_nuage = mysqli_query($connexion,$sql_nuage);

        //On met les résultats dans un tableau associatif pour donner en paramètres à la fonction generernuage()
        $tab_nuage=array();
        while ($lign = mysqli_fetch_row($resultat_nuage)) {
           $tab_nuage += [ $lign[0] => $lign[1] ];
        }

        // On vérifie si query figure das la liste aléatoire, sinon on l'ajoute 
        if (!in_array($query, $tab_nuage)) {
          $tab_nuage += [ $query => $ligne[4] ];
        }

          //Affichage du nuage
          echo '<br><div class="tagcloud" style="display:none" id="'.$ligne[0].'">
          '.genererNuage($tab_nuage,$ligne[1]).'
          </div>

          <script>
          function myFunction(bouton,id) {
            var x = document.getElementById(id);
            if (x.style.display === "none") {
              x.style.display = "block";
              bouton.innerHTML="nuage(-)";
            } else {
              x.style.display = "none";
              bouton.innerHTML="nuage(+)";
            }
          }
          </script><br><hr><br>';
          }
      	 } 
        }
      	?>
      </div>
      </div>
      </div>

<br> <hr>

<!-- Pagination des résultats -->
<nav aria-label="Page navigation example ">
  <ul class="pagination pg-blue justify-content-center">
   
    <?php 
    //Vérification de la validité du nbr_pages pour afficher la pagination
    if (isset($nbr_pages)) {

          //si on est sur la première page, on désactive le boutton Previous 
          if ($prev_page == 0) {?>
               <li>  <a class="page-link" disabled="disabled">Previous</a> </li>
                <?php }?>
          
          <?php 
          //Sinon on le boutton Previous est active et envoie à la page précédente
          if ($prev_page != 0) {?>          
              <li> <a class="page-link" href="rechercher.php?page=<?php echo($prev_page)?>&query=<?php echo($query)?>" >Previous</a></li> 
              <?php }?>

          <?php  
          //Affichage des numéro de pages disponibles avec le lien qui envoie à la page concernée
          for($i=1;$i<=$nbr_pages;$i++) {?>
              <li class="page-item">

                <?php 
                //Distinguer le numéro de page sur laquelle on se trouve
                if ($i == $page) {?>
                   <li class="page-item active"><span class="page-link" href="rechercher.php?page=<?php echo($i)?>&query=<?php echo($query)?>"><?php echo($i)?><span class="sr-only">(current)</span></span></li>
                  <?php }?>
                <?php if($i != $page) {?>
                    <a class="page-link" href="rechercher.php?page=<?php echo($i)?>&query=<?php echo($query)?>"><?php echo($i)?></a>
                  <?php }?>
              </li>
          <?php }?>
                  
          <?php 
          //si on est sur la dérnière page, on désactive le bouton Next 
            if ($page == $nbr_pages) {?>
                 <li>  <a class="page-link" disabled="disabled">Next</a> </li>
                  <?php }?>

          <?php
          //Sinon on le boutton Next est active et envoie à la page suivante
            if ($page != $nbr_pages) {?>
            <li> <a class="page-link" href="rechercher.php?page=<?php echo($next_page)?>&query=<?php echo($query)?>" >Next</a></li>  
            <?php }?>
          <?php }?>
    
  </ul>
</nav>

<!-- Footer -->
<footer class="page-footer  font-small blue-grey lighten-5">
    <!-- affichage du message d'erreur en cas de recherche sur un vide --> 
    <div style="text-align: center; color: red"><a> <b> <?php echo $error; ?> </b></a></div>
    <div class="footer-copyright text-center text-black-50 py-3">© 2019 Copyright:
      <a class="dark-grey-text" href="https://www.univ-paris8.fr/Master-Technologies-de-l-Hypermedia"> Master THYP Paris8</a>
    </div>

  </footer>

  </body>
</html>
