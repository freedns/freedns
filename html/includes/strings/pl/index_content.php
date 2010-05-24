<?
$content = '
<dl>
   <dt>Nowy start [2010-05-23]</dt>
   <dd>Ruszamy! Logujecie siê <strong>korzystaj±c z loginu i has³a ze starego systemu</strong>, klikacie przycisk "Migruj", czekacie, a¿ zacznie dzia³aæ, zmieniacie u rejestratorów domen wpisy dotycz±ce serwerów nazw, tj. na: fns1.42.pl&nbsp;(79.98.145.34) oraz fns2.42.pl&nbsp;(195.80.237.194), czekacie, a¿ siê zmiany rozpropaguj± i&nbsp;wtedy mo¿ecie skasowaæ strefy i&nbsp;konto ze starego systemu.</dd><dd>Powodzenia!</dd>

   <dt>Polskie znaki w nazwach domen [2009-12-31]</dt>
   <dd>Polskie znaki w nazwach domen uzyskuje siê przez wpisanie w konfiguracji nazwy IDN,
   nie bezpo¶rednio nazwy z polskimi literami. Przyk³adowo
   zamiast <code>¿ó³ty¿ó³w.pl</code> trzeba za³o¿yæ 
   domenê <nobr><code>xn--tyw-fnac58bd27be.pl</code></nobr>.
   Przegl±darki automatycznie zamieniaj± nazwê z polskimi literami wpisan± w pasku adresu
   na postaæ IDN rozumian± przez DNS, wiêc dla u¿ytkowników jest to (podobno) niezauwa¿alne,
   tylko administrator DNS widzi, jak jest naprawdê. :)<p>
   T³umacz IDN jest m.in. na stronie <a href="http://www.dns.pl/cgi-bin/idntranslator.pl">NASK</a>.
   </dd>

   <dt>Eksperyment: rekord WWW [2007-05-27]</dt>
   <dd>Doda³em eksperymentalnie nowy rekord: WWW. To nie jest rekord DNS, tylko
		 skrót my¶lowy na ramkê lub przekierowanie WWW. Dzia³a to w nastêpuj±cy
		 sposób: dodajemy rekord WWW <tt>test</tt> w domenie <tt>nasza.pl</tt> z zawarto¶ci±
		 <tt><nobr>http://gdzies.serwer.z.naszym/~username/i/plikami/</nobr></tt>. Je¿eli zaznaczymy
		 "przekierowanie", to po wej¶ciu na <tt><nobr>http://test.nasza.pl</nobr></tt> zostaniemy przekierowani
		 na stronê jw., a je¿eli "ramka", to zobaczymy od razu nasz± stronê (ukryt±
		 w ramce). Oczywi¶cie trzeba chwilê odczekaæ, bo serwer dodaje po cichu
		 nowy rekord A wskazuj±cy na adres serwera WWW, który to obs³uguje. :)
   </dd>
	<dd>Bardzo proszê o komentarze i zg³aszanie ewentualnych problemów.</dd>

	<dt>Dynamiczny DNS</dt>
	<dd>
        Do dynamicznego aktualizowania rekordów u¿ywany jest XML RPC.
        Przygotowa³em <a href="freedns-dyndns.py">skrypt w Pythonie</a>,
        który w prosty sposób (<tt><nobr>freedns-dyndns.py --help</nobr></tt> lub zajrzyj do
        ¼ród³a) pozwoli Ci na zmianê Twojego IP. Po zmianie IP wystarczy uruchomiæ
        <tt><nobr>freedns-dyndns.py --newaddress <i>nowy.adres.ip</i></nobr></tt> i ju¿!<br>

        <p style="font-size:80%; margin-left:40px; margin-right:40px;">nale¿y poprawiæ
        domy¶lne warto¶ci w ¼ródle skryptu, ¿eby nie
        musieæ podawaæ nazwy hosta, u¿ytkownika i has³a w linii komend;<br>
        stare IP mo¿na podaæ jako \'*\', nie trzeba go mieæ zapisanego;</small></p>

        Innym sposobem jest skorzystanie z jakiegokolwiek serwisu oferuj±cego
        us³ugê dynamicznego DNS (np. <a href="http://www.dyndns.org">DynDNS</a>)
        i dodanie tutaj rekordu CNAME host.Twoja.domena wskazuj±cego na nazwê
        w zewnêtrznym serwisie.
	</dd>
	<dt>Idea</dt>
	<dd>
	Darmowy serwis utrzymywania DNS przeznaczony jest dla osób, które nie
	chc± traciæ czasu ani pieniêdzy u providerów (którzy w dodatku
	czasem nie s± zbyt ¿wawi, je¶li chodzi o zmiany w DNS).</dd>
	<dd>
	Oferujemy Ci podstawowy <b>oraz</b> zapasowy serwer nazw, automatyczne aktualizacje
  co kwadrans, rekordy SRV, TXT i obs³ugê IPv6 (ustaw sobie w opcjach u¿ytkownika),
  system przekierowañ/ramek (pseudo rekord WWW).
	<br >
	Wszystkie strefy obs³ugiwane jako podstawowe lub zapasowe na
	g³ównym serwerze &mdash; fns1.42.pl (79.98.145.34) &mdash; s± automatycznie
	replikowane na nasz drugi serwer, fns2.42.pl (195.80.237.194). 
  </dd>

	<dd>Je¿eli rejestruj±cy domenê (np. NASK) wymaga podania danych osobowych
   i/lub emaila administratora domeny, to podawajcie wasze w³asne. To wy bêdziecie
   zarz±dzaæ domen±, nie ja. Nie wpisujcie mnie jako administratora
   waszych domen.</dd>
	<dd>
	Wszystkie konfiguracje musz± zostaæ przeprowadzone przy u¿yciu
	tego interfejsu WWW.</dd>
	<dt>Skontaktuj siê ze mn±</dt>
	<dd>
	Je¶li masz jakie¶ pytania, napisz do mnie email na adres
	<a href="mailto:freedns na 42 kropka pl" class="linkcolor">
	freedns na 42 pl</a>. Czekaj cierpliwie &mdash; to darmowy serwis i na
	pytania odpowiadam w wolnym czasie.
	</dd>
   </dl>
