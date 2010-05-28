<?
$content = '
		<div class="boxheader">XName Software</div>
		<div class="boxcontent">
		Oprogramowanie XName zostało poprawnie(?) zainstalowane.<br >
		Musisz teraz zrobić następujące rzeczy:
		<ul>
		<li> skopiuj libs/config.default jako libs/config.php</li>
		<li> popraw wszystkie pozycje w pliku libs/config.php</li>
		<li> jeśli dostajesz błędy z mysql, sprawdź, czy użytkownik 
		skonfigurowany w config.php istnieje, a także czy nazwa bazy
		danych jest taka sama, jak ta w sql/creation.sql</li>
		<li> popraw tekst w pliku html/includes/strings/pl/index_content.php
		(ewentualnie wnieś poprawki do tego pliku w innych katalogach
		językowych)</li>
		<li> popraw plik libs/html.php, aby wygląd pasował do Twojego
		projektu strony;
		klasa HTML jest użyta tylko w plikach html/*.php oraz includes/*.php</li>
		</ul>
		</div>
	';
?>
