<?
$content = '
		<div class="boxheader">Logiciel XName</div>
		<div class="boxcontent">
		Vous avez installé le logiciel XName avec succès (?). <br >
		Prenez garde aux points suivants:
		<ul>
		<li> copiez libs/config.default en libs/config.php</li>
		<li> Modifiez tous les éléments dans libs/config.php</li>
		<li> Si vous avez des erreurs mysql, vérifiez que l\'utilisateur
		configuré dans config.php existe, et que le nom de la base de données
		est le même que celui que vous avez modifié dans sql/creation.sql</li>
		<li> Modifiez ce texte - html/includes/strings/fr/index_content.php (et copiez
		ce fichier dans tous les répertoires html/includes/strings/*) </li>
		<li> Modifiez tous les fichiers html/*.php pour correspondre à votre
		design html (toutes les fonctions graphiques utilisées sont regroupées 
		dans libs/html.php, n\'hésitez pas à utiliser vos propres fonctions !).
		La classe Html n\'est utilisée que dans ces fichiers et includes/*.php</li>
		</ul>
		</div>
	';
?>