';
$oldcontent = '
   <dt>Rekordy wildcard (*) [2008-09-12]</dt>
   <dd>Zamiast dodawaæ <code>* IN A 123.45.67.89</code>, skorzystaj z wieloznacznego
   rekordu CNAME:<p>
   <code>* IN CNAME rekord<br>rekord IN A 123.45.67.89</code>
   </dd>

   <dt>Komunikat [2010-04-30]</dt>
   <dd>
   SGH zdecydowa³a siê wy³±czyæ FreeDNS i nie wspó³pracowaæ ze mn± w zakresie przeniesienia
   u¿ytkowników na nowy serwis. Wkrótce wiêcej informacji o krokach potrzebnych do
   przeniesienia siê tutaj.
   </dd>
   <dt>Komunikat [2010-04-14]</dt>
   <dd>
Uprzejmie informujê, ¿e z powodów ode mnie niezale¿nych (patrz
<a href="http://42.pl/freedns.html">42.pl/freedns.html</a>)
nie bêdê móg³ siê dalej opiekowaæ FreeDNS::SGH.
</dd>
<dd>
W zamian utworzy³em FreeDNS::42, nied³ugo serdecznie zapraszam
do korzystania z tej wersji.
<!-- Wszystkie wasze dane zostan± przeniesione.<br>
Mo¿ecie zgadywaæ, który serwis bêdzie dalej rozwijany i ulepszany. -->
   </dd>
   <dt>Komunikat [2010-04-12]</dt>
   <dd>
Uprzejmie informujemy, ¿e od 8 kwietnia 2010 r. serwis FreeDNS SGH
znajduje
siê pod wy³±czn± opiek± administratorów Centrum Informatycznego SGH. O
decyzjach W³adz Uczelni zwi±zanych z dalszym funkcjonowaniem serwisu
FreeDNS, poinformujemy w terminie pó¼niejszym.<br><br>
Centrum Informatyczne SGH
   </dd>
   <dt>Zmienione delegowanie podstref stref odwrotnych [2008-11-28]</dt>
   <dd>Dla stref odwrotnych IPv6, jako ¿e zazwyczaj dostaje siê bardzo du¿y
       blok IP, wprowadzi³em mo¿liwo¶æ delegowanie ca³ych podsieci na inne serwery DNS
       (mo¿na, oczywi¶cie, podaæ serwery FreeDNS).<p>
       Dla stref odwrotnych IPv4 zostaje po staremu &mdash; mo¿liwo¶æ delegowania
       kawa³ków sieci (kilku IP) innym u¿ytkownikom FreeDNS. Wyszed³em z za³o¿enia,
       ¿e na 99,99% tacy u¿ytkownicy nie maj± do dyspozycji klas B, ¿eby rozpisywaæ
       dla nich normalne delegacje.
   </dd>
	<dd>Proszê o komentarze i zg³aszanie ewentualnych problemów.</dd>

	<dt>Aktualizacja oprogramowania [2006-10-20]</dt>
	<dd>
        Dzi¶ zaktualizowa³em oprogramowanie XName do najnowszej wersji. 
        Rzuæcie okiem na swoje strefy... mam nadziejê, ¿e nic siê nie popsu³o.
   </dd>
   <dt>Czêstsze aktualizacje [2007-04-29]</dt>
   <dd>Ha, zapomnia³em napisaæ, ¿e od lutego serwery s± aktualizowane co kwadrans,
       nie co pó³ godziny.
   </dd>
   <dt>B³±d oprogramowania [2007-04-22]</dt>
   <dd>Piotr Szeptyñski z Marcinem Kopcem znale¼li b³±d w oprogramowaniu XName
       pozwalaj±cy ogl±daæ cudze logi strefy. B³±d oczywi¶cie natychmiast 
       poprawi³em. Co zreszt± i tak nie ma wiêkszego znaczenia, bo z ró¿nych
       powodów logi nie s± aktualizowane na bie¿±co. :(
   </dd>
   <dt>Brak listów z powiadomieniem o zmianach w strefie [2006-10-31]</dt>
   <dd>Oczywi¶cie po aktualizacji co¶ siê popsu³o: a mianowicie przesta³y
       przychodziæ listy z powiadomieniem o zmianach w strefie. Ju¿ naprawione.
   </dd>
	<dt>Automatyczne aktualizacje [2005-02-28]</dt>
	<dd>
	Aktualizacja wpisów do DNS odbywa siê co kwadrans o :01, :16, :31 i :46. 
  </dd>

	<dt>Nazwa z kropk± [2005-01-22]</dt>
	<dd>
	Ulegaj±c wielu pro¶bom u¿ytkowników umo¿liwi³em tworzenie wpisów IN A
	zawieraj±cych jedn± kropkê.
   </dd>

	<dt>CNAME na domenê [2003-12-11]</dt>
	<dd>
	Nie da siê ustawiæ CNAME na domenê. Proszê nie pytaæ o tak± mo¿liwo¶æ.<br>
	"cokolwiek IN CNAME" &mdash; tak, "@ IN CNAME" &mdash; nie.<br>
	To nie jest mój wymys³, RFC 1034 zabrania rekordów, które maj± co¶ oprócz 
	CNAME (a tak jest w przypadku rekordu domeny, który przecie¿ musi mieæ SOA i NS).
   </dd>

	<dt>Rekordy SRV [2006-10-20]</dt>
	<dd>
        Po aktualizacji dosz³a jedna opcja w ustawieniach u¿ytkownika: "rekordy SRV".
        Nale¿y j± sobie w³±czyæ, je¶li chcecie zmieniaæ rekordy SRV. 
   </dd>
	<dt>Problemy z fns2 [2006-08-08]</dt>
	<dd>
		Mamy problem z fns2, a administrator pojecha³ na urlop. :(<br >
		Bardzo wszystkich przepraszam za k³opot.
	</dd>
        <dt>Dynamiczne aktualizowanie rekordów [2005-08-30]</dt>
        <dd>
        Dynamiczne aktualizowanie rekordów przy pomocy skryptów wykorzystuj±cych
        XML RPC chwilowo nie dzia³a. Gdy zniknie ten komunikat, to znaczy, ¿e ju¿
        dzia³a. :-)
        </dd>
	<dt>Rekordy TXT [2005-07-25]</dt>
	<dd>
        Po aktualizacji dosz³a jedna opcja w ustawieniach u¿ytkownika: "rekordy TXT".
        Nale¿y j± sobie w³±czyæ, je¶li chcecie zmieniaæ rekordy TXT. Dodatkowo mo¿na
	teraz robiæ rekordy TXT nie tylko na domenê g³ówn±.
        </dd>
	<dt>Aktualizacja oprogramowania [2005-07-24]</dt>
	<dd>
        Dzi¶ zaktualizowa³em oprogramowanie XName do najnowszej 
        wersji. Rzuæcie okiem na swoje strefy... mam nadziejê, ¿e nic siê
	nie popsu³o.
        </dd>
	<dt>pf.pl =&gt; epf.pl [2005-03-15]</dt>
	<dd>
        W zwi±zku ze zmian± domeny pf.pl na epf.pl i zaprzestaniem ¶wiadczenia
        us³ug pod star± nazw± poszed³em u¿ytkownikom na rêkê (mam nadziejê!)
        i zmieni³em hurtem wszystkie adresy mailowe z @pf.pl na @epf.pl.
        </dd>
	<dt>Problem z fns2.sgh.waw.pl [2004-10-07]</dt>
	<dd>
        Popsu³ siê serwer, na którym stoi fns2. Nie dzia³a³ prawie przez ca³y
	dzieñ 7. pa¼dziernika, zosta³ podmieniony tymczasowo na s³absz± maszynê
	(ale nie powinno to zostaæ zauwa¿one) i ma byæ naprawiony w weekend.
        </dd>
	<dt>FreeDNS::SGH na 42. miejscu [2004-09-15]</dt>
	<dd>
	W serwisie top100.pl FreeDNS::SGH zajmuje przyjemne 42. miejsce w rankingu
	serwisów DNS pod wzglêdem ilo¶ci obs³ugiwanych domen .pl<br>
	<strong>To wasza zas³uga, u¿ytkownicy &mdash; dziêkujê.
   </strong> :-)</dd>
	<dd>
	42. miejsce jest przyjemne dlatego, ¿e posiadam domenê 
	<a href="http://42.pl/">42.pl</a>
	i FreeDNS mia³ tam staæ (a mo¿e jeszcze bêdzie!).<br>
	Przy okazji ma³e piêtno dla top100 za podawanie b³êdnego adresu
	www do FreeDNS (do "freedns.sgh.waw.pl" dokleili na pocz±tku "www."
	(taka nazwa nie istnieje) i, co gorsza, odmawiaj± poprawienia t³umacz±c
	siê, ¿e "taki maj± skrypt" &mdash; lazy excuse of the day).
        </dd>
	<div class="boxheader"><em>strefa po³±czona z t± ju¿ istnieje i to
 nie Ty ni± zarz±dzasz...</em>[2004-09-15]</dd>
	<dd>
	Poprawi³em w koñcu to ograniczenie odno¶nie zak³adania stref, które
	s± podstrefami ju¿ istniej±cych. A konkretnie doda³em warunek, ¿e to
	ograniczenie dzia³a tylko dla stref podstawowych (w tym wypadku
	w³a¶ciciel mo¿e za³o¿yæ podstrefê i oddaæ uprawnienia do niej komukolwiek).
	Dla stref, które w serwisie by³y tylko jako secondary (jak np. pl.eu.org),
	ograniczenie to uniemo¿liwia³o skorzystanie z serwisu w ogóle.
	Za k³opot przepraszam.
        </dd>
	<dt>Naprawione listowanie stref [2004-09-15]</dt>
	<dd>
	Serwer DNS dzia³a³ poprawnie, ale nie mo¿na by³o wylistowaæ zawarto¶ci
	stref z innych serwerów w interfejsie WWW. Problem naprawiony.
        </dd>
	<dt>Chwilowa przerwa w dzia³aniu [2004-09-01]</dt>
	<dd>
	Ze wzglêdu na upgrade sprzêtu w dniu dzisiejszym interfejs WWW FreeDNS::SGH
	mo¿e byæ chwilami nieczynny (komunikat "problem z baz± danych" i brak 
	mo¿liwo¶ci zalogowania siê na swoje konto). Sam serwer DNS powinien
	dzia³aæ bez przeszkód.
        </dd>
	<dt>U¿ytkownicy z adresami @konto.pl [2004-08-20]</dt>
	<dd>
        Administratorzy konto.pl postanowili odrzucaæ
        maile z domeny sgh.waw.pl, a wiêc tak¿e z serwisu FreeDNS::SGH. Sugerujê
        zapisywaæ siê z innego adresu mail lub spróbowaæ wyja¶niæ sprawê
        u administratorów konto.pl.
        </dd>
	<dt>Problemy z baz± danych [2004-07-22]</dt>
	<dd>
	Z³o¶liwo¶æ komputerów, spu¶ciæ je z oka na kilka dni i siê psuj±. :-)
	Naprawione i mam nadziejê, ¿e siê nie powtórzy.
        </dd>
	<dt>Rekordy TXT [2004-06-10]</dt>
	<dd>
	Na pro¶bê u¿ytkowników doda³em mo¿liwo¶æ tworzenia rekordów TXT.
        </dd>
	<dt>Bezpieczne logowanie [2004-05-30]</dt>
	<dd>
	Na pro¶bê u¿ytkowników logowanie do serwisu jest szyfrowane przez SSL.
	Niestety musia³em przez to zmieniæ adres IP interfejsu WWW &mdash; teraz jest to
	194.145.96.21 (taki sam, jak adres serwera fns1).
        </dd>
	<del><dt>Uwaga przy tworzeniu domen [2004-05-27]</dt>
	<dd>
	Uwaga: po utworzeniu strefy nale¿y j± zmodyfikowaæ! Choæby¶cie nie dodawali
	¿adnego wpisu, trzeba wej¶æ w zak³adkê modyfikuj strefê i wybraæ
	<b>Utwórz konfiguracjê strefy</b>. W przeciwnym wypadku strefa nie bêdzie
	widoczna jako dzia³aj±ca (przynajmniej dopóki nie naprawiê tego b³êdu).
   </dd></del>
	<dt>Problemy z logami [2004-04-14]</dt>
	<dd>
	Poprawi³em problem braku ¶wie¿ych logów oraz niepoprawnych dat
	w logach. Przy okazji skasowa³em istniej±ce logi i za³adowa³em
	od nowa logi od pocz±tku roku &mdash; przejrzyjcie i pokasujcie.
   </dd>
	<dt>D³ugie logowanie [2004-02-13]</dt>
	<dd>
	Wystêpuj±ce od jakiego¶ czasu problemy z d³ugim logowaniem (i do¶æ czêsto
	du¿ymi utrudnieniami w zarz±dzaniu strefami) okaza³y siê wynikiem
	b³êdu w skrypcie wrzucaj±cym do bazy logi serwera DNS. Od 1 lutego nazbiera³o
	siê ich ca³kiem niepotrzebnie ponad 3.5mln, co (w zwi±zku z ró¿nymi
	procedurami wykonuj±cymi siê w trakcie logowania) kompletnie zatka³o
	serwer. Ju¿ powinno byæ w porz±dku.
   </dd>
	<dt>Admin DNS [2004-01-20]</dt>
	<dd>
	Je¿eli rejestruj±cy domenê (np. NASK) wymaga podania danych osobowych
   i/lub emaila administratora domeny, to podawajcie wasze. To wy bêdziecie
   zarz±dzaæ domen±, nie ja. Nie wpisujcie mnie jako administratora
   waszych domen.
   </dd>
	<dt>Dynamiczny DNS [2003-12-12]</dt>
	<dd>
	Kilka s³ów wyja¶nienia: mo¿liwo¶æ aktualizacji IP przez skrypt (poni¿ej)
	to nie jest prawdziwy dynamiczny DNS &mdash; ten system od¶wie¿a
	konfiguracjê co kwadrans. Je¶li Twoje IP zmienia siê czê¶ciej, to nie
	jest serwis dla Ciebie (chyba ¿e u¿yjesz CNAME na adres w "prawdziwym" DynDNS).
   </dd>
	<dt>Czê¶ciej [2003-12-05]</dt>
	<dd>
	Teraz strefy s± od¶wie¿ane co pó³ godziny &mdash; taki Miko³ajkowy prezent ;)
   </dd>
	<dt>Drobiazgi [2003-11-28]</dt>
	<dd>
        Poprawiono kilka drobiazgów. M.in. mo¿na u¿ywaæ CNAME z kropk±,
        a tak¿e korzystaæ z @ (zamiast pe³nej nazwy) do robienia wpisów
        dla samej strefy.<br>
        Dodatkowo zmieni³em interfejs w zak³adce modyfikacji &mdash; mam nadziejê,
        ¿e teraz jest czytelniej.
        </dd>

	<dt>Dwa serwery DNS</dt>
	<dd>
	Zrezygnowa³em z obowi±zku rejestrowania obu serwerów DNS,
	teraz obowi±zkowy jest tylko fns1.42.pl. Oczywi¶cie
	dalej mo¿na wpisywaæ fns2.42.pl. W dodatku
	fns2 bêdzie posiada³ dok³adn± kopiê fns1, a tak¿e bêdzie
	sam próbowa³ ¶ci±gaæ zawarto¶æ stref, dla których jeste¶my
	zapasowym serwerem DNS.
	</dd>

	<dt>Upgrade</dt>
	<dd>
        Zaktualizowa³em oprogramowanie FreeDNS. Przy okazji wkrad³o siê
        kilka b³êdów, które musia³em rêcznie poprawiæ, co spowodowa³o
        konieczno¶æ wygenerowania wszystkich stref od nowa. St±d te listy
        o tym, ¿e prze³adowano Twoje strefy.
        </dd>
';
$content_test_old = '
<p style="color:#ff0000;text-align:center;font-size:170%">PROSZÊ JESZCZE NIE U¯YWAÆ<br>JESZCZE NIE JEST GOTOWE<br>DAM WAM ZNAÆ</p>
   <dl>
<dt>Prawdopodobny start #2 [2010-05-20]</dt>
<dd>Tym razem ju¿ wszystko mam pod rêk± i wygl±da, ¿e dzia³a. Zatem najpó¼niej w poniedzia³ek ruszamy.</dd>

<dt>Wszystko dzia³a [2010-05-19]</dt>
<dd>Wszystko dzia³a... a przynajmniej powinno. Mo¿ecie dodawaæ, kasowaæ, zmieniaæ strefy, obserwujcie, czy fns1.42.pl i fns2.42.pl odpowiadaj± poprawnie. Nie zapominajcie, ¿e to <strong>wszystko</strong> zostanie skasowane na rzecz importu ze starego serwisu.</dd>

<dt>Ci±gle czekamy [2010-05-19]</dt>
<dd>W koñcu dosta³em wjazd na fns2, biorê siê ostro za robotê, do koñca tygodnia na pewno ruszy.</dd>

<dt>Ci±gle czekamy [2010-05-16]</dt>
<dd>Ci±gle czekam na uruchomienie przez sponsora drugiego serwera dns, mam nadziejê, ¿e to ju¿ wkrótce.</dd>

<dt>Prawdopodobny start [2010-05-04]</dt>
<dd>Prawdopodobny start oko³o 15 maja, czekamy na przyj¶cie nowozakupionych serwerów, no i trzeba skonfigurowaæ i przetestowaæ.</dd>
<dd>
Zdecydowa³em te¿, ¿e zaimportujê wszystkich u¿ytkowników (trzeba bêdzie potwierdziæ adres email, bo jak nie, to kasacja w rozs±dnym czasie) oraz wszystkie strefy i rekordy. Kiedy dok³adnie ten import nast±pi, jeszcze nie wiem, ale og³oszê z wyprzedzeniem. W ka¿dym razie nie bêdziecie musieli (zbyt du¿o) aktualizowaæ w stosunku do starego serwisu<!--, gdy¿ ci±gle mam dostêp do drugiego serwera DNS, gdzie s± Wasze wszystkie rekordy-->.
</dd>
<dt>Ci±gle w wersji testowej [2010-05-03]</dt>
<dd>
Mo¿na zak³adaæ sobie u¿ytkowników i strefy, <del>ale ¿aden serwer DNS jeszcze tego nie obs³uguje</del>. Ponadto po zakoñczeniu testów, przed zaimportowaniem backupu z poprzedniego serwisu, wszystkie dotychczasowe, tymczasowe wpisy z bazy danych zostan± skasowane.
</dd>
<dd>Oczywi¶cie je¶li znajdziecie jakie¶ problemy, to proszê pisaæ. :-)</dd>
<!-- Poprzedni fns2 (193.111.27.194) zostanie przekonfigurowany, ¿eby ¶ci±ga³
     strefy z nowych serwerów; w ten sposób nawet je¶li kto¶ bêdzie mia³ problem
     z przekonfigurowaniem, to bêdzie mu dzia³aæ. -->

</dl>
';

?>
